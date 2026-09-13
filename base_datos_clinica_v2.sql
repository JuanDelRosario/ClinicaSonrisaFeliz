-- Clínica Dental Mi Sonrisa Feliz - esquema V2
-- Importar en una base vacía llamada clinica_sonrisa_feliz_v2.
CREATE DATABASE IF NOT EXISTS clinica_sonrisa_feliz_v2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE clinica_sonrisa_feliz_v2;

CREATE TABLE IF NOT EXISTS roles (
 id_rol INT AUTO_INCREMENT PRIMARY KEY, nombre_rol VARCHAR(50) NOT NULL UNIQUE,
 descripcion VARCHAR(255), estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
 creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS usuarios (
 id_usuario INT AUTO_INCREMENT PRIMARY KEY, id_rol INT NOT NULL,
 nombre VARCHAR(100) NOT NULL, apellido VARCHAR(100) NOT NULL,
 usuario_login VARCHAR(50) NOT NULL UNIQUE, email VARCHAR(120) NOT NULL UNIQUE,
 telefono VARCHAR(25), cedula VARCHAR(25) NOT NULL UNIQUE, password_hash VARCHAR(255) NOT NULL,
 estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo', fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT fk_usuario_rol FOREIGN KEY(id_rol) REFERENCES roles(id_rol)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS especialidades (
 id_especialidad INT AUTO_INCREMENT PRIMARY KEY, nombre_especialidad VARCHAR(100) NOT NULL UNIQUE,
 descripcion TEXT, estado ENUM('activo','inactivo') DEFAULT 'activo', fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pacientes (
 id_paciente INT AUTO_INCREMENT PRIMARY KEY, id_usuario INT UNIQUE,
 nombre VARCHAR(100) NOT NULL DEFAULT '', apellido VARCHAR(100) NOT NULL DEFAULT '', fecha_nacimiento DATE NOT NULL DEFAULT '1900-01-01',
 sexo ENUM('masculino','femenino','otro','prefiere_no_decir') NOT NULL DEFAULT 'prefiere_no_decir',
 genero ENUM('masculino','femenino','otro') NULL,
 cedula VARCHAR(25) UNIQUE, telefono VARCHAR(25), email VARCHAR(120), direccion VARCHAR(255),
 ciudad VARCHAR(100), codigo_postal VARCHAR(15), estado_civil ENUM('soltero','casado','divorciado','viudo') NULL, tipo_sangre VARCHAR(5), alergias TEXT,
 enfermedades_cronicas TEXT, contacto_emergencia VARCHAR(150), telefono_emergencia VARCHAR(25),
 observaciones TEXT, estado ENUM('activo','inactivo') DEFAULT 'activo',
 fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP, fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT fk_paciente_usuario FOREIGN KEY(id_usuario) REFERENCES usuarios(id_usuario) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS doctores (
 id_doctor INT AUTO_INCREMENT PRIMARY KEY, id_usuario INT UNIQUE, id_especialidad INT NOT NULL,
 nombre VARCHAR(100) NOT NULL DEFAULT '', apellido VARCHAR(100) NOT NULL DEFAULT '', cedula VARCHAR(25) UNIQUE,
 telefono VARCHAR(25), email VARCHAR(120), direccion VARCHAR(255), numero_colegiado VARCHAR(60) NOT NULL UNIQUE,
 experiencia_anios SMALLINT UNSIGNED DEFAULT 0, horario_entrada TIME, horario_salida TIME,
 estado ENUM('activo','inactivo','licencia') DEFAULT 'activo', fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_doctor_usuario FOREIGN KEY(id_usuario) REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
 CONSTRAINT fk_doctor_especialidad FOREIGN KEY(id_especialidad) REFERENCES especialidades(id_especialidad)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS servicios (
 id_servicio INT AUTO_INCREMENT PRIMARY KEY, nombre_servicio VARCHAR(120) NOT NULL UNIQUE,
 descripcion TEXT, costo_servicio DECIMAL(10,2) NOT NULL, duracion_minutos SMALLINT UNSIGNED,
 estado ENUM('activo','inactivo') DEFAULT 'activo', fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS citas (
 id_cita INT AUTO_INCREMENT PRIMARY KEY, id_paciente INT NOT NULL, id_doctor INT NOT NULL, id_servicio INT NOT NULL,
 fecha_cita DATE NOT NULL, hora_cita TIME NOT NULL, estado_cita ENUM('programada','confirmada','completada','cancelada','no_asistio') DEFAULT 'programada',
 notas TEXT, fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_cita_paciente FOREIGN KEY(id_paciente) REFERENCES pacientes(id_paciente),
 CONSTRAINT fk_cita_doctor FOREIGN KEY(id_doctor) REFERENCES doctores(id_doctor),
 CONSTRAINT fk_cita_servicio FOREIGN KEY(id_servicio) REFERENCES servicios(id_servicio),
 UNIQUE KEY uq_cita_doctor_hora(id_doctor,fecha_cita,hora_cita)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS consultas (
 id_consulta INT AUTO_INCREMENT PRIMARY KEY, id_cita INT NOT NULL UNIQUE, id_paciente INT NOT NULL, id_doctor INT NOT NULL,
 motivo_consulta TEXT NOT NULL, diagnostico TEXT NOT NULL, tratamiento TEXT, medicamentos_prescritos TEXT,
 observaciones TEXT, fecha_consulta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, proxima_cita DATE,
 estado_consulta ENUM('completada','pendiente','cancelada') DEFAULT 'completada',
 CONSTRAINT fk_consulta_cita FOREIGN KEY(id_cita) REFERENCES citas(id_cita),
 CONSTRAINT fk_consulta_paciente FOREIGN KEY(id_paciente) REFERENCES pacientes(id_paciente),
 CONSTRAINT fk_consulta_doctor FOREIGN KEY(id_doctor) REFERENCES doctores(id_doctor)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS historial_paciente (
 id_historial INT AUTO_INCREMENT PRIMARY KEY, id_paciente INT NOT NULL, id_doctor INT NOT NULL,
 tipo_evento VARCHAR(100) NOT NULL, descripcion_evento TEXT NOT NULL, datos_clinicos TEXT,
 fecha_evento DATETIME DEFAULT CURRENT_TIMESTAMP, fecha_proxima_revision DATE,
 CONSTRAINT fk_historial_paciente FOREIGN KEY(id_paciente) REFERENCES pacientes(id_paciente),
 CONSTRAINT fk_historial_doctor FOREIGN KEY(id_doctor) REFERENCES doctores(id_doctor)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS facturacion (
 id_factura INT AUTO_INCREMENT PRIMARY KEY, id_paciente INT NOT NULL, id_consulta INT NULL,
 numero_factura VARCHAR(50) NOT NULL UNIQUE, fecha_factura DATE NOT NULL,
 descripcion_servicios TEXT NOT NULL, subtotal DECIMAL(10,2) NOT NULL, impuesto DECIMAL(10,2) NOT NULL DEFAULT 0,
 total DECIMAL(10,2) NOT NULL, metodo_pago ENUM('efectivo','tarjeta','transferencia','cheque') DEFAULT 'efectivo',
 estado_factura ENUM('pagada','pendiente','anulada') DEFAULT 'pendiente', notas TEXT,
 CONSTRAINT fk_factura_paciente FOREIGN KEY(id_paciente) REFERENCES pacientes(id_paciente),
 CONSTRAINT fk_factura_consulta FOREIGN KEY(id_consulta) REFERENCES consultas(id_consulta) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS historial_facturacion (
 id_historial_factura INT AUTO_INCREMENT PRIMARY KEY, id_factura INT NOT NULL, id_paciente INT NOT NULL,
 monto_pago DECIMAL(10,2) NOT NULL, fecha_pago DATE NOT NULL, hora_pago TIME, metodo_pago ENUM('efectivo','tarjeta','transferencia','cheque') NOT NULL,
 referencia_pago VARCHAR(100), estado_pago ENUM('confirmado','pendiente','rechazado') DEFAULT 'confirmado',
 id_usuario_registra INT NULL, notas TEXT, fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_pago_factura FOREIGN KEY(id_factura) REFERENCES facturacion(id_factura),
 CONSTRAINT fk_pago_paciente FOREIGN KEY(id_paciente) REFERENCES pacientes(id_paciente),
 CONSTRAINT fk_pago_usuario FOREIGN KEY(id_usuario_registra) REFERENCES usuarios(id_usuario) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS proveedores (
 id_proveedor INT AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(150) NOT NULL, nit VARCHAR(30) UNIQUE,
 telefono VARCHAR(25), email VARCHAR(120), direccion VARCHAR(255), estado ENUM('activo','inactivo') DEFAULT 'activo'
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS inventario (
 id_inventario INT AUTO_INCREMENT PRIMARY KEY, id_proveedor INT NULL, nombre_producto VARCHAR(150) NOT NULL,
 descripcion TEXT, cantidad INT NOT NULL DEFAULT 0, cantidad_minima INT NOT NULL DEFAULT 0,
 precio_unitario DECIMAL(10,2) NOT NULL, fecha_vencimiento DATE, proveedor VARCHAR(150),
 estado ENUM('activo','inactivo') DEFAULT 'activo', fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_inventario_proveedor FOREIGN KEY(id_proveedor) REFERENCES proveedores(id_proveedor) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS movimientos_inventario (
 id_movimiento INT AUTO_INCREMENT PRIMARY KEY, id_inventario INT NOT NULL, id_usuario INT NULL,
 tipo_movimiento ENUM('entrada','salida','ajuste') NOT NULL, cantidad INT NOT NULL, motivo VARCHAR(255), fecha_movimiento DATETIME DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_movimiento_inventario FOREIGN KEY(id_inventario) REFERENCES inventario(id_inventario),
 CONSTRAINT fk_movimiento_usuario FOREIGN KEY(id_usuario) REFERENCES usuarios(id_usuario) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS auditorias (
 id_auditoria INT AUTO_INCREMENT PRIMARY KEY, id_usuario INT NULL, tabla_afectada VARCHAR(100) NOT NULL,
 tipo_operacion ENUM('INSERT','UPDATE','DELETE') NOT NULL, datos_antiguos JSON, datos_nuevos JSON,
 fecha_operacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP, direccion_ip VARCHAR(45),
 CONSTRAINT fk_auditoria_usuario FOREIGN KEY(id_usuario) REFERENCES usuarios(id_usuario) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO roles(id_rol,nombre_rol,descripcion) VALUES
(1,'Administrador','Acceso total'),(2,'Doctor','Atención clínica'),(3,'Recepcionista','Agenda y pacientes'),(4,'Paciente','Portal del paciente'),(5,'Contador','Cobros y reportes')
ON DUPLICATE KEY UPDATE nombre_rol=VALUES(nombre_rol);

-- ozuna07 / ozun@123
INSERT INTO usuarios(id_usuario,id_rol,nombre,apellido,usuario_login,email,telefono,cedula,password_hash,estado) VALUES
(1,1,'Ozuna','Administrador','ozuna07','ozuna07@clinicasonrisa.test','70000001','ADM-001','$2y$10$AzdkYBdWHjDFH2B9BymfuOemC6Jbaaw6WXDzwEOZjFGm5kpziI1Ty','activo'),
(2,2,'Laura','Mendoza','laura.mendoza','laura@clinicasonrisa.test','70000002','DOC-001','$2y$10$AzdkYBdWHjDFH2B9BymfuOemC6Jbaaw6WXDzwEOZjFGm5kpziI1Ty','activo'),
(3,2,'Miguel','Rojas','miguel.rojas','miguel@clinicasonrisa.test','70000003','DOC-002','$2y$10$AzdkYBdWHjDFH2B9BymfuOemC6Jbaaw6WXDzwEOZjFGm5kpziI1Ty','activo'),
(4,2,'Sofía','Torres','sofia.torres','sofia@clinicasonrisa.test','70000004','DOC-003','$2y$10$AzdkYBdWHjDFH2B9BymfuOemC6Jbaaw6WXDzwEOZjFGm5kpziI1Ty','activo'),
(5,2,'Daniel','Vargas','daniel.vargas','daniel@clinicasonrisa.test','70000005','DOC-004','$2y$10$AzdkYBdWHjDFH2B9BymfuOemC6Jbaaw6WXDzwEOZjFGm5kpziI1Ty','activo'),
(6,3,'Carla','Núñez','carla.nunez','carla@clinicasonrisa.test','70000006','REC-001','$2y$10$AzdkYBdWHjDFH2B9BymfuOemC6Jbaaw6WXDzwEOZjFGm5kpziI1Ty','activo'),
(7,4,'Ana','Pérez','ana.perez','ana@clinicasonrisa.test','70000007','PAC-001','$2y$10$AzdkYBdWHjDFH2B9BymfuOemC6Jbaaw6WXDzwEOZjFGm5kpziI1Ty','activo'),
(8,4,'José','Quispe','jose.quispe','jose@clinicasonrisa.test','70000008','PAC-002','$2y$10$AzdkYBdWHjDFH2B9BymfuOemC6Jbaaw6WXDzwEOZjFGm5kpziI1Ty','activo'),
(9,4,'María','Flores','maria.flores','maria@clinicasonrisa.test','70000009','PAC-003','$2y$10$AzdkYBdWHjDFH2B9BymfuOemC6Jbaaw6WXDzwEOZjFGm5kpziI1Ty','activo'),
(10,4,'Luis','Gómez','luis.gomez','luis@clinicasonrisa.test','70000010','PAC-004','$2y$10$AzdkYBdWHjDFH2B9BymfuOemC6Jbaaw6WXDzwEOZjFGm5kpziI1Ty','activo');

INSERT INTO especialidades(id_especialidad,nombre_especialidad,descripcion) VALUES
(1,'Odontología general','Prevención y tratamientos generales'),(2,'Ortodoncia','Corrección de alineación dental'),(3,'Endodoncia','Tratamiento de conductos'),(4,'Periodoncia','Cuidado de encías');
INSERT INTO doctores(id_doctor,id_usuario,id_especialidad,nombre,apellido,cedula,telefono,email,numero_colegiado,experiencia_anios,horario_entrada,horario_salida) VALUES
(1,2,1,'Laura','Mendoza','DOC-001','70000002','laura@clinicasonrisa.test','COL-1001',8,'08:00','16:00'),(2,3,2,'Miguel','Rojas','DOC-002','70000003','miguel@clinicasonrisa.test','COL-1002',6,'09:00','17:00'),(3,4,3,'Sofía','Torres','DOC-003','70000004','sofia@clinicasonrisa.test','COL-1003',10,'08:00','16:00'),(4,5,4,'Daniel','Vargas','DOC-004','70000005','daniel@clinicasonrisa.test','COL-1004',7,'10:00','18:00');
INSERT INTO pacientes(id_paciente,id_usuario,nombre,apellido,fecha_nacimiento,sexo,cedula,telefono,email,direccion,ciudad,tipo_sangre,alergias,enfermedades_cronicas,contacto_emergencia,telefono_emergencia) VALUES
(1,7,'Ana','Pérez','1994-05-12','femenino','PAC-001','70000007','ana@clinicasonrisa.test','Av. Libertad 101','La Paz','O+','Penicilina','Ninguna','Carlos Pérez','71000001'),(2,8,'José','Quispe','1985-09-03','masculino','PAC-002','70000008','jose@clinicasonrisa.test','Calle 8 #22','La Paz','A+','Ninguna','Hipertensión','Marta Quispe','71000002'),(3,9,'María','Flores','2001-01-21','femenino','PAC-003','70000009','maria@clinicasonrisa.test','Zona Sur 45','La Paz','B+','Látex','Ninguna','Elena Flores','71000003'),(4,10,'Luis','Gómez','1978-11-18','masculino','PAC-004','70000010','luis@clinicasonrisa.test','Av. Central 33','La Paz','AB+','Ninguna','Diabetes controlada','Rosa Gómez','71000004');
INSERT INTO servicios(id_servicio,nombre_servicio,descripcion,costo_servicio,duracion_minutos) VALUES
(1,'Consulta general','Evaluación odontológica',120.00,30),(2,'Limpieza dental','Profilaxis profesional',180.00,45),(3,'Endodoncia','Tratamiento de conducto',850.00,90),(4,'Ortodoncia','Control mensual',250.00,30);
INSERT INTO citas(id_cita,id_paciente,id_doctor,id_servicio,fecha_cita,hora_cita,estado_cita,notas) VALUES
(1,1,1,1,'2026-09-15','09:00','confirmada','Primera consulta'),(2,2,2,4,'2026-09-16','10:00','programada','Control'),(3,3,3,3,'2026-09-10','11:00','completada','Dolor dental'),(4,4,4,2,'2026-09-11','15:00','completada','Limpieza anual');
INSERT INTO consultas(id_consulta,id_cita,id_paciente,id_doctor,motivo_consulta,diagnostico,tratamiento,medicamentos_prescritos,estado_consulta) VALUES
(1,1,1,1,'Revisión general','Caries incipiente','Aplicar flúor','Ninguno','pendiente'),(2,2,2,2,'Control ortodoncia','Ajuste requerido','Cambio de ligas','Ibuprofeno si dolor','pendiente'),(3,3,3,3,'Dolor molar','Pulpitis reversible','Endodoncia','Ibuprofeno','completada'),(4,4,4,4,'Control preventivo','Gingivitis leve','Limpieza y educación','Enjuague bucal','completada');
INSERT INTO historial_paciente(id_historial,id_paciente,id_doctor,tipo_evento,descripcion_evento,datos_clinicos,fecha_proxima_revision) VALUES
(1,1,1,'Evaluación','Evaluación inicial registrada','Caries leve en molar 16','2026-10-15'),(2,2,2,'Ortodoncia','Ajuste de brackets','Arco superior ajustado','2026-10-16'),(3,3,3,'Endodoncia','Tratamiento de conducto iniciado','Molar 26','2026-09-24'),(4,4,4,'Profilaxis','Limpieza profesional realizada','Sangrado gingival leve','2027-03-11');
INSERT INTO facturacion(id_factura,id_paciente,id_consulta,numero_factura,fecha_factura,descripcion_servicios,subtotal,impuesto,total,metodo_pago,estado_factura) VALUES
(1,1,1,'FAC-0001','2026-09-15','Consulta general',120,0,120,'efectivo','pendiente'),(2,2,2,'FAC-0002','2026-09-16','Control de ortodoncia',250,0,250,'tarjeta','pendiente'),(3,3,3,'FAC-0003','2026-09-10','Endodoncia',850,0,850,'transferencia','pagada'),(4,4,4,'FAC-0004','2026-09-11','Limpieza dental',180,0,180,'efectivo','pagada');
INSERT INTO historial_facturacion(id_historial_factura,id_factura,id_paciente,monto_pago,fecha_pago,hora_pago,metodo_pago,referencia_pago,estado_pago,id_usuario_registra) VALUES
(1,1,1,120,'2026-09-15','09:30','efectivo','REC-001','pendiente',6),(2,2,2,250,'2026-09-16','10:30','tarjeta','POS-1002','pendiente',6),(3,3,3,850,'2026-09-10','12:30','transferencia','TRX-1003','confirmado',1),(4,4,4,180,'2026-09-11','15:30','efectivo','REC-004','confirmado',6);
INSERT INTO proveedores(id_proveedor,nombre,nit,telefono,email,direccion) VALUES
(1,'Dental Supply','NIT-1001','72000001','ventas@dentalsupply.test','Av. Comercial 10'),(2,'BioDental','NIT-1002','72000002','ventas@biodental.test','Calle Salud 20'),(3,'Orto Plus','NIT-1003','72000003','ventas@ortoplus.test','Av. Norte 30'),(4,'MedLab','NIT-1004','72000004','ventas@medlab.test','Zona Industrial 40');
INSERT INTO inventario(id_inventario,id_proveedor,nombre_producto,descripcion,cantidad,cantidad_minima,precio_unitario,fecha_vencimiento,proveedor) VALUES
(1,1,'Guantes de nitrilo','Caja de 100 unidades',80,20,65,'2027-04-30','Dental Supply'),(2,2,'Resina dental','Kit restaurador',12,5,210,'2027-01-15','BioDental'),(3,3,'Arcos de ortodoncia','Paquete de arcos',25,10,90,'2027-06-20','Orto Plus'),(4,4,'Anestesia local','Cartuchos de lidocaína',40,15,150,'2026-12-01','MedLab');
INSERT INTO movimientos_inventario(id_movimiento,id_inventario,id_usuario,tipo_movimiento,cantidad,motivo) VALUES
(1,1,6,'entrada',100,'Compra inicial'),(2,2,1,'entrada',15,'Reposición'),(3,3,6,'salida',5,'Uso en consulta'),(4,4,1,'ajuste',40,'Inventario inicial');
INSERT INTO auditorias(id_auditoria,id_usuario,tabla_afectada,tipo_operacion,datos_nuevos,direccion_ip) VALUES
(1,1,'usuarios','INSERT',JSON_OBJECT('usuario','ozuna07'),'127.0.0.1'),(2,6,'pacientes','INSERT',JSON_OBJECT('paciente','Ana Pérez'),'127.0.0.1'),(3,1,'inventario','INSERT',JSON_OBJECT('producto','Guantes de nitrilo'),'127.0.0.1'),(4,6,'citas','INSERT',JSON_OBJECT('cita',1),'127.0.0.1');

CREATE OR REPLACE VIEW vista_pacientes_completa AS SELECT id_paciente,nombre,apellido,TIMESTAMPDIFF(YEAR,fecha_nacimiento,CURDATE()) edad,sexo,cedula,telefono,email,ciudad,tipo_sangre,estado FROM pacientes;
CREATE OR REPLACE VIEW vista_doctores_completa AS SELECT d.id_doctor,d.nombre,d.apellido,e.nombre_especialidad,d.numero_colegiado,d.telefono,d.email,d.estado FROM doctores d JOIN especialidades e ON e.id_especialidad=d.id_especialidad;
CREATE OR REPLACE VIEW vista_facturas_pendientes AS SELECT f.id_factura,f.numero_factura,f.fecha_factura,CONCAT(p.nombre,' ',p.apellido) paciente,f.total,DATEDIFF(CURDATE(),f.fecha_factura) dias_vencimiento FROM facturacion f JOIN pacientes p ON p.id_paciente=f.id_paciente WHERE f.estado_factura='pendiente';
CREATE OR REPLACE VIEW vista_ingresos_mensuales AS SELECT DATE_FORMAT(fecha_factura,'%Y-%m') mes,COUNT(*) cantidad_facturas,SUM(total) total_ingresos,SUM(IF(estado_factura='pagada',total,0)) ingresos_cobrados FROM facturacion GROUP BY DATE_FORMAT(fecha_factura,'%Y-%m');

-- Normalización UTF-8 de los ejemplos con acentos. Se usan literales hexadecimales
-- para que la importación sea correcta independientemente de la consola usada.
UPDATE usuarios SET nombre=CONVERT(0x536F66C3AD61 USING utf8mb4) WHERE usuario_login='sofia.torres';
UPDATE usuarios SET apellido=CONVERT(0x4EC3BAC3B1657A USING utf8mb4) WHERE usuario_login='carla.nunez';
UPDATE usuarios SET apellido=CONVERT(0x50C3A972657A USING utf8mb4) WHERE usuario_login='ana.perez';
UPDATE usuarios SET nombre=CONVERT(0x4A6F73C3A9 USING utf8mb4) WHERE usuario_login='jose.quispe';
UPDATE usuarios SET nombre=CONVERT(0x4D6172C3AD61 USING utf8mb4) WHERE usuario_login='maria.flores';
UPDATE usuarios SET apellido=CONVERT(0x47C3B36D657A USING utf8mb4) WHERE usuario_login='luis.gomez';
UPDATE pacientes SET nombre=CONVERT(0x4A6F73C3A9 USING utf8mb4) WHERE id_paciente=2;
UPDATE pacientes SET nombre=CONVERT(0x4D6172C3AD61 USING utf8mb4) WHERE id_paciente=3;
UPDATE pacientes SET apellido=CONVERT(0x50C3A972657A USING utf8mb4) WHERE id_paciente=1;
UPDATE pacientes SET apellido=CONVERT(0x47C3B36D657A USING utf8mb4) WHERE id_paciente=4;
UPDATE doctores SET nombre=CONVERT(0x536F66C3AD61 USING utf8mb4) WHERE id_doctor=3;
