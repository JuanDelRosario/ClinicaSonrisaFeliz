<?php
/** Funciones compartidas para calcular horarios de citas sin duplicar reglas. */

// Devuelve los horarios libres de un doctor para un servicio y una fecha.
function availableAppointmentSlots(int $doctorId, string $date, int $serviceId, int $excludeAppointmentId = 0): array {
    $doctor = getRecord("SELECT horario_entrada, horario_salida FROM doctores WHERE id_doctor = ? AND estado = 'activo'", [$doctorId]);
    $service = getRecord("SELECT duracion_minutos FROM servicios WHERE id_servicio = ? AND estado = 'activo'", [$serviceId]);
    if (!$doctor || !$service || !$doctor['horario_entrada'] || !$doctor['horario_salida']) return [];

    $duration = max(15, (int) $service['duracion_minutos']);
    $appointments = getRecords("SELECT c.hora_cita, s.duracion_minutos FROM citas c JOIN servicios s ON s.id_servicio = c.id_servicio WHERE c.id_doctor = ? AND c.fecha_cita = ? AND c.estado_cita NOT IN ('cancelada', 'no_asistio') AND c.id_cita <> ?", [$doctorId, $date, $excludeAppointmentId]);
    $busy = [];
    foreach ($appointments as $appointment) {
        $start = strtotime($date . ' ' . $appointment['hora_cita']);
        $busy[] = [$start, $start + (max(15, (int) $appointment['duracion_minutos']) * 60)];
    }

    $open = strtotime($date . ' ' . $doctor['horario_entrada']);
    $close = strtotime($date . ' ' . $doctor['horario_salida']);
    $slots = [];
    for ($candidate = $open; $candidate + ($duration * 60) <= $close; $candidate += 1800) {
        $free = true;
        foreach ($busy as [$busyStart, $busyEnd]) {
            if ($candidate < $busyEnd && ($candidate + ($duration * 60)) > $busyStart) { $free = false; break; }
        }
        if ($free) $slots[] = date('H:i', $candidate);
    }
    return $slots;
}
