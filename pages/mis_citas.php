<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/appointment_helpers.php';
checkSession();
if ($_SESSION['user_role'] != ROLE_PACIENTE || !checkPermission('mis_citas')) { header('Location: ' . BASE_URL . '/index.php'); exit(); }

$patient = getRecord('SELECT id_paciente FROM pacientes WHERE id_usuario = ?', [(int) $_SESSION['user_id']]);
if (!$patient) { exit('No existe una ficha de paciente vinculada a esta cuenta.'); }
$patientId = (int) $patient['id_paciente'];
if (empty($_SESSION['csrf_citas'])) $_SESSION['csrf_citas'] = bin2hex(random_bytes(32));
$message = ''; $error = ''; $editing = null;

if (isset($_GET['edit'])) {
    $editing = getRecord("SELECT * FROM citas WHERE id_cita = ? AND id_paciente = ? AND fecha_cita >= CURDATE() AND estado_cita IN ('programada', 'confirmada')", [(int) $_GET['edit'], $patientId]);
    if (!$editing) $error = 'La cita solicitada no puede modificarse.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_citas'], $_POST['csrf'] ?? '')) {
        $error = 'La solicitud expiró. Intente nuevamente.';
    } else {
        $doctorId = (int) ($_POST['id_doctor'] ?? 0); $serviceId = (int) ($_POST['id_servicio'] ?? 0);
        $date = $_POST['fecha_cita'] ?? ''; $time = $_POST['hora_cita'] ?? ''; $appointmentId = (int) ($_POST['id_cita'] ?? 0);
        $owned = !$appointmentId || getRecord("SELECT id_cita FROM citas WHERE id_cita = ? AND id_paciente = ? AND fecha_cita >= CURDATE() AND estado_cita IN ('programada', 'confirmada')", [$appointmentId, $patientId]);
        if (!$doctorId || !$serviceId || !$date || !$time || $date < date('Y-m-d') || !$owned) {
            $error = 'Revise los datos de la cita.';
        } elseif (!in_array($time, availableAppointmentSlots($doctorId, $date, $serviceId, $appointmentId), true)) {
            $error = 'El horario seleccionado ya no está disponible. Elija otro horario.';
        } else {
            $data = ['id_doctor' => $doctorId, 'id_servicio' => $serviceId, 'fecha_cita' => $date, 'hora_cita' => $time, 'notas' => trim($_POST['notas'] ?? '')];
            if ($appointmentId) { updateRecord('citas', $data, 'id_cita', $appointmentId); $message = 'Tu cita fue reprogramada correctamente.'; }
            else { $data['id_paciente'] = $patientId; $data['estado_cita'] = 'programada'; insertRecord('citas', $data); $message = 'Tu cita fue reservada correctamente.'; }
            $editing = null;
        }
    }
}

