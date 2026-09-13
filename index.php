<?php
/**
 * Dashboard Principal
 * Página de inicio que muestra información diferente según el rol
 */

require_once './config/database.php';
require_once './config/config.php';

checkSession();

// Obtener información del usuario
$user_id = $_SESSION['user_id'];
$role_id = $_SESSION['user_role'];

// Obtener estadísticas según el rol
$stats = [];

if ($role_id == ROLE_ADMIN) {
    // Estadísticas para Administrador
    $stats['total_usuarios'] = getRecord("SELECT COUNT(*) as count FROM usuarios")['count'] ?? 0;
    $stats['total_pacientes'] = getRecord("SELECT COUNT(*) as count FROM pacientes")['count'] ?? 0;
    $stats['total_doctores'] = getRecord("SELECT COUNT(*) as count FROM doctores")['count'] ?? 0;
    $stats['total_citas'] = getRecord("SELECT COUNT(*) as count FROM citas WHERE DATE(fecha_cita) = CURDATE()")['count'] ?? 0;
    $stats['facturacion_pendiente'] = getRecord("SELECT SUM(total) as total FROM facturacion WHERE estado_factura = 'pendiente'")['total'] ?? 0;
    
} elseif ($role_id == ROLE_DOCTOR) {
    // Estadísticas y calendario para Doctor
    $doctor_id = getRecord("SELECT id_doctor FROM doctores WHERE id_usuario = ?", [$user_id])['id_doctor'] ?? 0;
    $stats['citas_hoy'] = getRecord(
        "SELECT COUNT(*) as count FROM citas
         WHERE id_doctor = ? AND DATE(fecha_cita) = CURDATE()
         AND estado_cita IN ('programada', 'confirmada')",
        [$doctor_id]
    )['count'] ?? 0;
    $stats['consultas_mes'] = getRecord("SELECT COUNT(*) as count FROM consultas WHERE id_doctor = ? AND MONTH(fecha_consulta) = MONTH(NOW())", [$doctor_id])['count'] ?? 0;
    $stats['pacientes'] = getRecord("SELECT COUNT(DISTINCT id_paciente) as count FROM citas WHERE id_doctor = ?", [$doctor_id])['count'] ?? 0;

    // Permite revisar cualquier mes sin aceptar una fecha inválida en la URL.
    $calendar_month = isset($_GET['mes']) && preg_match('/^\\d{4}-\\d{2}$/', $_GET['mes'])
        ? $_GET['mes']
        : date('Y-m');
    $calendar_timestamp = strtotime($calendar_month . '-01');
    if ($calendar_timestamp === false) {
        $calendar_timestamp = strtotime(date('Y-m-01'));
        $calendar_month = date('Y-m');
    }

    $calendar_start = date('Y-m-01', $calendar_timestamp);
    $calendar_end = date('Y-m-01', strtotime('+1 month', $calendar_timestamp));
    $calendar_counts = [];
    $calendar_rows = getRecords(
        "SELECT fecha_cita, COUNT(*) AS cantidad
         FROM citas
         WHERE id_doctor = ? AND fecha_cita >= ? AND fecha_cita < ?
         AND estado_cita IN ('programada', 'confirmada')
         GROUP BY fecha_cita",
        [$doctor_id, $calendar_start, $calendar_end]
    );
    foreach ($calendar_rows as $calendar_row) {
        $calendar_counts[$calendar_row['fecha_cita']] = (int) $calendar_row['cantidad'];
    }
    
} elseif ($role_id == ROLE_RECEPCIONISTA) {
    // Estadísticas para Recepcionista
    $stats['citas_hoy'] = getRecord("SELECT COUNT(*) as count FROM citas WHERE DATE(fecha_cita) = CURDATE()")['count'] ?? 0;
    $stats['pacientes_activos'] = getRecord("SELECT COUNT(*) as count FROM pacientes WHERE estado = 'activo'")['count'] ?? 0;
    $stats['consultas_pendientes'] = getRecord("SELECT COUNT(*) as count FROM consultas WHERE estado_consulta = 'pendiente'")['count'] ?? 0;
    
} elseif ($role_id == ROLE_PACIENTE) {
    // Estadísticas para Paciente
    $paciente_id = getRecord("SELECT id_paciente FROM pacientes WHERE id_usuario = ?", [$user_id])['id_paciente'] ?? 0;
    $stats['proximas_citas'] = getRecord("SELECT COUNT(*) as count FROM citas WHERE id_paciente = ? AND fecha_cita >= CURDATE()", [$paciente_id])['count'] ?? 0;
    $stats['ultimas_consultas'] = getRecord("SELECT COUNT(*) as count FROM consultas WHERE id_paciente = ? LIMIT 5", [$paciente_id])['count'] ?? 0;
    $stats['facturas_pendientes'] = getRecord("SELECT SUM(total) as total FROM facturacion WHERE id_paciente = ? AND estado_factura = 'pendiente'", [$paciente_id])['total'] ?? 0;
    
} elseif ($role_id == ROLE_CONTADOR) {
    // Estadísticas para Contador
    $stats['facturas_pendiente'] = getRecord("SELECT COUNT(*) as count FROM facturacion WHERE estado_factura = 'pendiente'")['count'] ?? 0;
    $stats['total_pendiente'] = getRecord("SELECT SUM(total) as total FROM facturacion WHERE estado_factura = 'pendiente'")['total'] ?? 0;
    $stats['ingresos_mes'] = getRecord("SELECT SUM(total) as total FROM facturacion WHERE estado_factura = 'pagada' AND MONTH(fecha_factura) = MONTH(NOW())")['total'] ?? 0;
}

