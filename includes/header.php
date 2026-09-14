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

// Iconos SVG locales: conservan una apariencia consistente sin depender de emojis o servicios externos.
if (!function_exists('uiIcon')) {
    function uiIcon(string $name, string $class = ''): string {
        $aliases = ['mis_citas' => 'citas'];
        $name = $aliases[$name] ?? $name;
        $icons = [
            'dashboard' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><path d="M14 17h7M14 21h7M14 13h7"/>',
            'usuarios' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
            'pacientes' => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0M19 8v6M16 11h6"/>',
            'doctores' => '<path d="M4 4h16v16H4z"/><path d="M12 7v10M7 12h10"/>',
            'especialidades' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2.1 2.1-.06-.06a1.7 1.7 0 0 0-1.88-.34 1.7 1.7 0 0 0-1.03 1.56V20h-3v-.09A1.7 1.7 0 0 0 10.7 18.35a1.7 1.7 0 0 0-1.88.34l-.06.06-2.1-2.1.06-.06A1.7 1.7 0 0 0 7.06 14.7 1.7 1.7 0 0 0 5.5 13.67H5v-3h.09A1.7 1.7 0 0 0 6.65 9.64a1.7 1.7 0 0 0-.34-1.88l-.06-.06 2.1-2.1.06.06a1.7 1.7 0 0 0 1.88.34A1.7 1.7 0 0 0 11.33 4.5V4h3v.09A1.7 1.7 0 0 0 15.36 5.65a1.7 1.7 0 0 0 1.88-.34l.06-.06 2.1 2.1-.06.06A1.7 1.7 0 0 0 19 9.3a1.7 1.7 0 0 0 1.56 1.03H21v3h-.09A1.7 1.7 0 0 0 19.4 15z"/>',
            'citas' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/>',
            'consultas' => '<path d="M6 3h9l3 3v15H6z"/><path d="M15 3v4h4M9 12h6M9 16h6"/>',
            'materiales' => '<path d="M4 7h16v13H4z"/><path d="M8 7V4h8v3M8 12h8"/>',
            'facturacion' => '<path d="M5 3h14v18H5z"/><path d="M8 8h8M8 12h8M8 16h5"/>',
            'pagos' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18M7 15h3"/>',
            'nomina' => '<path d="M4 3h16v18H4z"/><path d="M8 8h8M8 12h2M14 12h2M8 16h8"/>',
            'inventario' => '<path d="m3 7 9-4 9 4-9 4-9-4Z"/><path d="m3 12 9 4 9-4M3 17l9 4 9-4"/>',
            'compras' => '<path d="M6 6h15l-2 9H8L6 3H3"/><circle cx="9" cy="20" r="1"/><circle cx="18" cy="20" r="1"/>',
            'reportes' => '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>',
            'servicios' => '<path d="M12 3v18M3 12h18"/><circle cx="12" cy="12" r="9"/>',
            'perfil' => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
            'add' => '<path d="M12 5v14M5 12h14"/>', 'bell' => '<path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/>',
            'logout' => '<path d="M10 17l5-5-5-5M15 12H3M21 3v18"/>', 'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2.1 2.1-.06-.06a1.7 1.7 0 0 0-1.88-.34"/>',
            'menu' => '<path d="M4 6h16M4 12h16M4 18h16"/>', 'check' => '<path d="m5 12 4 4L19 6"/>'
        ];
        $path = $icons[$name] ?? $icons['dashboard'];
        return '<svg class="ui-icon ' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">' . $path . '</svg>';
    }
}
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
                <button class="sidebar-toggle" id="sidebarToggle" aria-label="Abrir menú"><?php echo uiIcon('menu'); ?></button>
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
                        'materiales' => ['label' => 'Materiales', 'url' => BASE_URL . '/pages/materiales.php', 'icon' => 'M'],
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
                        'materiales' => ['label' => 'Materiales', 'url' => BASE_URL . '/pages/materiales.php', 'icon' => 'M'],
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
                        <span class="nav-icon"><?php echo uiIcon($key); ?></span>
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
                <a href="<?php echo BASE_URL; ?>/auth/logout.php" class="btn-logout"><?php echo uiIcon('logout'); ?><span>Salir</span></a>
            </div>
        </aside>

        <!-- Topbar -->
        <div class="topbar">
            <div class="topbar-left">
                <button class="btn-menu" id="menuToggle" aria-label="Abrir menú"><?php echo uiIcon('menu'); ?></button>
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
                    <button class="btn-notification" aria-label="Notificaciones"><?php echo uiIcon('bell'); ?></button>
                    <span class="notification-count">3</span>
                </div>
                <div class="user-menu">
                    <button class="btn-user-menu"><?php echo displayText($_SESSION['user_name']); ?> ▼</button>
                    <div class="user-dropdown" style="display: none;">
                        <a href="<?php echo BASE_URL; ?>/pages/perfil.php"><?php echo uiIcon('perfil'); ?> Mi Perfil</a>
                        <a href="<?php echo BASE_URL; ?>/pages/configuracion.php"><?php echo uiIcon('settings'); ?> Configuración</a>
                        <hr>
                        <a href="<?php echo BASE_URL; ?>/auth/logout.php"><?php echo uiIcon('logout'); ?> Salir</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenido Principal -->
        <main class="main-content">
