# Genera la memoria técnica en Word y sus diagramas PNG sin requerir software externo.
Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$root = if ($PSScriptRoot) { Split-Path -Parent $PSScriptRoot } else { (Get-Location).Path }
$diagramDir = Join-Path $root 'docs\diagramas'
New-Item -ItemType Directory -Force -Path $diagramDir | Out-Null
Add-Type -AssemblyName System.Drawing
Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem

function New-Canvas([int]$width, [int]$height) {
    $bitmap = [System.Drawing.Bitmap]::new($width, $height)
    $graphics = [System.Drawing.Graphics]::FromImage($bitmap)
    $graphics.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::AntiAlias
    $graphics.TextRenderingHint = [System.Drawing.Text.TextRenderingHint]::ClearTypeGridFit
    $graphics.Clear([System.Drawing.Color]::FromArgb(250, 252, 251))
    return @($bitmap, $graphics)
}
function Draw-TextBlock($g, [string]$text, [System.Drawing.RectangleF]$area, $font, $brush, [System.Drawing.StringAlignment]$alignment = [System.Drawing.StringAlignment]::Near) {
    $format = [System.Drawing.StringFormat]::new()
    $format.Alignment = $alignment
    $format.LineAlignment = [System.Drawing.StringAlignment]::Center
    $format.Trimming = [System.Drawing.StringTrimming]::EllipsisWord
    $format.FormatFlags = [System.Drawing.StringFormatFlags]::LineLimit
    $g.DrawString($text, $font, $brush, $area, $format)
    $format.Dispose()
}
function Draw-Entity($g, [int]$x, [int]$y, [int]$w, [string]$title, [string[]]$fields, [System.Drawing.Color]$color) {
    $h = 48 + ($fields.Count * 27) + 10
    $border = [System.Drawing.Pen]::new($color, 2)
    $g.FillRectangle([System.Drawing.SolidBrush]::new([System.Drawing.Color]::White), $x, $y, $w, $h)
    $g.FillRectangle([System.Drawing.SolidBrush]::new($color), $x, $y, $w, 44)
    $g.DrawRectangle($border, $x, $y, $w, $h)
    Draw-TextBlock $g $title ([System.Drawing.RectangleF]::new($x + 8, $y + 3, $w - 16, 37)) ([System.Drawing.Font]::new('Arial', 14, [System.Drawing.FontStyle]::Bold)) ([System.Drawing.Brushes]::White)
    for ($i = 0; $i -lt $fields.Count; $i++) {
        Draw-TextBlock $g $fields[$i] ([System.Drawing.RectangleF]::new($x + 10, $y + 48 + ($i * 27), $w - 20, 24)) ([System.Drawing.Font]::new('Arial', 10)) ([System.Drawing.SolidBrush]::new([System.Drawing.Color]::FromArgb(45, 55, 72)))
    }
    $border.Dispose()
    return $h
}
function Draw-Arrow($g, [int]$x1, [int]$y1, [int]$x2, [int]$y2, [string]$label = '') {
    $pen = [System.Drawing.Pen]::new([System.Drawing.Color]::FromArgb(91, 116, 106), 2)
    $pen.CustomEndCap = [System.Drawing.Drawing2D.AdjustableArrowCap]::new(5, 7, $true)
    $g.DrawLine($pen, $x1, $y1, $x2, $y2)
    if ($label) { Draw-TextBlock $g $label ([System.Drawing.RectangleF]::new(($x1+$x2)/2-22, ($y1+$y2)/2-13, 44, 22)) ([System.Drawing.Font]::new('Arial', 9, [System.Drawing.FontStyle]::Bold)) ([System.Drawing.SolidBrush]::new([System.Drawing.Color]::FromArgb(49, 85, 65))) ([System.Drawing.StringAlignment]::Center) }
    $pen.Dispose()
}
function Save-EntityDiagram([string]$path) {
    $canvas = New-Canvas 2400 1740; $bmp = $canvas[0]; $g = $canvas[1]
    $green = [System.Drawing.Color]::FromArgb(29, 103, 57); $blue = [System.Drawing.Color]::FromArgb(38, 105, 151); $orange = [System.Drawing.Color]::FromArgb(185, 105, 35); $purple = [System.Drawing.Color]::FromArgb(111, 77, 145)
    Draw-TextBlock $g 'Modelo entidad–relación — Clínica Dental Mi Sonrisa Feliz' ([System.Drawing.RectangleF]::new(55, 25, 2290, 50)) ([System.Drawing.Font]::new('Arial', 25, [System.Drawing.FontStyle]::Bold)) ([System.Drawing.SolidBrush]::new($green))
    Draw-TextBlock $g 'Estructura lógica del esquema clinica_sonrisa_feliz_v2 (campos principales y relaciones)' ([System.Drawing.RectangleF]::new(55, 75, 2290, 32)) ([System.Drawing.Font]::new('Arial', 13)) ([System.Drawing.SolidBrush]::new([System.Drawing.Color]::FromArgb(80,90,100)))
    Draw-Entity $g 70 160 280 'roles' @('PK id_rol','nombre_rol','estado') $green | Out-Null
    Draw-Entity $g 420 145 310 'usuarios' @('PK id_usuario','FK id_rol','nombre, apellido','cedula, correo','usuario_login, estado') $green | Out-Null
    Draw-Entity $g 820 145 300 'especialidades' @('PK id_especialidad','nombre_especialidad','estado') $blue | Out-Null
    Draw-Entity $g 1200 135 320 'doctores' @('PK id_doctor','FK id_usuario','FK id_especialidad','colegiado, horario','estado') $blue | Out-Null
    Draw-Entity $g 1590 145 310 'pacientes' @('PK id_paciente','FK id_usuario','datos personales','contacto y salud','estado') $blue | Out-Null
    Draw-Entity $g 1980 160 310 'servicios' @('PK id_servicio','nombre, costo','duración, estado') $blue | Out-Null
    Draw-Entity $g 760 530 360 'citas' @('PK id_cita','FK id_paciente','FK id_doctor','FK id_servicio','fecha, hora, estado') $green | Out-Null
    Draw-Entity $g 1220 530 340 'consultas' @('PK id_consulta','FK id_cita (único)','FK paciente, doctor','diagnóstico, tratamiento','estado') $green | Out-Null
    Draw-Entity $g 1660 560 330 'historial_paciente' @('PK id_historial','FK paciente, doctor','evento, datos clínicos','próxima revisión') $green | Out-Null
    Draw-Entity $g 90 950 350 'facturacion' @('PK id_factura','FK id_paciente','FK id_consulta','número, total, estado') $orange | Out-Null
    Draw-Entity $g 530 980 350 'historial_facturacion' @('PK id_historial_factura','FK factura, paciente','monto, método, estado') $orange | Out-Null
    Draw-Entity $g 970 980 320 'empleados_nomina' @('PK id_empleado_nomina','FK id_usuario','salario_base, estado') $orange | Out-Null
    Draw-Entity $g 1380 980 340 'pagos_nomina' @('PK id_pago_nomina','FK empleado_nomina','período, monto, estado') $orange | Out-Null
    Draw-Entity $g 1810 950 300 'proveedores' @('PK id_proveedor','nombre, NIT','contacto, estado') $purple | Out-Null
    Draw-Entity $g 180 1330 330 'inventario' @('PK id_inventario','FK id_proveedor','existencias, mínimo','precio, gastable') $purple | Out-Null
    Draw-Entity $g 640 1350 350 'movimientos_inventario' @('PK id_movimiento','FK inventario, usuario','tipo, cantidad, motivo') $purple | Out-Null
    Draw-Entity $g 1100 1350 350 'compras_inventario' @('PK id_compra','FK inventario, usuario','cantidad, total, proveedor') $purple | Out-Null
    Draw-Entity $g 1570 1360 330 'auditorias' @('PK id_auditoria','FK id_usuario','tabla, operación, fecha') $purple | Out-Null
    Draw-Arrow $g 350 215 420 215 '1:N'; Draw-Arrow $g 730 225 1200 225 '1:1'; Draw-Arrow $g 1120 225 1200 225 '1:N'; Draw-Arrow $g 730 270 1590 270 '1:1'
    Draw-Arrow $g 1750 355 980 530 '1:N'; Draw-Arrow $g 1360 365 980 530 '1:N'; Draw-Arrow $g 2130 305 1040 530 '1:N'; Draw-Arrow $g 1120 670 1220 670 '1:1'; Draw-Arrow $g 1740 360 1830 560 '1:N'
    Draw-Arrow $g 1590 390 265 950 '1:N'; Draw-Arrow $g 1390 770 265 950 '0:1'; Draw-Arrow $g 440 1080 530 1080 '1:N'; Draw-Arrow $g 730 350 1120 980 '1:1'; Draw-Arrow $g 1290 1080 1380 1080 '1:N'
    Draw-Arrow $g 1960 1110 345 1330 '1:N'; Draw-Arrow $g 510 1460 640 1460 '1:N'; Draw-Arrow $g 510 1500 1100 1500 '1:N'; Draw-Arrow $g 730 330 1730 1360 '1:N'
    Draw-TextBlock $g 'Leyenda: PK = clave primaria · FK = clave foránea · Las flechas indican dependencias principales.' ([System.Drawing.RectangleF]::new(60, 1670, 2280, 35)) ([System.Drawing.Font]::new('Arial', 12, [System.Drawing.FontStyle]::Italic)) ([System.Drawing.SolidBrush]::new([System.Drawing.Color]::FromArgb(80,90,100)))
    $bmp.Save($path, [System.Drawing.Imaging.ImageFormat]::Png); $g.Dispose(); $bmp.Dispose()
}
function Save-ProcessDiagram([string]$path) {
    $canvas = New-Canvas 2200 1160; $bmp = $canvas[0]; $g = $canvas[1]
    $green = [System.Drawing.Color]::FromArgb(29,103,57); $blue = [System.Drawing.Color]::FromArgb(38,105,151); $orange = [System.Drawing.Color]::FromArgb(185,105,35)
    Draw-TextBlock $g 'Flujo operativo y responsabilidades del sistema' ([System.Drawing.RectangleF]::new(55, 28, 2090, 48)) ([System.Drawing.Font]::new('Arial', 25, [System.Drawing.FontStyle]::Bold)) ([System.Drawing.SolidBrush]::new($green))
    $steps = @(@('Paciente','Consulta médicos, disponibilidad y reserva una cita',$blue),@('Recepción','Registra pacientes y confirma la cita programada',$green),@('Doctor','Atiende, completa la cita y registra la consulta',$green),@('Contador','Gestiona factura, cobro, nómina y reportes',$orange),@('Inventario','Registra uso, compras y alertas de reposición',$orange))
    $x = 65
    for ($i=0; $i -lt $steps.Count; $i++) { $item=$steps[$i]; Draw-Entity $g $x 230 365 $item[0] @($item[1]) $item[2] | Out-Null; if($i -lt $steps.Count-1){Draw-Arrow $g ($x+365) 315 ($x+410) 315 '';}; $x += 430 }
    Draw-TextBlock $g 'Controles transversales' ([System.Drawing.RectangleF]::new(70, 610, 450, 36)) ([System.Drawing.Font]::new('Arial', 17, [System.Drawing.FontStyle]::Bold)) ([System.Drawing.SolidBrush]::new($green))
    $controls = @('Autenticación y permisos por rol','Protección CSRF en operaciones de estado','Codificación utf8mb4 para datos con acentos','Auditoría y restricciones de integridad','Interfaz adaptable y español / inglés')
    $y=675; foreach($control in $controls){$g.FillEllipse([System.Drawing.SolidBrush]::new($green),88,$y+7,13,13);Draw-TextBlock $g $control ([System.Drawing.RectangleF]::new(120,$y,800,28)) ([System.Drawing.Font]::new('Arial',14)) ([System.Drawing.SolidBrush]::new([System.Drawing.Color]::FromArgb(45,55,72)));$y+=67}
    Draw-TextBlock $g 'Resultados del proceso' ([System.Drawing.RectangleF]::new(1220, 610, 450, 36)) ([System.Drawing.Font]::new('Arial', 17, [System.Drawing.FontStyle]::Bold)) ([System.Drawing.SolidBrush]::new($green))
    $outcomes = @('Agenda ordenada y trazable','Historia clínica asociada a la cita','Factura y comprobantes imprimibles','Control de pagos y nómina','Inventario actualizado para reposición')
    $y=675; foreach($outcome in $outcomes){$g.FillEllipse([System.Drawing.SolidBrush]::new($orange),1238,$y+7,13,13);Draw-TextBlock $g $outcome ([System.Drawing.RectangleF]::new(1270,$y,800,28)) ([System.Drawing.Font]::new('Arial',14)) ([System.Drawing.SolidBrush]::new([System.Drawing.Color]::FromArgb(45,55,72)));$y+=67}
    $bmp.Save($path, [System.Drawing.Imaging.ImageFormat]::Png); $g.Dispose(); $bmp.Dispose()
}

