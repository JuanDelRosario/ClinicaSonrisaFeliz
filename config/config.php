<?php
/**
 * Configuración General
 * Clínica Dental "Mi Sonrisa Feliz"
 */

// Fuerza UTF-8 en todas las respuestas PHP antes de iniciar la sesión.
ini_set('default_charset', 'UTF-8');
session_start();

/** Prepara texto para HTML conservando acentos, ñ y otros caracteres Unicode. */
function displayText($value): string {
    $text = (string) ($value ?? '');
    if (function_exists('mb_check_encoding') && !mb_check_encoding($text, 'UTF-8')) {
        $text = mb_convert_encoding($text, 'UTF-8', 'ISO-8859-1');
    } elseif (function_exists('mb_convert_encoding') && preg_match('/(?:Ã.|Â.|â.)/u', $text)) {
        // Repara textos antiguos que fueron codificados dos veces.
        $repaired = mb_convert_encoding($text, 'ISO-8859-1', 'UTF-8');
        if (mb_check_encoding($repaired, 'UTF-8')) {
            $text = $repaired;
        }
    }
    return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Información de la clínica
define('CLINIC_NAME', 'Clínica Dental Mi Sonrisa Feliz');
define('CLINIC_EMAIL', 'info@clinicasonrisafeliz.com');
define('CLINIC_PHONE', '+1 (849) 282-5216');
define('CLINIC_ADDRESS', 'Carrera Vieja de Sabana Perdida, Santo Domingo Norte, República Dominicana');

// Rutas del proyecto
define('BASE_URL', 'http://localhost/ClinicaSonrisaFeliz');
define('BASE_PATH', __DIR__ . '/../');
define('ASSETS_URL', BASE_URL . '/assets');

// Configuración de sesión
define('SESSION_TIMEOUT', 3600); // 1 hora en segundos

// Roles disponibles
define('ROLE_ADMIN', 1);
define('ROLE_DOCTOR', 2);
define('ROLE_RECEPCIONISTA', 3);
define('ROLE_PACIENTE', 4);
define('ROLE_CONTADOR', 5);

// Mapeo de roles
$ROLES = [
    1 => 'Administrador',
    2 => 'Doctor',
    3 => 'Recepcionista',
    4 => 'Paciente',
    5 => 'Contador'
];

// Permisos por rol
$PERMISSIONS = [
    1 => [ // Administrador
        'usuarios', 'doctores', 'pacientes', 'citas', 'consultas',
        'facturacion', 'inventario', 'reportes', 'auditorias'
    ],
    2 => [ // Doctor
        'citas', 'consultas', 'historial_paciente', 'servicios'
    ],
    3 => [ // Recepcionista
        'usuarios', 'pacientes', 'citas', 'facturacion'
    ],
    4 => [ // Paciente
        'mis_citas', 'mis_consultas', 'mis_facturas'
    ],
    5 => [ // Contador
        'facturacion', 'historial_facturacion', 'reportes', 'nomina'
    ]
];

// Función para verificar sesión
function checkSession() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
        header('Location: ' . BASE_URL . '/auth/login.php');
        exit();
    }

    // Verificar timeout de sesión
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        session_destroy();
        header('Location: ' . BASE_URL . '/auth/login.php?timeout=1');
        exit();
    }

    $_SESSION['last_activity'] = time();
}

// Función para verificar permiso
function checkPermission($permission) {
    global $PERMISSIONS;

    if (!isset($_SESSION['user_role'])) {
        return false;
    }

    $role_id = $_SESSION['user_role'];
    
    return in_array($permission, $PERMISSIONS[$role_id] ?? []);
}

// Función para obtener rol del usuario
function getUserRole() {
    return $_SESSION['user_role'] ?? null;
}

// Función para obtener nombre del rol
function getRoleName($role_id) {
    global $ROLES;
    return $ROLES[$role_id] ?? 'Desconocido';
}

// Función para encriptar contraseña
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

// Función para verificar contraseña
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

?>
