<?php
/**
 * Logout
 * Cierra la sesión del usuario
 */

require_once '../config/config.php';

// Destruir la sesión
session_destroy();

// Redirigir al login
header('Location: ' . BASE_URL . '/auth/login.php?logout=1');
exit();

?>
