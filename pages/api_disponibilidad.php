<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/appointment_helpers.php';
checkSession();
header('Content-Type: application/json; charset=utf-8');

if ($_SESSION['user_role'] != ROLE_PACIENTE) { http_response_code(403); echo json_encode(['error' => 'No autorizado']); exit(); }
$doctorId = (int) ($_GET['doctor_id'] ?? 0);
$serviceId = (int) ($_GET['servicio_id'] ?? 0);
$date = $_GET['fecha'] ?? '';
$exclude = (int) ($_GET['cita_id'] ?? 0);
if (!$doctorId || !$serviceId || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || $date < date('Y-m-d')) { echo json_encode(['slots' => []]); exit(); }
echo json_encode(['slots' => availableAppointmentSlots($doctorId, $date, $serviceId, $exclude)]);
