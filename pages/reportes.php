<?php
require_once __DIR__ . '/../config/database.php'; require_once __DIR__ . '/../config/config.php'; checkSession();
if (!checkPermission('reportes') && $_SESSION['user_role'] != ROLE_ADMIN) { header('Location: ' . BASE_URL . '/index.php'); exit(); }
$income = getRecords('SELECT mes AS Mes, cantidad_facturas AS Facturas, total_ingresos AS Facturado, ingresos_cobrados AS Cobrado FROM vista_ingresos_mensuales LIMIT 12');
$pending = getRecords('SELECT numero_factura AS Factura, paciente AS Paciente, fecha_factura AS Fecha, total AS Total, dias_vencimiento AS "Días pendiente" FROM vista_facturas_pendientes LIMIT 10');
$stock = getRecords("SELECT nombre_producto AS Producto, cantidad AS Existencias, cantidad_minima AS Mínimo FROM inventario WHERE cantidad <= cantidad_minima AND estado='activo' ORDER BY cantidad");
$payrollDate = trim($_GET['fecha'] ?? '');
$payrollEmployee = trim($_GET['empleado'] ?? '');
$payrollId = trim($_GET['cedula'] ?? '');
$payrollRole = trim($_GET['cargo'] ?? '');
$payrollWhere = []; $payrollParams = [];
if ($payrollDate !== '') { $payrollWhere[] = 'p.fecha_pago = ?'; $payrollParams[] = $payrollDate; }
if ($payrollEmployee !== '') { $payrollWhere[] = "CONCAT(u.nombre,' ',u.apellido) LIKE ?"; $payrollParams[] = "%$payrollEmployee%"; }
if ($payrollId !== '') { $payrollWhere[] = 'u.cedula LIKE ?'; $payrollParams[] = "%$payrollId%"; }
if ($payrollRole !== '') { $payrollWhere[] = 'r.nombre_rol = ?'; $payrollParams[] = $payrollRole; }
$payrollSql = "SELECT p.periodo AS Período, CONCAT(u.nombre,' ',u.apellido) AS Empleado, u.cedula AS Cédula, r.nombre_rol AS Cargo, p.monto AS Monto, p.estado_pago AS Estado, p.fecha_pago AS 'Fecha de pago' FROM pagos_nomina p JOIN empleados_nomina e ON e.id_empleado_nomina=p.id_empleado_nomina JOIN usuarios u ON u.id_usuario=e.id_usuario JOIN roles r ON r.id_rol=u.id_rol";
if ($payrollWhere) $payrollSql .= ' WHERE ' . implode(' AND ', $payrollWhere);
$payroll = getRecords($payrollSql . ' ORDER BY p.periodo DESC, u.nombre', $payrollParams);
$rolesPayroll = getRecords("SELECT nombre_rol FROM roles WHERE id_rol IN (1,2,3,5) ORDER BY nombre_rol");
include __DIR__ . '/../includes/header.php';
function reportTable($rows) { if (!$rows) { echo '<p class="empty">Sin datos disponibles.</p>'; return; } echo '<div class="table-responsive"><table class="table"><thead><tr>'; foreach(array_keys($rows[0]) as $h) echo '<th>'.htmlspecialchars($h).'</th>'; echo '</tr></thead><tbody>'; foreach($rows as $row){echo '<tr>';foreach($row as $v)echo '<td>'.htmlspecialchars((string)($v ?? '—')).'</td>';echo '</tr>';} echo '</tbody></table></div>'; }
?>
<div class="page-header"><div><h1>Reportes</h1><p>Resumen financiero y alertas operativas.</p></div></div>
<section class="content-section"><h2>Ingresos mensuales</h2><?php reportTable($income); ?></section><section class="content-section mt-20"><h2>Facturas pendientes</h2><?php reportTable($pending); ?></section><section class="content-section mt-20"><h2>Pagos de nómina</h2><form method="get" class="payroll-filter"><input class="form-control" type="date" name="fecha" value="<?=htmlspecialchars($payrollDate)?>" title="Fecha de pago"><input class="form-control" name="empleado" placeholder="Empleado" value="<?=htmlspecialchars($payrollEmployee)?>"><input class="form-control" name="cedula" placeholder="Cédula" value="<?=htmlspecialchars($payrollId)?>"><select class="form-control" name="cargo"><option value="">Todos los cargos</option><?php foreach($rolesPayroll as $role):?><option value="<?=htmlspecialchars($role['nombre_rol'])?>" <?=$payrollRole===$role['nombre_rol']?'selected':''?>><?=htmlspecialchars($role['nombre_rol'])?></option><?php endforeach;?></select><button class="btn btn-primary">Buscar</button><a class="btn btn-secondary" href="reportes.php">Limpiar</a></form><?php reportTable($payroll); ?></section><section class="content-section mt-20"><h2>Inventario por reponer</h2><?php reportTable($stock); ?></section>
<style>.page-header{margin-bottom:24px;border-bottom:2px solid var(--gray-300);padding-bottom:16px}.content-section{background:#fff;padding:20px;border-radius:8px;box-shadow:var(--box-shadow)}.content-section h2{margin-bottom:16px}.empty{color:var(--gray-500)}.payroll-filter{display:grid;grid-template-columns:repeat(5,minmax(130px,1fr));gap:10px;margin-bottom:18px}.payroll-filter .btn{padding:10px}@media(max-width:760px){.payroll-filter{grid-template-columns:1fr}}</style>
<?php include __DIR__ . '/../includes/footer.php'; ?>