$erDiagram = Join-Path $diagramDir 'modelo_entidad_relacion.png'
$processDiagram = Join-Path $diagramDir 'flujo_operativo.png'
Save-EntityDiagram $erDiagram; Save-ProcessDiagram $processDiagram

function Escape-Xml([string]$text) { [System.Security.SecurityElement]::Escape($text) }
$body = [System.Text.StringBuilder]::new(); $script:drawingId = 1
function Add-Paragraph([string]$text, [string]$style = 'Normal', [bool]$bold = $false) {
    $escaped = Escape-Xml $text; $boldXml = if($bold){'<w:b/>'}else{''}
    [void]$body.Append(('<w:p><w:pPr><w:pStyle w:val="{0}"/></w:pPr><w:r><w:rPr>{1}</w:rPr><w:t xml:space="preserve">{2}</w:t></w:r></w:p>' -f $style, $boldXml, $escaped))
}
function Add-Bullet([string]$text) { [void]$body.Append(('<w:p><w:pPr><w:pStyle w:val="Bullet"/></w:pPr><w:r><w:t>{0}</w:t></w:r></w:p>' -f (Escape-Xml $text))) }
function Add-Table([string[]]$headers, [object[][]]$rows) {
    [void]$body.Append('<w:tbl><w:tblPr><w:tblW w:w="0" w:type="auto"/><w:tblBorders><w:top w:val="single" w:sz="8" w:color="1D6739"/><w:left w:val="single" w:sz="8" w:color="1D6739"/><w:bottom w:val="single" w:sz="8" w:color="1D6739"/><w:right w:val="single" w:sz="8" w:color="1D6739"/><w:insideH w:val="single" w:sz="4" w:color="B7CFC0"/><w:insideV w:val="single" w:sz="4" w:color="B7CFC0"/></w:tblBorders></w:tblPr>')
    $allRows = @(@($headers)) + $rows
    for($i=0;$i -lt $allRows.Count;$i++){[void]$body.Append('<w:tr>');foreach($cell in $allRows[$i]){$shading=if($i -eq 0){'<w:shd w:fill="1D6739"/>'}else{''};$color=if($i -eq 0){'<w:color w:val="FFFFFF"/><w:b/>'}else{''};[void]$body.Append(('<w:tc><w:tcPr>{0}</w:tcPr><w:p><w:r><w:rPr>{1}</w:rPr><w:t xml:space="preserve">{2}</w:t></w:r></w:p></w:tc>' -f $shading, $color, (Escape-Xml ([string]$cell)) ))};[void]$body.Append('</w:tr>')}
    [void]$body.Append('</w:tbl><w:p/>')
}
function Add-Image([string]$relation, [int]$cx, [int]$cy, [string]$name) {
    $script:drawingId++; [void]$body.Append(('<w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r><w:drawing><wp:inline distT="0" distB="0" distL="0" distR="0" xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing"><wp:extent cx="{1}" cy="{2}"/><wp:docPr id="{3}" name="{4}"/><a:graphic xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"><a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture"><pic:pic xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture"><pic:nvPicPr><pic:cNvPr id="0" name="{4}"/><pic:cNvPicPr/></pic:nvPicPr><pic:blipFill><a:blip r:embed="{0}" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"/><a:stretch><a:fillRect/></a:stretch></pic:blipFill><pic:spPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="{1}" cy="{2}"/></a:xfrm><a:prstGeom prst="rect"><a:avLst/></a:prstGeom></pic:spPr></pic:pic></a:graphicData></a:graphic></wp:inline></w:drawing></w:r></w:p>' -f $relation, $cx, $cy, $drawingId, (Escape-Xml $name)))
}
function Add-PageBreak { [void]$body.Append('<w:p><w:r><w:br w:type="page"/></w:r></w:p>') }