$doctors = getRecords("SELECT d.id_doctor, CONCAT(d.nombre, ' ', d.apellido) nombre, e.nombre_especialidad especialidad, d.horario_entrada, d.horario_salida FROM doctores d JOIN especialidades e ON e.id_especialidad = d.id_especialidad WHERE d.estado = 'activo' ORDER BY d.nombre");
$services = getRecords("SELECT id_servicio, nombre_servicio, duracion_minutos FROM servicios WHERE estado = 'activo' ORDER BY nombre_servicio");
$appointments = getRecords("SELECT c.*, CONCAT(d.nombre, ' ', d.apellido) doctor, e.nombre_especialidad, s.nombre_servicio FROM citas c JOIN doctores d ON d.id_doctor=c.id_doctor JOIN especialidades e ON e.id_especialidad=d.id_especialidad JOIN servicios s ON s.id_servicio=c.id_servicio WHERE c.id_paciente=? ORDER BY c.fecha_cita, c.hora_cita", [$patientId]);
include __DIR__ . '/../includes/header.php';
?>
<div class="page-header"><div><h1>Mis citas</h1><p>Elige un médico, revisa su especialidad y reserva un horario disponible.</p></div><a class="btn btn-primary" href="?new=1">Agendar cita</a></div>
<?php if($message): ?><div class="alert alert-success"><?=htmlspecialchars($message)?></div><?php endif; ?><?php if($error): ?><div class="alert alert-danger"><?=htmlspecialchars($error)?></div><?php endif; ?>
<section class="content-section doctors"><h2>Médicos disponibles</h2><div class="doctor-grid"><?php foreach($doctors as $doctor): ?><article><strong><?=htmlspecialchars($doctor['nombre'])?></strong><span><?=htmlspecialchars($doctor['especialidad'])?></span><small>Horario: <?=substr($doctor['horario_entrada'],0,5)?> - <?=substr($doctor['horario_salida'],0,5)?></small></article><?php endforeach; ?></div></section>
<?php if(isset($_GET['new']) || $editing): ?><section class="content-section mt-20"><h2><?=$editing ? 'Cambiar cita' : 'Agendar nueva cita'?></h2><form method="post" id="appointmentForm"><input type="hidden" name="csrf" value="<?=htmlspecialchars($_SESSION['csrf_citas'])?>"><?php if($editing): ?><input type="hidden" name="id_cita" value="<?=(int)$editing['id_cita']?>"><?php endif; ?><div class="form-grid"><div class="form-group"><label>Médico *</label><select class="form-control" id="doctor" name="id_doctor" required><option value="">Seleccione un médico</option><?php foreach($doctors as $doctor): ?><option value="<?=(int)$doctor['id_doctor']?>" <?=((int)($editing['id_doctor']??0)==(int)$doctor['id_doctor'])?'selected':''?>><?=htmlspecialchars($doctor['nombre'].' — '.$doctor['especialidad'])?></option><?php endforeach; ?></select></div><div class="form-group"><label>Servicio *</label><select class="form-control" id="service" name="id_servicio" required><option value="">Seleccione un servicio</option><?php foreach($services as $service): ?><option value="<?=(int)$service['id_servicio']?>" <?=((int)($editing['id_servicio']??0)==(int)$service['id_servicio'])?'selected':''?>><?=htmlspecialchars($service['nombre_servicio'].' ('.$service['duracion_minutos'].' min)')?></option><?php endforeach; ?></select></div><div class="form-group"><label>Fecha *</label><input class="form-control" id="date" type="date" name="fecha_cita" min="<?=date('Y-m-d')?>" value="<?=htmlspecialchars($editing['fecha_cita']??'')?>" required></div><div class="form-group"><label>Hora disponible *</label><div id="slots" class="slots"><span>Seleccione médico, servicio y fecha.</span></div><input type="hidden" id="time" name="hora_cita" value="<?=htmlspecialchars(isset($editing['hora_cita'])?substr($editing['hora_cita'],0,5):'')?>" required></div><div class="form-group full"><label>Notas</label><textarea class="form-control" name="notas" placeholder="Motivo de la cita o información adicional"><?=htmlspecialchars($editing['notas']??'')?></textarea></div></div><button class="btn btn-primary"><?=$editing?'Guardar cambio':'Reservar cita'?></button><a class="btn btn-secondary" href="?">Cancelar</a></form></section><?php endif; ?>
<section class="content-section mt-20"><h2>Historial y próximas citas</h2><?php if(!$appointments): ?><div class="alert alert-info">Aún no tienes citas registradas.</div><?php else: ?><div class="table-responsive"><table class="table"><thead><tr><th>Fecha</th><th>Hora</th><th>Médico</th><th>Especialidad</th><th>Servicio</th><th>Estado</th><th></th></tr></thead><tbody><?php foreach($appointments as $appointment): ?><tr><td><?=date('d/m/Y',strtotime($appointment['fecha_cita']))?></td><td><?=substr($appointment['hora_cita'],0,5)?></td><td><?=htmlspecialchars($appointment['doctor'])?></td><td><?=htmlspecialchars($appointment['nombre_especialidad'])?></td><td><?=htmlspecialchars($appointment['nombre_servicio'])?></td><td><span class="badge badge-<?=htmlspecialchars($appointment['estado_cita'])?>"><?=htmlspecialchars($appointment['estado_cita'])?></span></td><td><?php if($appointment['fecha_cita'] >= date('Y-m-d') && in_array($appointment['estado_cita'],['programada','confirmada'])): ?><a class="btn btn-sm btn-primary" href="?edit=<?=(int)$appointment['id_cita']?>">Cambiar</a><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section>
<style>.page-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;border-bottom:2px solid var(--gray-300);padding-bottom:16px}.content-section{background:#fff;padding:20px;border-radius:8px;box-shadow:var(--box-shadow)}.doctor-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:14px}.doctor-grid article{border:1px solid var(--gray-300);border-left:4px solid var(--primary-color);padding:14px;border-radius:6px;display:flex;flex-direction:column;gap:4px}.doctor-grid span{color:var(--primary-dark);font-weight:600}.doctor-grid small{color:var(--gray-600)}.form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:0 20px}.full{grid-column:1/-1}.slots{display:flex;flex-wrap:wrap;gap:8px;min-height:42px;padding:6px 0}.slot{border:1px solid var(--primary-color);background:#fff;color:var(--primary-dark);padding:7px 10px;border-radius:5px;cursor:pointer}.slot.selected,.slot:hover{background:var(--primary-color);color:#fff}@media(max-width:650px){.page-header{align-items:flex-start;gap:12px;flex-direction:column}.form-grid{grid-template-columns:1fr}}</style>
<script>document.addEventListener('DOMContentLoaded',()=>{const d=document.getElementById('doctor'),s=document.getElementById('service'),date=document.getElementById('date'),slots=document.getElementById('slots'),time=document.getElementById('time'),id=document.querySelector('[name=id_cita]')?.value||0;async function load(){time.value='';if(!d.value||!s.value||!date.value){slots.innerHTML='<span>Seleccione médico, servicio y fecha.</span>';return;}slots.textContent='Consultando horarios...';const q=new URLSearchParams({doctor_id:d.value,servicio_id:s.value,fecha:date.value,cita_id:id});const r=await fetch('api_disponibilidad.php?'+q);const data=await r.json();slots.innerHTML='';if(!data.slots?.length){slots.textContent='No hay horarios libres para esta fecha.';return;}data.slots.forEach(slot=>{const b=document.createElement('button');b.type='button';b.className='slot';b.textContent=slot;b.onclick=()=>{document.querySelectorAll('.slot').forEach(x=>x.classList.remove('selected'));b.classList.add('selected');time.value=slot;};slots.appendChild(b);});} [d,s,date].forEach(x=>x.addEventListener('change',load));if(d.value&&s.value&&date.value)load();});</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
