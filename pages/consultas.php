<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
checkSession();

// El médico usa una vista protegida: solo puede gestionar sus propias consultas.
if ($_SESSION['user_role'] === ROLE_DOCTOR) {
    require __DIR__ . '/consultas_operacion.php';
    exit();
}

$module = 'consultas';
require_once __DIR__ . '/../includes/module_view.php';
