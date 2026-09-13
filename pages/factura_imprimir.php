<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
checkSession();
header('Content-Type: text/html; charset=UTF-8');

if (!checkPermission('facturacion') && $_SESSION['user_role'] != ROLE_ADMIN && $_SESSION['user_role'] != ROLE_CONTADOR && $_SESSION['user_role'] != ROLE_PACIENTE) {
    header('Location: ' . BASE_URL . '/index.php'); exit();
}

$invoiceId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$where = 'f.id_factura = ?'; $params = [$invoiceId];
if ($_SESSION['user_role'] == ROLE_PACIENTE) {
    $where .= ' AND p.id_usuario = ?'; $params[] = (int)$_SESSION['user_id'];
}
$invoice = getRecord("SELECT f.*, p.nombre paciente_nombre, p.apellido paciente_apellido, p.cedula paciente_cedula,
    p.direccion, p.ciudad, p.telefono, COALESCE(SUM(h.monto_pago), 0) total_pagado
    FROM facturacion f JOIN pacientes p ON p.id_paciente=f.id_paciente
    LEFT JOIN historial_facturacion h ON h.id_factura=f.id_factura AND h.estado_pago='confirmado'
    WHERE $where GROUP BY f.id_factura", $params);
if (!$invoice) { http_response_code(404); exit('Factura no encontrada.'); }
$amountDue = max(0, (float)$invoice['total'] - (float)$invoice['total_pagado']);
// Escapa valores de la base antes de imprimirlos en HTML.
function invoiceValue($value) { return displayText($value ?? '—'); }
?>
<!doctype html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Factura <?=invoiceValue($invoice['numero_factura'])?></title>
<style>
body{font-family:Arial,sans-serif;color:#1f2937;background:#f3f4f6;margin:0}.invoice{max-width:800px;margin:35px auto;background:#fff;padding:44px;box-shadow:0 2px 18px #0002}.head{display:flex;justify-content:space-between;border-bottom:3px solid #2c5f2d;padding-bottom:22px}.brand{display:flex;align-items:flex-start;gap:14px}.brand-logo{width:60px;height:60px;object-fit:contain;flex:0 0 auto}.brand h1{color:#2c5f2d;margin:0}.brand p{color:#6b7280;margin:6px 0}.number{text-align:right}.number h2{margin:0;color:#2c5f2d}.details{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin:30px 0}.details h3{font-size:14px;text-transform:uppercase;color:#2c5f2d;margin:0 0 8px}.details p{line-height:1.6;margin:0}.table{width:100%;border-collapse:collapse;margin-top:20px}.table th{background:#e8f5e9;text-align:left;padding:12px}.table td{padding:12px;border-bottom:1px solid #e5e7eb}.totals{margin:24px 0 0 auto;width:310px}.totals div{display:flex;justify-content:space-between;padding:7px 0}.totals .grand{font-size:20px;font-weight:bold;color:#2c5f2d;border-top:2px solid #2c5f2d}.status{display:inline-block;padding:5px 10px;border-radius:20px;background:#e8f5e9;color:#166534;font-weight:bold}.actions{max-width:800px;margin:0 auto 35px;display:flex;gap:12px}.btn{padding:11px 16px;border:0;border-radius:6px;cursor:pointer;background:#2c5f2d;color:#fff;font-size:14px}.btn.secondary{background:#6b7280}@media print{body{background:#fff}.invoice{box-shadow:none;margin:0;max-width:none}.actions{display:none}@page{margin:12mm}}
</style></head><body>
<main class="invoice"><header class="head"><div class="brand"><img class="brand-logo" src="<?= ASSETS_URL ?>/images/logo-mi-sonrisa-feliz.png?v=<?= filemtime(BASE_PATH . 'assets/images/logo-mi-sonrisa-feliz.png') ?>" alt="Logo de Mi Sonrisa Feliz"><div><h1>Clínica Dental Mi Sonrisa Feliz</h1><p>Comprobante de servicios odontológicos</p><p><?=invoiceValue(CLINIC_EMAIL)?> · <?=invoiceValue(CLINIC_PHONE)?></p></div></div><div class="number"><h2>FACTURA</h2><strong><?=invoiceValue($invoice['numero_factura'])?></strong><p>Fecha: <?=date('d/m/Y', strtotime($invoice['fecha_factura']))?></p><span class="status"><?=invoiceValue(ucfirst($invoice['estado_factura']))?></span></div></header>
<section class="details"><div><h3>Facturar a</h3><p><strong><?=invoiceValue($invoice['paciente_nombre'].' '.$invoice['paciente_apellido'])?></strong><br>C.I.: <?=invoiceValue($invoice['paciente_cedula'])?><br><?=invoiceValue($invoice['direccion'])?><br><?=invoiceValue($invoice['ciudad'])?><br><?=invoiceValue($invoice['telefono'])?></p></div><div><h3>Información de pago</h3><p>Método: <?=invoiceValue(ucfirst($invoice['metodo_pago']))?><br>Estado: <?=invoiceValue(ucfirst($invoice['estado_factura']))?><br>Pagado: Bs <?=number_format((float)$invoice['total_pagado'], 2)?><br>Saldo: Bs <?=number_format($amountDue, 2)?></p></div></section>
<table class="table"><thead><tr><th>Descripción</th><th>Subtotal</th><th>Impuesto</th><th>Total</th></tr></thead><tbody><tr><td><?=nl2br(invoiceValue($invoice['descripcion_servicios']))?></td><td>Bs <?=number_format((float)$invoice['subtotal'],2)?></td><td>Bs <?=number_format((float)$invoice['impuesto'],2)?></td><td>Bs <?=number_format((float)$invoice['total'],2)?></td></tr></tbody></table>
<section class="totals"><div><span>Subtotal</span><strong>Bs <?=number_format((float)$invoice['subtotal'],2)?></strong></div><div><span>Impuesto</span><strong>Bs <?=number_format((float)$invoice['impuesto'],2)?></strong></div><div class="grand"><span>Total</span><span>Bs <?=number_format((float)$invoice['total'],2)?></span></div></section><?php if($invoice['notas']):?><p><strong>Notas:</strong> <?=invoiceValue($invoice['notas'])?></p><?php endif;?></main>
<div class="actions"><button class="btn" onclick="window.print()">Imprimir factura</button><button class="btn secondary" onclick="history.back()">Volver</button></div>
<script src="<?= ASSETS_URL ?>/js/i18n.js?v=<?= filemtime(BASE_PATH . 'assets/js/i18n.js') ?>"></script>
</body></html>
