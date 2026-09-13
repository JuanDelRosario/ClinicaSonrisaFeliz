<?php
/** Gestión de estados de citas, restringida al rol que está conectado. */
if (!checkPermission('citas')) { header('Location: ' . BASE_URL . '/index.php'); exit(); }

$role = (int) $_SESSION['user_role'];
$doctorId = 0;
if ($role === ROLE_DOCTOR) {
    $doctor = getRecord('SELECT id_doctor FROM doctores WHERE id_usuario = ?', [(int) $_SESSION['user_id']]);
    $doctorId = (int) ($doctor['id_doctor'] ?? 0);
    if (!$doctorId) exit('No existe un perfil de doctor vinculado a esta cuenta.');
}
if (empty($_SESSION['csrf_estado_cita'])) $_SESSION['csrf_estado_cita'] = bin2hex(random_bytes(32));
$message = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_estado_cita'], $_POST['csrf'] ?? '')) {
        $error = 'La solicitud expiró. Intente nuevamente.';
    } else {
        $appointmentId = (int) ($_POST['cita_id'] ?? 0);
        $action = $_POST['action'] ?? '';
        try {
            if ($role === ROLE_RECEPCIONISTA && $action === 'confirmar') {
                // Solo Recepción confirma una cita que aún está programada.
                executeQuery("UPDATE citas SET estado_cita = 'confirmada' WHERE id_cita = ? AND estado_cita = 'programada'", [$appointmentId]);
                $message = 'La cita fue confirmada.';
            } elseif ($role === ROLE_DOCTOR && $action === 'completar') {
                // El Doctor solo finaliza sus propias citas ya confirmadas.
                executeQuery("UPDATE citas SET estado_cita = 'completada' WHERE id_cita = ? AND id_doctor = ? AND estado_cita = 'confirmada'", [$appointmentId, $doctorId]);
                $message = 'La cita fue marcada como completada.';
            } else {
                $error = 'No tienes permiso para realizar esta acción.';
            }
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            $error = 'No fue posible actualizar el estado de la cita.';
        }
    }
}

$params = [];
$where = '';
if ($role === ROLE_DOCTOR) { $where = 'WHERE c.id_doctor = ?'; $params[] = $doctorId; }
$appointments = getRecords("SELECT c.id_cita, c.fecha_cita, c.hora_cita, c.estado_cita, CONCAT(p.nombre, ' ', p.apellido) paciente, CONCAT(d.nombre, ' ', d.apellido) doctor, e.nombre_especialidad, s.nombre_servicio FROM citas c JOIN pacientes p ON p.id_paciente=c.id_paciente JOIN doctores d ON d.id_doctor=c.id_doctor JOIN especialidades e ON e.id_especialidad=d.id_especialidad JOIN servicios s ON s.id_servicio=c.id_servicio $where ORDER BY c.fecha_cita, c.hora_cita", $params);
include __DIR__ . '/../includes/header.php';
?>
<div class="page-header"><div><h1><?=$role === ROLE_RECEPCIONISTA ? 'Confirmación de citas' : 'Mis citas de atención'?></h1><p><?=$role === ROLE_RECEPCIONISTA ? 'Confirma las citas programadas para informar al paciente.' : 'Finaliza una cita cuando la atención haya terminado.'?></p></div><?php if($role === ROLE_RECEPCIONISTA): ?><a class="btn btn-primary" href="?new=1">Nueva cita</a><?php endif; ?></div>
<?php if($message): ?><div class="alert alert-success"><?=htmlspecialchars($message)?></div><?php endif; ?><?php if($error): ?><div class="alert alert-danger"><?=htmlspecialchars($error)?></div><?php endif; ?>
<section class="content-section"><div class="role-note"><?=$role === ROLE_RECEPCIONISTA ? 'Recepción puede confirmar únicamente citas programadas.' : 'Solo puedes completar tus citas que ya fueron confirmadas por Recepción.'?></div><?php if(!$appointments): ?><div class="alert alert-info">No hay citas para mostrar.</div><?php else: ?><div class="table-responsive"><table class="table"><thead><tr><th>Fecha</th><th>Hora</th><th>Paciente</th><th>Médico</th><th>Especialidad</th><th>Servicio</th><th>Estado</th><th>Acción</th></tr></thead><tbody><?php foreach($appointments as $appointment): ?><tr><td><?=date('d/m/Y',strtotime($appointment['fecha_cita']))?></td><td><?=substr($appointment['hora_cita'],0,5)?></td><td><?=htmlspecialchars($appointment['paciente'])?></td><td><?=htmlspecialchars($appointment['doctor'])?></td><td><?=htmlspecialchars($appointment['nombre_especialidad'])?></td><td><?=htmlspecialchars($appointment['nombre_servicio'])?></td><td><span class="badge badge-<?=htmlspecialchars($appointment['estado_cita'])?>"><?=htmlspecialchars($appointment['estado_cita'])?></span></td><td><?php if($role === ROLE_RECEPCIONISTA && $appointment['estado_cita'] === 'programada'): ?><form method="post"><input type="hidden" name="csrf" value="<?=htmlspecialchars($_SESSION['csrf_estado_cita'])?>"><input type="hidden" name="action" value="confirmar"><input type="hidden" name="cita_id" value="<?=(int)$appointment['id_cita']?>"><button class="btn btn-sm btn-primary">Confirmar</button></form><?php elseif($role === ROLE_DOCTOR && $appointment['estado_cita'] === 'confirmada'): ?><form method="post"><input type="hidden" name="csrf" value="<?=htmlspecialchars($_SESSION['csrf_estado_cita'])?>"><input type="hidden" name="action" value="completar"><input type="hidden" name="cita_id" value="<?=(int)$appointment['id_cita']?>"><button class="btn btn-sm btn-primary">Completar cita</button></form><?php else: ?><span class="muted">Sin acción</span><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section>
<style>.page-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;border-bottom:2px solid var(--gray-300);padding-bottom:16px}.content-section{background:#fff;padding:20px;border-radius:8px;box-shadow:var(--box-shadow)}.role-note{background:#e8f5e9;color:#1b5e20;padding:12px;border-radius:6px;margin-bottom:16px}.muted{color:var(--gray-500);font-size:12px}@media(max-width:650px){.page-header{align-items:flex-start;gap:12px;flex-direction:column}}</style>
<?php include __DIR__ . '/../includes/footer.php'; ?>
