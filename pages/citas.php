<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
checkSession();

// Recepción y Doctores usan una pantalla limitada para no modificar citas ajenas.
if (in_array($_SESSION['user_role'], [ROLE_RECEPCIONISTA, ROLE_DOCTOR], true) && !isset($_GET['new'])) {
    require __DIR__ . '/citas_operacion.php';
    exit();
}

$module = 'citas';
require_once __DIR__ . '/../includes/module_view.php';
