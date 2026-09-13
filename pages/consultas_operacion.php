<?php
/**
 * Consultas del doctor.
 * Esta pantalla limita los registros al médico conectado y permite completar
 * una consulta usando el mismo flujo de estado que las citas.
 */
if (!checkPermission('consultas')) {
    header('Location: ' . BASE_URL . '/index.php');
    exit();
}

$doctor = getRecord('SELECT id_doctor FROM doctores WHERE id_usuario = ?', [(int) $_SESSION['user_id']]);
$doctorId = (int) ($doctor['id_doctor'] ?? 0);
if (!$doctorId) {
    exit('No existe un perfil de doctor vinculado a esta cuenta.');
}

if (empty($_SESSION['csrf_consulta'])) {
    $_SESSION['csrf_consulta'] = bin2hex(random_bytes(32));
}

$message = '';
$error = '';

// Verifica que la cita pertenezca al doctor y obtiene el paciente relacionado.
function getDoctorAppointment(int $appointmentId, int $doctorId, bool $mustBeCompleted = true): ?array {
    $statusCondition = $mustBeCompleted ? " AND c.estado_cita = 'completada'" : '';
    return getRecord(
        "SELECT c.id_cita, c.id_paciente, c.fecha_cita, c.hora_cita,
                CONCAT(pu.nombre, ' ', pu.apellido) AS paciente
         FROM citas c
         JOIN pacientes p ON p.id_paciente = c.id_paciente
         JOIN usuarios pu ON pu.id_usuario = p.id_usuario
         WHERE c.id_cita = ? AND c.id_doctor = ?" . $statusCondition,
        [$appointmentId, $doctorId]
    );
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_consulta'], $_POST['csrf'] ?? '')) {
        $error = 'La solicitud expiró. Intente nuevamente.';
    } else {
        try {
            $action = $_POST['action'] ?? '';
            $consultationId = (int) ($_POST['consulta_id'] ?? 0);

            if ($action === 'completar') {
                // Solo se finaliza una consulta pendiente del médico conectado.
                executeQuery(
                    "UPDATE consultas SET estado_consulta = 'completada'
                     WHERE id_consulta = ? AND id_doctor = ? AND estado_consulta = 'pendiente'",
                    [$consultationId, $doctorId]
                );
                $message = 'La consulta fue marcada como completada.';
            } elseif ($action === 'guardar') {
                $appointmentId = (int) ($_POST['cita_id'] ?? 0);
                $appointment = getDoctorAppointment($appointmentId, $doctorId);
                if (!$appointment) {
                    throw new Exception('La cita no está disponible para registrar una consulta.');
                }

                $reason = trim($_POST['motivo_consulta'] ?? '');
                $diagnosis = trim($_POST['diagnostico'] ?? '');
                if ($reason === '' || $diagnosis === '') {
                    throw new Exception('El motivo y el diagnóstico son obligatorios.');
                }

                $data = [
                    'motivo_consulta' => $reason,
                    'diagnostico' => $diagnosis,
                    'tratamiento' => trim($_POST['tratamiento'] ?? '') ?: null,
                    'medicamentos_prescritos' => trim($_POST['medicamentos_prescritos'] ?? '') ?: null,
                    'observaciones' => trim($_POST['observaciones'] ?? '') ?: null,
                    'proxima_cita' => trim($_POST['proxima_cita'] ?? '') ?: null
                ];

                if ($consultationId > 0) {
                    $existing = getRecord('SELECT id_consulta FROM consultas WHERE id_consulta = ? AND id_doctor = ? AND estado_consulta = \'pendiente\'', [$consultationId, $doctorId]);
                    if (!$existing) {
                        throw new Exception('La consulta no se puede editar porque ya fue finalizada.');
                    }
                    updateRecord('consultas', $data, 'id_consulta', $consultationId);
                    $message = 'La consulta fue actualizada.';
                } else {
                    $data = array_merge([
                        'id_cita' => $appointmentId,
                        'id_paciente' => (int) $appointment['id_paciente'],
                        'id_doctor' => $doctorId,
                        'estado_consulta' => 'pendiente'
                    ], $data);
                    insertRecord('consultas', $data);
                    $message = 'La consulta fue registrada como pendiente.';
                }
            } else {
                throw new Exception('Acción no válida.');
            }
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            $error = $exception->getMessage();
        }
    }
}

