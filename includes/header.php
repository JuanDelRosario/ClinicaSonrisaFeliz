<?php
/**
 * Header / Encabezado
 * Componente reutilizable
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/ClinicaSonrisaFeliz/config/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ClinicaSonrisaFeliz/config/database.php';
checkSession();
header('Content-Type: text/html; charset=UTF-8');

// Sincroniza el nombre de la sesión con UTF-8 desde la base de datos.
// Esto evita conservar textos dañados si la sesión se inició antes de una corrección de codificación.
$currentUser = getRecord('SELECT nombre, apellido FROM usuarios WHERE id_usuario = ?', [(int) $_SESSION['user_id']]);
if ($currentUser) {
    $_SESSION['user_name'] = trim($currentUser['nombre'] . ' ' . $currentUser['apellido']);
}

// Actualiza la URL de recursos después de cada cambio para evitar caché obsoleta.
$assetsVersion = (string) max(
    filemtime(BASE_PATH . 'assets/css/style.css') ?: 0,
    filemtime(BASE_PATH . 'assets/css/dashboard.css') ?: 0,
    filemtime(BASE_PATH . 'assets/js/i18n.js') ?: 0
);
$logoVersion = (string) (filemtime(BASE_PATH . 'assets/images/logo-mi-sonrisa-feliz.png') ?: 0);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control - <?php echo displayText(CLINIC_NAME); ?></title>
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/style.css?v=<?php echo $assetsVersion; ?>">
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/dashboard.css?v=<?php echo $assetsVersion; ?>">
</head>
<body>
    <div class="layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <img class="clinic-logo-small" src="<?php echo ASSETS_URL; ?>/images/logo-mi-sonrisa-feliz.png?v=<?php echo $logoVersion; ?>" alt="Logo de Mi Sonrisa Feliz">
                <h2>Mi Sonrisa Feliz</h2>
                <button class="sidebar-toggle" id="sidebarToggle">☰</button>
            </div>

            <nav class="sidebar-nav">
                <!-- Navegación por rol -->
                <?php
                $role_id = $_SESSION['user_role'];
                
                // Menú base para todos
                $menu = [
                    'dashboard' => [
                        'label' => 'Dashboard',
                        'url' => BASE_URL . '/index.php',
                        'icon' => '📊'
                    ]
                ];

                // Menú específico por rol
                if ($role_id == ROLE_ADMIN) {
                    $menu += [
                        'usuarios' => ['label' => 'Usuarios', 'url' => BASE_URL . '/pages/usuarios.php', 'icon' => 'U'],
                        'doctores' => ['label' => 'Doctores', 'url' => BASE_URL . '/pages/doctores.php', 'icon' => 'D'],
                        'especialidades' => ['label' => 'Especialidades', 'url' => BASE_URL . '/pages/especialidades.php', 'icon' => 'E'],
                        'citas' => ['label' => 'Citas', 'url' => BASE_URL . '/pages/citas.php', 'icon' => 'C'],
                        'consultas' => ['label' => 'Consultas', 'url' => BASE_URL . '/pages/consultas.php', 'icon' => 'Co'],
                        'facturacion' => ['label' => 'Facturación', 'url' => BASE_URL . '/pages/facturacion.php', 'icon' => 'F'],
                        'inventario' => ['label' => 'Inventario', 'url' => BASE_URL . '/pages/inventario.php', 'icon' => 'I'],
                        'compras' => ['label' => 'Compras', 'url' => BASE_URL . '/pages/compras.php', 'icon' => 'C'],
                        'reportes' => ['label' => 'Reportes', 'url' => BASE_URL . '/pages/reportes.php', 'icon' => 'R'],
                        'servicios' => ['label' => 'Servicios', 'url' => BASE_URL . '/pages/servicios.php', 'icon' => 'S'],
                        'pacientes' => ['label' => 'Pacientes', 'url' => BASE_URL . '/pages/pacientes.php', 'icon' => '🧑‍🦱'],
                        // Próximas funcionalidades en desarrollo:
                        // 'usuarios' => ['label' => 'Usuarios', 'url' => BASE_URL . '/pages/usuarios.php', 'icon' => '👥'],
                        // 'doctores' => ['label' => 'Doctores', 'url' => BASE_URL . '/pages/doctores.php', 'icon' => '👨‍⚕️'],
                        // 'especialidades' => ['label' => 'Especialidades', 'url' => BASE_URL . '/pages/especialidades.php', 'icon' => '🔬'],
                        // 'citas' => ['label' => 'Citas', 'url' => BASE_URL . '/pages/citas.php', 'icon' => '📅'],
                        // 'consultas' => ['label' => 'Consultas', 'url' => BASE_URL . '/pages/consultas.php', 'icon' => '📋'],
                        // 'facturacion' => ['label' => 'Facturación', 'url' => BASE_URL . '/pages/facturacion.php', 'icon' => '💰'],
                        // 'inventario' => ['label' => 'Inventario', 'url' => BASE_URL . '/pages/inventario.php', 'icon' => '📦'],
                        // 'reportes' => ['label' => 'Reportes', 'url' => BASE_URL . '/pages/reportes.php', 'icon' => '📈']
                    ];
                } elseif ($role_id == ROLE_DOCTOR) {
                    $menu += [
                        'citas' => ['label' => 'Citas', 'url' => BASE_URL . '/pages/citas.php', 'icon' => 'C'],
                        'consultas' => ['label' => 'Consultas', 'url' => BASE_URL . '/pages/consultas.php', 'icon' => 'Co'],
                        // Próximas funcionalidades en desarrollo:
                        // 'citas' => ['label' => 'Mis Citas', 'url' => BASE_URL . '/pages/mis_citas.php', 'icon' => '📅'],
                        // 'consultas' => ['label' => 'Mis Consultas', 'url' => BASE_URL . '/pages/mis_consultas.php', 'icon' => '📋'],
                        // 'pacientes' => ['label' => 'Mis Pacientes', 'url' => BASE_URL . '/pages/mis_pacientes.php', 'icon' => '🧑‍🦱'],
                        // 'historial' => ['label' => 'Historial Clínico', 'url' => BASE_URL . '/pages/historial.php', 'icon' => '📂']
                    ];
                } elseif ($role_id == ROLE_RECEPCIONISTA) {
                    $menu += [
                        'usuarios' => ['label' => 'Usuarios', 'url' => BASE_URL . '/pages/usuarios.php', 'icon' => 'U'],
                        'citas' => ['label' => 'Citas', 'url' => BASE_URL . '/pages/citas.php', 'icon' => 'C'],
                        'facturacion' => ['label' => 'Facturación', 'url' => BASE_URL . '/pages/facturacion.php', 'icon' => 'F'],
                        'pacientes' => ['label' => 'Pacientes', 'url' => BASE_URL . '/pages/pacientes.php', 'icon' => '🧑‍🦱'],
                        // Próximas funcionalidades en desarrollo:
                        // 'citas' => ['label' => 'Citas', 'url' => BASE_URL . '/pages/citas.php', 'icon' => '📅'],
                        // 'facturacion' => ['label' => 'Facturación', 'url' => BASE_URL . '/pages/facturacion.php', 'icon' => '💰']
                    ];
                } elseif ($role_id == ROLE_PACIENTE) {
                    $menu += [
                        'mis_citas' => ['label' => 'Mis Citas', 'url' => BASE_URL . '/pages/mis_citas.php', 'icon' => 'C'],
                        'perfil' => ['label' => 'Mi Perfil', 'url' => BASE_URL . '/pages/perfil.php', 'icon' => 'P'],
                        // Próximas funcionalidades en desarrollo:
                        // 'mis_citas' => ['label' => 'Mis Citas', 'url' => BASE_URL . '/pages/mis_citas.php', 'icon' => '📅'],
                        // 'mis_consultas' => ['label' => 'Mis Consultas', 'url' => BASE_URL . '/pages/mis_consultas.php', 'icon' => '📋'],
                        // 'mis_facturas' => ['label' => 'Mis Facturas', 'url' => BASE_URL . '/pages/mis_facturas.php', 'icon' => '🧾'],
                        // 'perfil' => ['label' => 'Mi Perfil', 'url' => BASE_URL . '/pages/perfil.php', 'icon' => '👤']
                    ];
                } elseif ($role_id == ROLE_CONTADOR) {
                    $menu += [
                        'facturacion' => ['label' => 'Facturación', 'url' => BASE_URL . '/pages/facturacion.php', 'icon' => 'F'],
                        'pagos' => ['label' => 'Pagos', 'url' => BASE_URL . '/pages/pagos.php', 'icon' => 'P'],
                        'compras' => ['label' => 'Compras', 'url' => BASE_URL . '/pages/compras.php', 'icon' => 'C'],
                        'nomina' => ['label' => 'Nómina', 'url' => BASE_URL . '/pages/nomina.php', 'icon' => 'N'],
                        'reportes' => ['label' => 'Reportes', 'url' => BASE_URL . '/pages/reportes.php', 'icon' => 'R'],
                        // Próximas funcionalidades en desarrollo:
                        // 'facturacion' => ['label' => 'Facturación', 'url' => BASE_URL . '/pages/facturacion.php', 'icon' => '💰'],
                        // 'pagos' => ['label' => 'Pagos', 'url' => BASE_URL . '/pages/pagos.php', 'icon' => '💳'],
                        // 'reportes' => ['label' => 'Reportes', 'url' => BASE_URL . '/pages/reportes.php', 'icon' => '📈']
                    ];
                }

                foreach ($menu as $key => $item):
                ?>
                    <a href="<?php echo $item['url']; ?>" class="nav-link">
                        <span class="nav-icon"><?php echo $item['icon']; ?></span>
                        <span class="nav-label"><?php echo $item['label']; ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['user_name'], 0, 2)); ?>
                    </div>
                    <div class="user-details">
                        <div class="user-name"><?php echo displayText($_SESSION['user_name']); ?></div>
                        <div class="user-role"><?php echo displayText($_SESSION['user_role_name']); ?></div>
                    </div>
                </div>
                <a href="<?php echo BASE_URL; ?>/auth/logout.php" class="btn-logout">Salir</a>
            </div>
        </aside>

        <!-- Topbar -->
        <div class="topbar">
            <div class="topbar-left">
                <button class="btn-menu" id="menuToggle">☰</button>
                <div class="breadcrumb">
                    <!-- Será actualizado por JavaScript -->
                </div>
            </div>

            <div class="topbar-right">
                <div class="language-switcher">
                    <label class="sr-only" for="languageSelect">Idioma / Language</label>
                    <select id="languageSelect" class="language-select" aria-label="Select language">
                        <option value="es">Español</option>
                        <option value="en">English</option>
                    </select>
                </div>
                <div class="notification-bell">
                    <button class="btn-notification">🔔</button>
                    <span class="notification-count">3</span>
                </div>
                <div class="user-menu">
                    <button class="btn-user-menu"><?php echo displayText($_SESSION['user_name']); ?> ▼</button>
                    <div class="user-dropdown" style="display: none;">
                        <a href="<?php echo BASE_URL; ?>/pages/perfil.php">👤 Mi Perfil</a>
                        <a href="<?php echo BASE_URL; ?>/pages/configuracion.php">⚙️ Configuración</a>
                        <hr>
                        <a href="<?php echo BASE_URL; ?>/auth/logout.php">🚪 Salir</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenido Principal -->
        <main class="main-content">