Add-Image 'rId1' 2100000 2100000 'Logo de la clínica'
Add-Paragraph 'CLÍNICA DENTAL MI SONRISA FELIZ' 'Title'
Add-Paragraph 'Memoria técnica de construcción e implementación' 'Subtitle'
Add-Paragraph 'Sistema web de gestión clínica, agenda, facturación, nómina e inventario' 'Subtitle'
Add-Paragraph 'Documento académico para presentación universitaria' 'Subtitle'
Add-Paragraph 'Versión 1.0 · Septiembre de 2026' 'Subtitle'
Add-PageBreak
Add-Paragraph 'Resumen ejecutivo' 'Heading1'
Add-Paragraph 'Este documento describe el proceso de diseño, construcción e implementación de Mi Sonrisa Feliz, un sistema web orientado a apoyar la operación de una clínica dental. La solución centraliza la gestión de usuarios, pacientes, profesionales, citas, consultas, facturación, pagos, nómina, compras, inventario y reportes.'
Add-Paragraph 'El desarrollo se planteó como una aplicación web de arquitectura cliente–servidor. Se aplicaron controles de acceso por rol, relaciones de integridad en la base de datos y una interfaz adaptable a equipos de escritorio y dispositivos móviles. La documentación presenta el modelo de datos vigente, las decisiones técnicas y los principales resultados obtenidos.'
Add-Paragraph 'Tabla de contenido' 'Heading1'
foreach($item in @('1. Contexto y alcance','2. Diseño de la solución','3. Modelos y diagramas utilizados','4. Diseño de la base de datos','5. Diseño de las interfaces de usuario','6. Implementación de funcionalidades','7. Proceso de desarrollo','8. Tecnologías y herramientas','9. Dificultades técnicas y soluciones','10. Conclusiones y recomendaciones')) { Add-Bullet $item }
Add-PageBreak
Add-Paragraph '1. Contexto y alcance' 'Heading1'
Add-Paragraph 'Mi Sonrisa Feliz responde a la necesidad de organizar los procesos clínicos y administrativos de una clínica odontológica en una única plataforma. El alcance incluye el registro y consulta de información de pacientes, la reserva y seguimiento de citas, el registro de consultas clínicas, la emisión de comprobantes, el control de pagos, la gestión de nómina y la operación básica de inventario.'
Add-Paragraph 'Los actores principales son Administrador, Doctor, Recepcionista, Paciente y Contador. Cada actor accede únicamente a las funcionalidades necesarias para sus actividades. Esta separación reduce errores operativos y mantiene la trazabilidad de la información.'
Add-Table @('Rol','Responsabilidades principales') @(@('Administrador','Administra catálogos, usuarios y configuración general.'),@('Recepcionista','Registra pacientes, busca por nombre o cédula, agenda y confirma citas.'),@('Doctor','Visualiza agenda, completa citas y registra consultas.'),@('Paciente','Consulta médicos, horarios disponibles y reserva o cambia sus citas.'),@('Contador','Gestiona facturas, cobros, comprobantes, nómina y reportes financieros.'))
Add-Paragraph '2. Diseño de la solución' 'Heading1'
Add-Paragraph 'La solución se diseñó con una arquitectura web de tres capas: presentación, lógica de negocio y persistencia. La capa de presentación reúne las vistas PHP, las hojas de estilo y los scripts de interacción. La lógica de negocio aplica sesiones, permisos, validaciones, reglas de citas y transacciones operativas. La capa de datos utiliza MySQL con claves primarias, claves foráneas, restricciones de unicidad y codificación utf8mb4.'
Add-Image 'rId3' 6000000 3150000 'Flujo operativo'
Add-Paragraph 'Figura 1. Flujo general de operación y controles transversales.' 'Caption'
Add-Paragraph '3. Modelos y diagramas utilizados' 'Heading1'
Add-Paragraph 'Se utilizaron un modelo entidad–relación para representar la persistencia de datos y un diagrama de flujo operativo para delimitar responsabilidades entre los usuarios. El modelo entidad–relación refleja las relaciones que sostienen la trazabilidad clínica, financiera y de inventario; el flujo operativo describe la secuencia desde la reserva hasta el control administrativo.'
Add-Paragraph '4. Diseño de la base de datos' 'Heading1'
Add-Paragraph 'La base de datos se denomina clinica_sonrisa_feliz_v2 y emplea el motor InnoDB. Está configurada con CHARACTER SET utf8mb4 y COLLATE utf8mb4_unicode_ci, lo que permite almacenar y recuperar correctamente nombres, direcciones y textos con tildes, eñes y otros caracteres Unicode.'
Add-Image 'rId2' 6500000 4710000 'Modelo entidad relación'
Add-Paragraph 'Figura 2. Modelo entidad–relación de la estructura implementada.' 'Caption'
Add-Table @('Área','Tablas principales','Propósito') @(@('Seguridad','roles, usuarios, auditorias','Control de acceso y trazabilidad.'),@('Atención clínica','pacientes, doctores, especialidades, servicios, citas, consultas, historial_paciente','Registro asistencial y agenda.'),@('Finanzas','facturacion, historial_facturacion, empleados_nomina, pagos_nomina','Cobros, comprobantes y remuneraciones.'),@('Abastecimiento','proveedores, inventario, movimientos_inventario, compras_inventario','Existencias, uso y reposición de materiales.'))
Add-Paragraph 'La restricción única sobre doctor, fecha y hora evita que un profesional sea asignado a dos citas simultáneas. Las relaciones de citas, consultas, facturas y pagos permiten seguir el ciclo de atención sin duplicar la información esencial.'
Add-Paragraph '5. Diseño de las interfaces de usuario' 'Heading1'
Add-Paragraph 'Las interfaces siguen una estructura consistente: barra lateral por módulos, encabezado con usuario e idioma, tarjetas de resumen y tablas responsivas. Los formularios agrupan campos relacionados y señalan los datos obligatorios. En los procesos frecuentes se incorporaron filtros de búsqueda: Recepción puede localizar pacientes por nombre o cédula tanto en el listado de citas como al crear una nueva cita.'
Add-Bullet 'Panel del Doctor: citas del día, calendario de carga diaria y consultas pendientes.'
Add-Bullet 'Panel de Recepción: registro de pacientes, agenda, confirmación y búsqueda rápida.'
Add-Bullet 'Panel del Contador: facturas, pagos, comprobantes, nómina y reportes filtrables.'
Add-Bullet 'Portal del Paciente: disponibilidad de médicos, reserva y cambio de citas.'
Add-Paragraph 'La interfaz incorpora traducción de textos de la aplicación entre español e inglés. Los valores controlados provenientes de la base de datos, como estados, roles, especialidades y métodos de pago, se traducen en la vista sin alterar el dato original almacenado.'
Add-Paragraph '6. Implementación de funcionalidades' 'Heading1'
Add-Table @('Funcionalidad','Implementación y resultado') @(@('Autenticación y roles','Sesiones PHP y validación de permisos por módulo.'),@('Agenda clínica','Reserva de citas, disponibilidad por médico, control de colisiones y estados programada, confirmada y completada.'),@('Consultas','Registro de diagnóstico, tratamiento, medicamentos y observaciones asociado a una cita.'),@('Facturación y pagos','Facturas imprimibles en dólares, actualización de estados y registro de pagos.'),@('Nómina','Salario base por empleado, pago por período y comprobante imprimible.'),@('Compras e inventario','Registro de compras, actualización de existencias, consumo clínico y alertas de reposición.'),@('Reportes','Consultas de facturas pagadas, compras y nómina con filtros por fecha, empleado, cédula, cargo, factura o proveedor.'))
Add-Paragraph '7. Proceso de desarrollo de la solución de software' 'Heading1'
Add-Paragraph 'El proceso se realizó de manera incremental. Primero se analizaron los actores, los datos requeridos y los flujos de trabajo. Luego se definió el esquema relacional y se construyeron los módulos base de autenticación, usuarios, pacientes, doctores y agenda. En iteraciones posteriores se incorporaron consultas, facturación, pagos, nómina, compras, inventario, reportes, internacionalización y mejoras de experiencia de usuario.'
Add-Paragraph 'Cada cambio se verificó mediante revisión de sintaxis PHP, pruebas funcionales desde los roles involucrados y comprobación de consultas a la base de datos. El repositorio Git permite mantener versiones de los avances y recuperar el historial de cambios de forma controlada.'
Add-Paragraph '8. Tecnologías y herramientas utilizadas' 'Heading1'
Add-Table @('Tecnología o herramienta','Función dentro del proyecto') @(@('PHP','Construcción de vistas, sesiones, validaciones y acceso a datos.'),@('MySQL / InnoDB','Persistencia relacional, integridad referencial, vistas y consultas.'),@('HTML5 y CSS3','Estructura y diseño visual adaptable.'),@('JavaScript','Interacción de formularios, calendario, filtros y traducción de interfaz.'),@('XAMPP / Apache','Entorno local de ejecución para PHP y MySQL.'),@('Git y GitHub','Control de versiones, respaldo y publicación del código fuente.'),@('Microsoft Word (.docx)','Formato de entrega de esta memoria técnica.'))
Add-Paragraph '9. Dificultades técnicas y soluciones aplicadas' 'Heading1'
Add-Table @('Dificultad','Solución aplicada') @(@('Caracteres especiales mostrados incorrectamente','Se configuró MySQL y la conexión mysqli con utf8mb4; la documentación se genera con XML UTF-8.'),@('Riesgo de doble reserva para un doctor','Se estableció una restricción única por doctor, fecha y hora y se validó la disponibilidad.'),@('Acciones no disponibles para Recepción','Se habilitaron formularios específicos de usuario, cita y factura de acuerdo con sus permisos.'),@('Estados de atención ambiguos','Se asignó a Recepción la confirmación y al Doctor la finalización de la cita.'),@('Seguimiento de materiales insuficiente','Se añadieron compras, movimientos, consumos clínicos y alerta de existencias mínimas.'),@('Traducción de datos controlados','Se implementó una capa de traducción visual que conserva la información original en la base de datos.'))
Add-Paragraph '10. Conclusiones y recomendaciones' 'Heading1'
Add-Paragraph 'La solución integra los procesos esenciales de una clínica dental en una plataforma coherente y trazable. El modelo de datos separa la información clínica, administrativa y de inventario; los roles distribuyen responsabilidades; y los comprobantes y reportes favorecen el control operativo.'
Add-Paragraph 'Como mejoras futuras se recomienda incorporar notificaciones por correo o mensajería, copias de seguridad automatizadas, pruebas automatizadas, auditoría ampliada y un módulo de indicadores con exportación de reportes. Antes de un despliegue productivo se deben configurar credenciales seguras, HTTPS y una política formal de respaldo y protección de datos personales.'
Add-Paragraph 'Anexo A. Criterios de calidad verificados' 'Heading1'
Add-Bullet 'Codificación UTF-8 / utf8mb4 para textos, nombres y direcciones con caracteres especiales.'
Add-Bullet 'Validación de sintaxis PHP en las vistas modificadas.'
Add-Bullet 'Uso de consultas preparadas en operaciones con parámetros.'
Add-Bullet 'Separación de permisos por rol y tokens CSRF para operaciones de cambio de estado.'
Add-Bullet 'Diagramas generados a partir de la estructura vigente del esquema y migraciones del proyecto.'