$editing = null;
$appointment = null;
if (isset($_GET['edit'])) {
    $editing = getRecord('SELECT * FROM consultas WHERE id_consulta = ? AND id_doctor = ?', [(int) $_GET['edit'], $doctorId]);
    if (!$editing || $editing['estado_consulta'] !== 'pendiente') {
        $editing = null;
        $error = 'Solo se pueden editar tus consultas pendientes.';
    } else {
        // Se muestra la información de la cita aun cuando sea un registro antiguo.
        $appointment = getDoctorAppointment((int) $editing['id_cita'], $doctorId, false);
    }
} elseif (isset($_GET['cita_id'])) {
    $appointment = getDoctorAppointment((int) $_GET['cita_id'], $doctorId);
    if (!$appointment) {
        $error = 'Selecciona una cita propia que ya haya sido completada.';
    }
}

$availableAppointments = getRecords(
    "SELECT c.id_cita, c.fecha_cita, c.hora_cita, CONCAT(pu.nombre, ' ', pu.apellido) AS paciente
     FROM citas c
     JOIN pacientes p ON p.id_paciente = c.id_paciente
     JOIN usuarios pu ON pu.id_usuario = p.id_usuario
     LEFT JOIN consultas co ON co.id_cita = c.id_cita
     WHERE c.id_doctor = ? AND c.estado_cita = 'completada' AND co.id_consulta IS NULL
     ORDER BY c.fecha_cita DESC, c.hora_cita DESC",
    [$doctorId]
);

$consultations = getRecords(
    "SELECT co.id_consulta, co.id_cita, co.fecha_consulta, co.motivo_consulta, co.diagnostico,
            co.estado_consulta, CONCAT(pu.nombre, ' ', pu.apellido) AS paciente
     FROM consultas co
     JOIN pacientes p ON p.id_paciente = co.id_paciente
     JOIN usuarios pu ON pu.id_usuario = p.id_usuario
     WHERE co.id_doctor = ?
     ORDER BY co.fecha_consulta DESC, co.id_consulta DESC",
    [$doctorId]
);

include __DIR__ . '/../includes/header.php';
?>
<div class="page-header">
    <div><h1>Mis consultas clínicas</h1><p>Registra la atención y finaliza las consultas pendientes.</p></div>
</div>

<?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<?php if ($appointment): ?>
    <?php $formData = $editing ?? []; ?>
    <section class="content-section form-panel">
        <h2><?= $editing ? 'Editar consulta pendiente' : 'Registrar consulta' ?></h2>
        <p class="role-note">Paciente: <strong><?= htmlspecialchars($appointment['paciente']) ?></strong> · Cita <?= date('d/m/Y', strtotime($appointment['fecha_cita'])) ?> a las <?= substr($appointment['hora_cita'], 0, 5) ?></p>
        <form method="post">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf_consulta']) ?>">
            <input type="hidden" name="action" value="guardar">
            <input type="hidden" name="cita_id" value="<?= (int) $appointment['id_cita'] ?>">
            <?php if ($editing): ?><input type="hidden" name="consulta_id" value="<?= (int) $editing['id_consulta'] ?>"><?php endif; ?>
            <div class="form-grid">
                <div class="form-group"><label>Motivo de consulta *</label><textarea class="form-control" name="motivo_consulta" required><?= htmlspecialchars($_POST['motivo_consulta'] ?? $formData['motivo_consulta'] ?? '') ?></textarea></div>
                <div class="form-group"><label>Diagnóstico *</label><textarea class="form-control" name="diagnostico" required><?= htmlspecialchars($_POST['diagnostico'] ?? $formData['diagnostico'] ?? '') ?></textarea></div>
                <div class="form-group"><label>Tratamiento</label><textarea class="form-control" name="tratamiento"><?= htmlspecialchars($_POST['tratamiento'] ?? $formData['tratamiento'] ?? '') ?></textarea></div>
                <div class="form-group"><label>Medicamentos prescritos</label><textarea class="form-control" name="medicamentos_prescritos"><?= htmlspecialchars($_POST['medicamentos_prescritos'] ?? $formData['medicamentos_prescritos'] ?? '') ?></textarea></div>
                <div class="form-group"><label>Observaciones</label><textarea class="form-control" name="observaciones"><?= htmlspecialchars($_POST['observaciones'] ?? $formData['observaciones'] ?? '') ?></textarea></div>
                <div class="form-group"><label>Próxima cita</label><input class="form-control" type="date" name="proxima_cita" value="<?= htmlspecialchars($_POST['proxima_cita'] ?? $formData['proxima_cita'] ?? '') ?>"></div>
            </div>
            <button class="btn btn-primary">Guardar consulta</button> <a class="btn btn-secondary" href="consultas.php">Cancelar</a>
        </form>
    </section>
