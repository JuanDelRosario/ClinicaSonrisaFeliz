<?php
/** Vista limitada de usuarios para Recepción. */
if (!checkPermission('usuarios')) { header('Location: ' . BASE_URL . '/index.php'); exit(); }
if (empty($_SESSION['csrf_recepcion'])) $_SESSION['csrf_recepcion'] = bin2hex(random_bytes(32));

$message = ''; $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_recepcion'], $_POST['csrf'] ?? '')) {
        $error = 'La solicitud expiró. Intente nuevamente.';
    } else {
        $fields = ['nombre', 'apellido', 'email', 'telefono', 'usuario_login', 'cedula', 'password'];
        $data = [];
        foreach ($fields as $field) $data[$field] = trim((string) ($_POST[$field] ?? ''));
        if (!$data['nombre'] || !$data['apellido'] || !$data['email'] || !$data['usuario_login'] || !$data['cedula'] || !$data['password']) {
            $error = 'Complete todos los campos obligatorios.';
        } else {
            try {
                insertRecord('usuarios', [
                    'id_rol' => ROLE_PACIENTE, 'nombre' => $data['nombre'], 'apellido' => $data['apellido'],
                    'email' => $data['email'], 'telefono' => $data['telefono'] ?: null,
                    'usuario_login' => $data['usuario_login'], 'cedula' => $data['cedula'],
                    'password_hash' => hashPassword($data['password']), 'estado' => 'activo'
                ]);
                $message = 'Usuario paciente creado correctamente.';
            } catch (Throwable $exception) {
                error_log($exception->getMessage());
                $error = 'No se pudo guardar. El usuario, correo o cédula podría ya existir.';
            }
        }
    }
}

$search = trim($_GET['buscar'] ?? '');
$params = []; $where = "WHERE u.id_rol = " . ROLE_PACIENTE;
if ($search !== '') {
    $where .= " AND (CONCAT(u.nombre, ' ', u.apellido) LIKE ? OR u.cedula LIKE ?)";
    $term = '%' . $search . '%'; $params = [$term, $term];
}
$users = getRecords("SELECT u.id_usuario AS ID, CONCAT(u.nombre, ' ', u.apellido) AS Nombre, u.cedula AS Cédula, u.telefono AS Teléfono, u.email AS Correo FROM usuarios u $where ORDER BY u.nombre, u.apellido", $params);
include __DIR__ . '/../includes/header.php';
?>
<div class="page-header"><div><h1>Usuarios pacientes</h1><p>Registro y búsqueda rápida por nombre o cédula.</p></div><a class="btn btn-primary" href="?new=1">Nuevo usuario</a></div>
<?php if ($message): ?><div class="alert alert-success"><?=htmlspecialchars($message)?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?=htmlspecialchars($error)?></div><?php endif; ?>
<section class="content-section"><form method="get" class="search-form"><input class="form-control" type="search" name="buscar" placeholder="Buscar por nombre o cédula" value="<?=htmlspecialchars($search)?>"><button class="btn btn-primary">Buscar</button><?php if($search): ?><a class="btn btn-secondary" href="?">Limpiar</a><?php endif; ?></form></section>
<?php if(isset($_GET['new'])): ?><section class="content-section mt-20"><h2>Nuevo usuario paciente</h2><form method="post"><input type="hidden" name="csrf" value="<?=htmlspecialchars($_SESSION['csrf_recepcion'])?>"><div class="form-grid"><div class="form-group"><label>Nombre *</label><input class="form-control" name="nombre" required></div><div class="form-group"><label>Apellido *</label><input class="form-control" name="apellido" required></div><div class="form-group"><label>Cédula *</label><input class="form-control" name="cedula" required></div><div class="form-group"><label>Teléfono</label><input class="form-control" name="telefono"></div><div class="form-group"><label>Correo *</label><input class="form-control" type="email" name="email" required></div><div class="form-group"><label>Usuario *</label><input class="form-control" name="usuario_login" required></div><div class="form-group"><label>Contraseña *</label><input class="form-control" type="password" name="password" required></div></div><button class="btn btn-primary">Guardar usuario</button><a class="btn btn-secondary" href="?">Cancelar</a></form></section><?php endif; ?>
<section class="content-section mt-20"><?php if(!$users): ?><div class="alert alert-info">No hay coincidencias.</div><?php else: ?><div class="table-responsive"><table class="table"><thead><tr><th>ID</th><th>Nombre</th><th>Cédula</th><th>Teléfono</th><th>Correo</th></tr></thead><tbody><?php foreach($users as $user): ?><tr><?php foreach($user as $value): ?><td><?=htmlspecialchars((string)($value ?? '—'))?></td><?php endforeach; ?></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section>
<style>.page-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;border-bottom:2px solid var(--gray-300);padding-bottom:16px}.content-section{background:#fff;padding:20px;border-radius:8px;box-shadow:var(--box-shadow)}.search-form{display:flex;gap:10px}.search-form .form-control{flex:1}.form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:0 20px}@media(max-width:650px){.page-header,.search-form{flex-direction:column;align-items:stretch}.form-grid{grid-template-columns:1fr}}</style>
<?php include __DIR__ . '/../includes/footer.php'; ?>