$documentXml = ('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><w:body>{0}<w:sectPr><w:pgSz w:w="11906" w:h="16838"/><w:pgMar w:top="1100" w:right="1100" w:bottom="1100" w:left="1100" w:header="700" w:footer="700" w:gutter="0"/><w:footerReference w:type="default" r:id="rId4"/></w:sectPr></w:body></w:document>' -f $body.ToString())
$styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:style w:type="paragraph" w:default="1" w:styleId="Normal"><w:name w:val="Normal"/><w:rPr><w:rFonts w:ascii="Aptos" w:hAnsi="Aptos"/><w:sz w:val="22"/></w:rPr><w:pPr><w:spacing w:after="140" w:line="276" w:lineRule="auto"/></w:pPr></w:style><w:style w:type="paragraph" w:styleId="Title"><w:name w:val="Title"/><w:rPr><w:b/><w:color w:val="1D6739"/><w:sz w:val="42"/></w:rPr><w:pPr><w:jc w:val="center"/><w:spacing w:before="240" w:after="180"/></w:pPr></w:style><w:style w:type="paragraph" w:styleId="Subtitle"><w:name w:val="Subtitle"/><w:rPr><w:color w:val="46515A"/><w:sz w:val="25"/></w:rPr><w:pPr><w:jc w:val="center"/><w:spacing w:after="110"/></w:pPr></w:style><w:style w:type="paragraph" w:styleId="Heading1"><w:name w:val="heading 1"/><w:rPr><w:b/><w:color w:val="1D6739"/><w:sz w:val="31"/></w:rPr><w:pPr><w:spacing w:before="280" w:after="150"/><w:outlineLvl w:val="0"/></w:pPr></w:style><w:style w:type="paragraph" w:styleId="Caption"><w:name w:val="Caption"/><w:rPr><w:i/><w:color w:val="53606A"/><w:sz w:val="19"/></w:rPr><w:pPr><w:jc w:val="center"/><w:spacing w:after="180"/></w:pPr></w:style><w:style w:type="paragraph" w:styleId="Bullet"><w:name w:val="Bullet"/><w:pPr><w:ind w:left="420" w:hanging="220"/><w:spacing w:after="80"/></w:pPr><w:rPr><w:rFonts w:ascii="Aptos" w:hAnsi="Aptos"/></w:rPr></w:style></w:styles>'
$footer = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><w:ftr xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r><w:rPr><w:color w:val="53606A"/><w:sz w:val="18"/></w:rPr><w:t>Clínica Dental Mi Sonrisa Feliz · Memoria técnica de construcción e implementación · 2026</w:t></w:r></w:p></w:ftr>'
$contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Default Extension="png" ContentType="image/png"/><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/><Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/><Override PartName="/word/footer1.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.footer+xml"/></Types>'
$rootRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/></Relationships>'
$docRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/logo.png"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/modelo_entidad_relacion.png"/><Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/flujo_operativo.png"/><Relationship Id="rId4" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/footer" Target="footer1.xml"/><Relationship Id="rId5" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>'

