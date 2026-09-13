<?php
require_once __DIR__ . '/../config/database.php'; require_once __DIR__ . '/../config/config.php'; checkSession();
$user = getRecord('SELECT nombre, apellido, email, telefono, usuario_login, cedula, estado FROM usuarios WHERE id_usuario=?', [(int)$_SESSION['user_id']]); include __DIR__ . '/../includes/header.php';
?>
<div class="page-header"><h1>Mi perfil</h1></div><section class="content-section"><dl><?php foreach(['Nombre'=>'nombre','Apellido'=>'apellido','Usuario'=>'usuario_login','Correo'=>'email','Teléfono'=>'telefono','Cédula'=>'cedula','Estado'=>'estado'] as $label=>$key): ?><div><dt><?php echo $label; ?></dt><dd><?php echo htmlspecialchars((string)($user[$key] ?? '—')); ?></dd></div><?php endforeach; ?></dl></section><style>.page-header{margin-bottom:24px;border-bottom:2px solid var(--gray-300);padding-bottom:16px}.content-section{background:#fff;padding:20px;border-radius:8px;box-shadow:var(--box-shadow)}dl{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}dt{font-weight:700;color:var(--gray-700)}dd{margin:3px 0 0}@media(max-width:600px){dl{grid-template-columns:1fr}}</style>
<?php include __DIR__ . '/../includes/footer.php'; ?>