include './includes/header.php';
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1>Bienvenido, <?php echo $_SESSION['user_name']; ?></h1>
        <p>Rol: <strong><?php echo $_SESSION['user_role_name']; ?></strong></p>
    </div>

    <!-- Dashboard Administrador -->
    <?php if ($role_id == ROLE_ADMIN): ?>
        <div class="dashboard-grid">
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-content">
                    <div class="stat-label">Total Usuarios</div>
                    <div class="stat-value"><?php echo $stats['total_usuarios']; ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">🧑‍🦱</div>
                <div class="stat-content">
                    <div class="stat-label">Total Pacientes</div>
                    <div class="stat-value"><?php echo $stats['total_pacientes']; ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">👨‍⚕️</div>
                <div class="stat-content">
                    <div class="stat-label">Total Doctores</div>
                    <div class="stat-value"><?php echo $stats['total_doctores']; ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">📅</div>
                <div class="stat-content">
                    <div class="stat-label">Citas Hoy</div>
                    <div class="stat-value"><?php echo $stats['total_citas']; ?></div>
                </div>
            </div>

            <div class="stat-card warning">
                <div class="stat-icon">💰</div>
                <div class="stat-content">
                    <div class="stat-label">Facturación Pendiente</div>
                    <div class="stat-value">$<?php echo number_format($stats['facturacion_pendiente'], 2); ?></div>
                </div>
            </div>
        </div>

        <div class="dashboard-sections">
            <div class="section">
                <h2>Acciones Rápidas</h2>
                <div class="quick-actions">
                    <a href="<?php echo BASE_URL; ?>/pages/usuarios.php?new=1" class="action-btn">➕ Nuevo Usuario</a>
                    <a href="<?php echo BASE_URL; ?>/pages/pacientes.php?new=1" class="action-btn">➕ Nuevo Paciente</a>
                    <a href="<?php echo BASE_URL; ?>/pages/citas.php?new=1" class="action-btn">➕ Nueva Cita</a>
                    <a href="<?php echo BASE_URL; ?>/pages/reportes.php" class="action-btn">📊 Ver Reportes</a>
                </div>
            </div>
        </div>

    <!-- Dashboard Doctor -->
    <?php elseif ($role_id == ROLE_DOCTOR): ?>
        <div class="dashboard-grid">
            <div class="stat-card">
                <div class="stat-icon">📅</div>
                <div class="stat-content">
                    <div class="stat-label">Citas Programadas Hoy</div>
                    <div class="stat-value"><?php echo $stats['citas_hoy']; ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">📋</div>
                <div class="stat-content">
                    <div class="stat-label">Consultas Este Mes</div>
                    <div class="stat-value"><?php echo $stats['consultas_mes']; ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">🧑‍🦱</div>
                <div class="stat-content">
                    <div class="stat-label">Total Pacientes</div>
                    <div class="stat-value"><?php echo $stats['pacientes']; ?></div>
                </div>
            </div>
        </div>

        <div class="dashboard-sections">
            <div class="section">
                <h2>Mis Citas de Hoy</h2>
                <?php
                $citas = getRecords(
                    "SELECT c.*, p.id_paciente, CONCAT(u.nombre, ' ', u.apellido) as paciente_nombre, s.nombre_servicio
                     FROM citas c
                     JOIN pacientes p ON c.id_paciente = p.id_paciente
                     JOIN usuarios u ON p.id_usuario = u.id_usuario
                     JOIN servicios s ON c.id_servicio = s.id_servicio
                     WHERE c.id_doctor = ? AND DATE(c.fecha_cita) = CURDATE()
                     ORDER BY c.hora_cita ASC",
                    [$doctor_id]
                );
                ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Hora</th>
                                <th>Paciente</th>
                                <th>Servicio</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($citas)): ?>
                                <tr><td colspan="5" class="calendar-empty">No tienes citas registradas para hoy.</td></tr>
                            <?php else: ?>
                            <?php foreach ($citas as $cita): ?>
                                <tr>
                                    <td><?php echo substr($cita['hora_cita'], 0, 5); ?></td>
                                    <td><?php echo $cita['paciente_nombre']; ?></td>
                                    <td><?php echo $cita['nombre_servicio']; ?></td>
                                    <td><span class="badge badge-<?php echo $cita['estado_cita']; ?>"><?php echo ucfirst($cita['estado_cita']); ?></span></td>
                                    <td>
                                        <a href="<?php echo BASE_URL; ?>/pages/consultas.php?cita_id=<?php echo $cita['id_cita']; ?>" class="btn-sm btn-primary">Registrar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php
            // Datos que necesita la cuadrícula del mes seleccionado.
            $month_names = [
                1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
            ];
            $previous_month = date('Y-m', strtotime('-1 month', $calendar_timestamp));
            $next_month = date('Y-m', strtotime('+1 month', $calendar_timestamp));
            $days_in_month = (int) date('t', $calendar_timestamp);
            $first_weekday = (int) date('N', $calendar_timestamp);
            $today = date('Y-m-d');
            $calendar_total = array_sum($calendar_counts);
            ?>
            <div class="section doctor-calendar-section">
                <div class="doctor-calendar-header">
                    <div class="doctor-calendar-title">
                        <span class="doctor-calendar-title-icon" aria-hidden="true">📆</span>
                        <div><h2>Citas programadas por día</h2>
                        <p>Solo se cuentan las citas pendientes de atender o confirmadas.</p>
                        </div>
                    </div>
                    <div class="doctor-calendar-navigation" aria-label="Cambiar mes del calendario">
                        <a href="<?php echo BASE_URL; ?>/index.php?mes=<?php echo $previous_month; ?>" aria-label="Mes anterior">&lsaquo;</a>
                        <strong><?php echo $month_names[(int) date('n', $calendar_timestamp)] . ' ' . date('Y', $calendar_timestamp); ?><small><?php echo $calendar_total; ?> <?php echo $calendar_total === 1 ? 'cita programada' : 'citas programadas'; ?></small></strong>
                        <a href="<?php echo BASE_URL; ?>/index.php?mes=<?php echo $next_month; ?>" aria-label="Mes siguiente">&rsaquo;</a>
                    </div>
                </div>
                <div class="doctor-calendar-weekdays" aria-hidden="true">
                    <span>Lun</span><span>Mar</span><span>Mié</span><span>Jue</span><span>Vie</span><span>Sáb</span><span>Dom</span>
                </div>
                <div class="doctor-calendar-grid">
                    <?php for ($blank_day = 1; $blank_day < $first_weekday; $blank_day++): ?>
                        <div class="doctor-calendar-day is-empty"></div>
                    <?php endfor; ?>
                    <?php for ($day = 1; $day <= $days_in_month; $day++): ?>
                        <?php
                        $date_key = $calendar_month . '-' . str_pad((string) $day, 2, '0', STR_PAD_LEFT);
                        $appointment_count = $calendar_counts[$date_key] ?? 0;
                        $is_today = $date_key === $today;
                        ?>
                        <div class="doctor-calendar-day<?php echo $is_today ? ' is-today' : ''; ?><?php echo $appointment_count > 0 ? ' has-appointments' : ''; ?>">
                            <span class="doctor-calendar-date"><?php echo $day; ?></span>
                            <?php if ($appointment_count > 0): ?>
                                <span class="doctor-calendar-count"><?php echo $appointment_count; ?> <?php echo $appointment_count === 1 ? 'cita' : 'citas'; ?></span>
                            <?php else: ?>
                                <span class="doctor-calendar-free">Libre</span>
                            <?php endif; ?>
                        </div>
                    <?php endfor; ?>
                </div>
                <div class="doctor-calendar-legend"><span><i class="legend-dot legend-dot-appointments"></i> Día con citas</span><span><i class="legend-dot legend-dot-today"></i> Día actual</span><span><i class="legend-dot legend-dot-free"></i> Sin citas</span></div>
            </div>
        </div>

    <!-- Dashboard Recepcionista -->
    <?php elseif ($role_id == ROLE_RECEPCIONISTA): ?>
        <div class="dashboard-grid">
            <div class="stat-card">
                <div class="stat-icon">📅</div>
                <div class="stat-content">
                    <div class="stat-label">Citas Hoy</div>
                    <div class="stat-value"><?php echo $stats['citas_hoy']; ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">🧑‍🦱</div>
                <div class="stat-content">
                    <div class="stat-label">Pacientes Activos</div>
                    <div class="stat-value"><?php echo $stats['pacientes_activos']; ?></div>
                </div>
            </div>

            <div class="stat-card warning">
                <div class="stat-icon">⏳</div>
                <div class="stat-content">
                    <div class="stat-label">Consultas Pendientes</div>
                    <div class="stat-value"><?php echo $stats['consultas_pendientes']; ?></div>
                </div>
            </div>
        </div>

        <div class="dashboard-sections">
            <div class="section">
                <h2>Acciones Rápidas</h2>
                <div class="quick-actions">
                    <a href="<?php echo BASE_URL; ?>/pages/pacientes.php?new=1" class="action-btn">➕ Nuevo Paciente</a>
                    <a href="<?php echo BASE_URL; ?>/pages/citas.php?new=1" class="action-btn">➕ Nueva Cita</a>
                    <a href="<?php echo BASE_URL; ?>/pages/facturacion.php?new=1" class="action-btn">💰 Crear Factura</a>
                </div>
            </div>
        </div>

    <!-- Dashboard Paciente -->
    <?php elseif ($role_id == ROLE_PACIENTE): ?>
        <div class="dashboard-grid">
            <div class="stat-card">
                <div class="stat-icon">📅</div>
                <div class="stat-content">
                    <div class="stat-label">Próximas Citas</div>
                    <div class="stat-value"><?php echo $stats['proximas_citas']; ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">📋</div>
                <div class="stat-content">
                    <div class="stat-label">Últimas Consultas</div>
                    <div class="stat-value"><?php echo $stats['ultimas_consultas']; ?></div>
                </div>
            </div>

            <div class="stat-card warning">
                <div class="stat-icon">💰</div>
                <div class="stat-content">
                    <div class="stat-label">Facturas Pendientes</div>
                    <div class="stat-value">$<?php echo number_format($stats['facturas_pendientes'], 2); ?></div>
                </div>
            </div>
        </div>

        <div class="dashboard-sections">
            <div class="section">
                <h2>Mis Próximas Citas</h2>
                <?php
                $paciente_id = getRecord("SELECT id_paciente FROM pacientes WHERE id_usuario = ?", [$user_id])['id_paciente'] ?? 0;
                $proximas_citas = getRecords(
                    "SELECT c.*, CONCAT(u.nombre, ' ', u.apellido) as doctor_nombre, s.nombre_servicio
                     FROM citas c
                     JOIN doctores d ON c.id_doctor = d.id_doctor
                     JOIN usuarios u ON d.id_usuario = u.id_usuario
                     JOIN servicios s ON c.id_servicio = s.id_servicio
                     WHERE c.id_paciente = ? AND c.fecha_cita >= CURDATE()
                     ORDER BY c.fecha_cita, c.hora_cita
                     LIMIT 5",
                    [$paciente_id]
                );
                ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>Doctor</th>
                                <th>Servicio</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($proximas_citas as $cita): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($cita['fecha_cita'])); ?></td>
                                    <td><?php echo substr($cita['hora_cita'], 0, 5); ?></td>
                                    <td><?php echo $cita['doctor_nombre']; ?></td>
                                    <td><?php echo $cita['nombre_servicio']; ?></td>
                                    <td><span class="badge badge-<?php echo $cita['estado_cita']; ?>"><?php echo ucfirst($cita['estado_cita']); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    <!-- Dashboard Contador -->
    <?php elseif ($role_id == ROLE_CONTADOR): ?>
        <div class="dashboard-grid">
            <div class="stat-card warning">
                <div class="stat-icon">📄</div>
                <div class="stat-content">
                    <div class="stat-label">Facturas Pendientes</div>
                    <div class="stat-value"><?php echo $stats['facturas_pendiente']; ?></div>
                </div>
            </div>

            <div class="stat-card warning">
                <div class="stat-icon">💰</div>
                <div class="stat-content">
                    <div class="stat-label">Total Pendiente de Cobro</div>
                    <div class="stat-value">$<?php echo number_format($stats['total_pendiente'], 2); ?></div>
                </div>
            </div>

            <div class="stat-card success">
                <div class="stat-icon">✅</div>
                <div class="stat-content">
                    <div class="stat-label">Ingresos Este Mes</div>
                    <div class="stat-value">$<?php echo number_format($stats['ingresos_mes'], 2); ?></div>
                </div>
            </div>
        </div>

        <div class="dashboard-sections">
            <div class="section">
                <h2>Facturas Pendientes de Cobro</h2>
                <?php
                $facturas_pendientes = getRecords(
                    "SELECT f.*, CONCAT(u.nombre, ' ', u.apellido) as paciente_nombre
                     FROM facturacion f
                     JOIN pacientes p ON f.id_paciente = p.id_paciente
                     JOIN usuarios u ON p.id_usuario = u.id_usuario
                     WHERE f.estado_factura = 'pendiente'
                     ORDER BY f.fecha_factura DESC
                     LIMIT 10"
                );
                ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Número Factura</th>
                                <th>Paciente</th>
                                <th>Fecha</th>
                                <th>Total</th>
                                <th>Días Vencida</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($facturas_pendientes as $factura): 
                                $dias_vencida = (int)((time() - strtotime($factura['fecha_factura'])) / 86400);
                            ?>
                                <tr>
                                    <td><?php echo $factura['numero_factura']; ?></td>
                                    <td><?php echo $factura['paciente_nombre']; ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($factura['fecha_factura'])); ?></td>
                                    <td>$<?php echo number_format($factura['total'], 2); ?></td>
                                    <td><?php echo $dias_vencida > 0 ? $dias_vencida . ' días' : 'Hoy'; ?></td>
                                    <td>
                                        <a href="<?php echo BASE_URL; ?>/pages/facturacion.php?id=<?php echo $factura['id_factura']; ?>" class="btn-sm btn-primary">Ver</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    <?php endif; ?>
</div>

<?php include './includes/footer.php'; ?>