$output = Join-Path $root 'DOCUMENTACION_CONSTRUCCION_IMPLEMENTACION.docx'
$zipPath = Join-Path $env:TEMP 'documentacion_clinica.docx.zip'
Remove-Item -LiteralPath $zipPath -Force -ErrorAction SilentlyContinue
$fileStream = [System.IO.File]::Open($zipPath, [System.IO.FileMode]::Create)
$archive = [System.IO.Compression.ZipArchive]::new($fileStream, [System.IO.Compression.ZipArchiveMode]::Create)
function Add-ZipText($archive, [string]$name, [string]$text) { $entry=$archive.CreateEntry($name); $writer=[System.IO.StreamWriter]::new($entry.Open(), [System.Text.UTF8Encoding]::new($false)); $writer.Write($text); $writer.Dispose() }
function Add-ZipFile($archive, [string]$name, [string]$source) { $entry=$archive.CreateEntry($name); $input=[System.IO.File]::OpenRead($source); $outputStream=$entry.Open(); $input.CopyTo($outputStream); $outputStream.Dispose(); $input.Dispose() }
Add-ZipText $archive '[Content_Types].xml' $contentTypes; Add-ZipText $archive '_rels/.rels' $rootRels; Add-ZipText $archive 'word/document.xml' $documentXml; Add-ZipText $archive 'word/styles.xml' $styles; Add-ZipText $archive 'word/footer1.xml' $footer; Add-ZipText $archive 'word/_rels/document.xml.rels' $docRels
Add-ZipFile $archive 'word/media/logo.png' (Join-Path $root 'assets\images\logo-mi-sonrisa-feliz.png'); Add-ZipFile $archive 'word/media/modelo_entidad_relacion.png' $erDiagram; Add-ZipFile $archive 'word/media/flujo_operativo.png' $processDiagram
$archive.Dispose(); $fileStream.Dispose(); Copy-Item -LiteralPath $zipPath -Destination $output -Force; Remove-Item -LiteralPath $zipPath -Force
Write-Output "Documento generado: $output"
