<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
checkSession();

if ($_SESSION['user_role'] == ROLE_RECEPCIONISTA) {
    require __DIR__ . '/usuarios_recepcion.php';
    exit();
}

$module = 'usuarios';
require_once __DIR__ . '/../includes/module_view.php';