<?php endif; ?>

<section class="content-section mt-20">
    <h2>Consultas pendientes de registrar</h2>
    <?php if (!$availableAppointments): ?>
        <p class="muted">No hay citas completadas pendientes de registrar.</p>
    <?php else: ?>
        <div class="table-responsive"><table class="table"><thead><tr><th>Fecha</th><th>Hora</th><th>Paciente</th><th>Acción</th></tr></thead><tbody>
        <?php foreach ($availableAppointments as $available): ?><tr><td><?= date('d/m/Y', strtotime($available['fecha_cita'])) ?></td><td><?= substr($available['hora_cita'], 0, 5) ?></td><td><?= htmlspecialchars($available['paciente']) ?></td><td><a class="btn btn-sm btn-primary" href="?cita_id=<?= (int) $available['id_cita'] ?>">Registrar consulta</a></td></tr><?php endforeach; ?>
        </tbody></table></div>
    <?php endif; ?>
</section>

<section class="content-section mt-20">
    <h2>Mis consultas</h2>
    <?php if (!$consultations): ?>
        <p class="muted">Aún no tienes consultas registradas.</p>
    <?php else: ?>
        <div class="table-responsive"><table class="table"><thead><tr><th>Fecha</th><th>Paciente</th><th>Motivo</th><th>Diagnóstico</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>
        <?php foreach ($consultations as $consultation): ?><tr>
            <td><?= date('d/m/Y H:i', strtotime($consultation['fecha_consulta'])) ?></td><td><?= htmlspecialchars($consultation['paciente']) ?></td><td><?= htmlspecialchars($consultation['motivo_consulta']) ?></td><td><?= htmlspecialchars($consultation['diagnostico']) ?></td>
            <td><span class="badge badge-<?= htmlspecialchars($consultation['estado_consulta']) ?>"><?= htmlspecialchars(ucfirst($consultation['estado_consulta'])) ?></span></td>
            <td class="actions"><?php if ($consultation['estado_consulta'] === 'pendiente'): ?><a class="btn btn-sm btn-secondary" href="?edit=<?= (int) $consultation['id_consulta'] ?>">Editar</a><form method="post"><input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf_consulta']) ?>"><input type="hidden" name="action" value="completar"><input type="hidden" name="consulta_id" value="<?= (int) $consultation['id_consulta'] ?>"><button class="btn btn-sm btn-primary">Completar consulta</button></form><?php else: ?><span class="muted">Sin acción</span><?php endif; ?></td>
        </tr><?php endforeach; ?>
        </tbody></table></div>
    <?php endif; ?>
</section>
<style>.page-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;border-bottom:2px solid var(--gray-300);padding-bottom:16px}.content-section{background:#fff;padding:20px;border-radius:8px;box-shadow:var(--box-shadow)}.form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:0 20px}.actions{display:flex;gap:8px;align-items:center}.actions form{margin:0}.role-note{background:#e8f5e9;color:#1b5e20;padding:12px;border-radius:6px;margin-bottom:16px}.muted{color:var(--gray-500);font-size:13px}@media(max-width:650px){.form-grid{grid-template-columns:1fr}.actions{align-items:flex-start;flex-direction:column}}</style>
<?php include __DIR__ . '/../includes/footer.php'; ?>
