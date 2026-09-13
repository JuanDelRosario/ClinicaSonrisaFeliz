<?php
/**
 * Script de Autenticación
 * Procesa el login del usuario
 */

require_once '../config/database.php';
require_once '../config/config.php';

$response = [
    'success' => false,
    'message' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['usuario_login'] ?? '');
    $password = $_POST['contraseña'] ?? '';

    // Validar datos
    if (empty($username) || empty($password)) {
        $response['message'] = 'Por favor ingrese usuario y contraseña';
    } else {
        try {
            // Buscar usuario
            $sql = "SELECT u.id_usuario, u.usuario_login, u.password_hash, u.nombre, u.apellido, 
                           u.id_rol, r.nombre_rol, u.estado 
                    FROM usuarios u 
                    JOIN roles r ON u.id_rol = r.id_rol
                    WHERE u.usuario_login = ? AND u.estado = 'activo'";
            
            $user = getRecord($sql, [$username]);

            if (!$user) {
                $response['message'] = 'Usuario o contraseña incorrectos';
            } elseif (!verifyPassword($password, $user['password_hash'])) {
                $response['message'] = 'Usuario o contraseña incorrectos';
            } else {
                // Login exitoso
                $_SESSION['user_id'] = $user['id_usuario'];
                $_SESSION['user_login'] = $user['usuario_login'];
                $_SESSION['user_name'] = $user['nombre'] . ' ' . $user['apellido'];
                $_SESSION['user_role'] = $user['id_rol'];
                $_SESSION['user_role_name'] = $user['nombre_rol'];
                $_SESSION['last_activity'] = time();

                $response['success'] = true;
                $response['message'] = 'Login exitoso';
                $response['redirect'] = BASE_URL . '/index.php';
            }
        } catch (Exception $e) {
            $response['message'] = 'Error al procesar el login: ' . $e->getMessage();
        }
    }
} else {
    $response['message'] = 'Método de solicitud inválido';
}

header('Content-Type: application/json');
echo json_encode($response);

?>
