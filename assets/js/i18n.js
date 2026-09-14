/* Client-side interface translations. Spanish remains the source language. */
(function () {
    const translations = {
        'Dashboard': 'Dashboard', 'Usuarios': 'Users', 'Doctores': 'Doctors',
        'Especialidades': 'Specialties', 'Servicios': 'Services', 'Pacientes': 'Patients',
        'Citas': 'Appointments', 'Consultas': 'Consultations', 'Facturación': 'Billing',
        'Inventario': 'Inventory', 'Reportes': 'Reports', 'Pagos': 'Payments',
        'Salir': 'Log out', 'Mi Perfil': 'My profile', 'Configuración': 'Settings',
        'Nuevo registro': 'New record', 'Editar': 'Edit', 'Eliminar': 'Delete',
        'Guardar': 'Save', 'Cancelar': 'Cancel', 'Acciones': 'Actions',
        'Gestión de registros.': 'Manage records.', 'Administración de registros.': 'Manage records.',
        'Reportes': 'Reports', 'Resumen financiero y alertas operativas.': 'Financial summary and operational alerts.',
        'Ingresos mensuales': 'Monthly income', 'Facturas pendientes': 'Outstanding invoices',
        'Inventario por reponer': 'Inventory to replenish', 'No hay registros para mostrar.': 'There are no records to display.',
        'No hay datos disponibles.': 'No data available.', 'Volver al panel': 'Back to dashboard',
        'Nuevo Paciente': 'New patient', 'Buscar': 'Search', 'Limpiar': 'Clear',
        'Fecha': 'Date', 'Paciente': 'Patient', 'Doctor': 'Doctor', 'Estado': 'Status',
        'Total': 'Total', 'Factura': 'Invoice', 'Método': 'Method', 'Descripción': 'Description',
        'Nombre': 'Name', 'Apellido': 'Last name', 'Correo': 'Email', 'Teléfono': 'Phone',
        'Dirección': 'Address', 'Ciudad': 'City', 'Contraseña': 'Password',
        'Seleccione…': 'Select…', 'Editar registro': 'Edit record', 'Nuevo registro': 'New record',
        'Historial clínico': 'Clinical history', 'Consulta de registros del sistema.': 'System records overview.',
        'Facturación Pendiente': 'Outstanding billing', 'Citas Hoy': "Today's appointments",
        'Total Pacientes': 'Total patients', 'Total Doctores': 'Total doctors', 'Total Usuarios': 'Total users',
        'Acciones Rápidas': 'Quick actions', 'Crear Factura': 'Create invoice', 'Ver Reportes': 'View reports',
        'Registrar': 'Register', 'Imprimir': 'Print', 'Imprimir factura': 'Print invoice'
    };

    // Textos de las vistas especializadas (citas, consultas, perfiles y calendario).
    Object.assign(translations, {
        'Español': 'Spanish', 'Idioma / Language': 'Language', 'Bienvenido,': 'Welcome,', 'Rol:': 'Role:',
        'Usuarios pacientes': 'Patient users', 'Nuevo Usuario': 'New user', 'Nueva Cita': 'New appointment',
        'Nuevo usuario': 'New user', 'Nuevo usuario paciente': 'New patient user', 'Guardar usuario': 'Save user',
        'Agendar cita': 'Schedule appointment', 'Agendar nueva cita': 'Schedule new appointment',
        'Cambiar cita': 'Reschedule appointment', 'Guardar cambio': 'Save changes', 'Reservar cita': 'Book appointment',
        'Cambiar': 'Reschedule', 'Mis citas': 'My appointments', 'Mis Citas': 'My appointments',
        'Mis Próximas Citas': 'My upcoming appointments', 'Médicos disponibles': 'Available doctors',
        'Historial y próximas citas': 'History and upcoming appointments', 'Médico': 'Doctor', 'Servicio': 'Service',
        'Hora': 'Time', 'Fecha': 'Date', 'Notas': 'Notes', 'Cédula': 'National ID',
        'Seleccione un médico': 'Select a doctor', 'Seleccione un servicio': 'Select a service',
        'Seleccione médico, servicio y fecha.': 'Select a doctor, service, and date.',
        'Motivo de la cita o información adicional': 'Reason for the appointment or additional information',
        'Buscar por nombre o cédula': 'Search by name or national ID',
        'Registro y búsqueda rápida por nombre o cédula.': 'Quick registration and search by name or national ID.',
        'Confirmación de citas': 'Appointment confirmation', 'Mis citas de atención': 'My care appointments',
        'Nueva cita': 'New appointment', 'Confirmar': 'Confirm', 'Completar cita': 'Complete appointment',
        'Sin acción': 'No action', 'Acción': 'Action', 'Especialidad': 'Specialty',
        'Mis consultas clínicas': 'My clinical consultations', 'Consultas pendientes de registrar': 'Consultations pending registration',
        'Mis consultas': 'My consultations', 'Registrar consulta': 'Register consultation',
        'Editar consulta pendiente': 'Edit pending consultation', 'Guardar consulta': 'Save consultation',
        'Completar consulta': 'Complete consultation', 'Motivo de consulta': 'Reason for consultation',
        'Diagnóstico': 'Diagnosis', 'Tratamiento': 'Treatment', 'Medicamentos prescritos': 'Prescribed medications',
        'Observaciones': 'Notes', 'Próxima cita': 'Next appointment',
        'Citas Programadas Hoy': "Today's scheduled appointments", 'Mis Citas de Hoy': "My appointments today",
        'Consultas Este Mes': 'Consultations this month', 'Total Pacientes': 'Total patients',
        'Citas programadas por día': 'Scheduled appointments by day', 'Día con citas': 'Day with appointments',
        'Día actual': 'Today', 'Sin citas': 'No appointments', 'Libre': 'Available',
        'Solo se cuentan las citas pendientes de atender o confirmadas.': 'Only pending or confirmed appointments are counted.',
        'Lun': 'Mon', 'Mar': 'Tue', 'Mié': 'Wed', 'Jue': 'Thu', 'Vie': 'Fri', 'Sáb': 'Sat', 'Dom': 'Sun',
        'Mes anterior': 'Previous month', 'Mes siguiente': 'Next month',
        'pendiente': 'pending', 'programada': 'scheduled', 'confirmada': 'confirmed', 'completada': 'completed',
        'cancelada': 'cancelled', 'pagada': 'paid', 'activo': 'active', 'inactivo': 'inactive',
        'No hay citas para mostrar.': 'There are no appointments to display.',
        'No tienes citas registradas para hoy.': 'You have no appointments scheduled for today.',
        'Aún no tienes citas registradas.': 'You do not have any appointments yet.',
        'Aún no tienes consultas registradas.': 'You do not have any consultations yet.',
        'No hay citas completadas pendientes de registrar.': 'There are no completed appointments pending registration.',
        'Gestión financiera': 'Financial management', 'Facturas, pagos de clientes y nómina de empleados.': 'Invoices, customer payments, and employee payroll.',
        'Nómina': 'Payroll', 'Pagos de nómina': 'Payroll payments', 'Salario base': 'Base salary',
        'Empleado': 'Employee', 'Cargo': 'Position', 'Período': 'Period', 'Pagar': 'Pay',
        'Compras': 'Purchases', 'Compras de equipos e inventario': 'Equipment and inventory purchases',
        'Registra adquisiciones y actualiza existencias automáticamente.': 'Record purchases and update stock automatically.',
        'Nueva compra': 'New purchase', 'Equipo o producto *': 'Equipment or product *', 'Cantidad *': 'Quantity *',
        'Costo unitario ($) *': 'Unit cost ($) *', 'Proveedor': 'Supplier', 'Comprobante / referencia': 'Receipt / reference',
        'Registrar compra': 'Record purchase', 'Compras recientes': 'Recent purchases', 'Producto': 'Product',
        'Cantidad': 'Quantity', 'Uso y solicitud de materiales': 'Material use and request',
        'Reporte el material gastable utilizado; el sistema alertará cuando deba reponerse.': 'Report consumable materials used; the system will alert you when restocking is needed.',
        'Material gastable': 'Consumable material', 'Cantidad utilizada': 'Quantity used', 'Motivo / consulta': 'Reason / consultation',
        'Reportar uso': 'Report use', 'Materiales por reponer': 'Materials to restock', 'Disponible': 'Available',
        'Mínimo': 'Minimum', 'Facturas pagadas': 'Paid invoices', 'Compras de equipos e inventario': 'Equipment and inventory purchases',
        'Factura o comprobante': 'Invoice or receipt', 'Fecha de pago': 'Payment date', 'Todos los cargos': 'All positions',
        'registrada': 'recorded', 'Registrada': 'Recorded', 'anulada': 'voided', 'Anulada': 'Voided',
        'entrada': 'entry', 'salida': 'outgoing', 'ajuste': 'adjustment', 'pagado': 'paid', 'Pagado': 'Paid',
        'anulado': 'voided', 'Anulado': 'Voided', 'Uso clínico reportado': 'Clinical use reported',
        'Configuración': 'Settings', 'Clínica': 'Clinic', 'Tiempo de sesión': 'Session timeout',
        'Mi perfil': 'My profile', 'Usuario': 'Username', 'Correo': 'Email', 'Teléfono': 'Phone',
        'Consulta de registros del sistema.': 'System records overview.',
        'Resumen financiero y alertas operativas.': 'Financial summary and operational alerts.',
        'Comprobante de servicios odontológicos': 'Dental services receipt', 'Factura': 'Invoice',
        'Facturar a': 'Bill to', 'Información de pago': 'Payment information', 'Método:': 'Method:',
        'Estado:': 'Status:', 'Pagado:': 'Paid:', 'Saldo:': 'Balance:', 'Volver': 'Back',
        'Panel de Control - Clínica Dental Mi Sonrisa Feliz': 'Control panel - Happy Smile Dental Clinic',
        'Factura': 'Invoice'
    });

    const prefixTranslations = {
        'Bienvenido,': 'Welcome,',
        'Paciente:': 'Patient:',
        'Horario:': 'Schedule:',
        'Enero': 'January', 'Febrero': 'February', 'Marzo': 'March', 'Abril': 'April',
        'Mayo': 'May', 'Junio': 'June', 'Julio': 'July', 'Agosto': 'August',
        'Septiembre': 'September', 'Octubre': 'October', 'Noviembre': 'November', 'Diciembre': 'December'
    };

    // Valores controlados que llegan desde la base de datos. No se traducen nombres,
    // diagnósticos, direcciones ni notas, pues son información propia del paciente.
    const databaseTranslations = {
        'Odontología general': 'General dentistry',
        'Consulta general': 'General consultation',
        'Limpieza dental': 'Dental cleaning',
        'Recepcionista': 'Receptionist',
        'Administrador': 'Administrator',
        'Endodoncia': 'Endodontics',
        'Ortodoncia': 'Orthodontics',
        'Periodoncia': 'Periodontics',
        'Contador': 'Accountant',
        'Paciente': 'Patient',
        'programada': 'scheduled', 'Programada': 'Scheduled',
        'confirmada': 'confirmed', 'Confirmada': 'Confirmed',
        'completada': 'completed', 'Completada': 'Completed',
        'cancelada': 'cancelled', 'Cancelada': 'Cancelled',
        'no_asistio': 'did not attend', 'No asistió': 'Did not attend',
        'pendiente': 'pending', 'Pendiente': 'Pending',
        'pagada': 'paid', 'Pagada': 'Paid', 'anulada': 'voided', 'Anulada': 'Voided',
        'activo': 'active', 'Activo': 'Active', 'inactivo': 'inactive', 'Inactivo': 'Inactive',
        'confirmado': 'confirmed', 'Confirmado': 'Confirmed',
        'rechazado': 'rejected', 'Rechazado': 'Rejected',
        'efectivo': 'cash', 'Efectivo': 'Cash', 'tarjeta': 'card', 'Tarjeta': 'Card',
        'transferencia': 'bank transfer', 'Transferencia': 'Bank transfer', 'cheque': 'check', 'Cheque': 'Check',
        'masculino': 'male', 'Masculino': 'Male', 'femenino': 'female', 'Femenino': 'Female',
        'otro': 'other', 'Otro': 'Other', 'soltero': 'single', 'Soltero': 'Single',
        'casado': 'married', 'Casado': 'Married', 'divorciado': 'divorced', 'Divorciado': 'Divorced',
        'viudo': 'widowed', 'Viudo': 'Widowed', 'licencia': 'on leave', 'Licencia': 'On leave'
    };

    function translateDatabaseTerms(text) {
        let translated = text;
        // Las frases más largas se sustituyen primero para preservar su significado.
        Object.entries(databaseTranslations)
            .sort(([sourceA], [sourceB]) => sourceB.length - sourceA.length)
            .forEach(([source, target]) => {
                translated = translated.replaceAll(source, target);
            });
        return translated;
    }

    // Reemplaza los textos visibles de una página por su traducción al inglés.
    function translateTextNodes(root) {
        const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT);
        const nodes = [];
        while (walker.nextNode()) nodes.push(walker.currentNode);
        nodes.forEach(node => {
            const original = node.nodeValue;
            const trimmed = original.trim();
            if (translations[trimmed]) {
                node.nodeValue = original.replace(trimmed, translations[trimmed]);
                return;
            }
            Object.entries(prefixTranslations).some(([spanish, english]) => {
                if (!trimmed.startsWith(spanish)) return false;
                node.nodeValue = original.replace(trimmed, english + trimmed.slice(spanish.length));
                return true;
            });
            const translatedValue = translateDatabaseTerms(node.nodeValue);
            if (translatedValue !== node.nodeValue) node.nodeValue = translatedValue;
        });
        root.querySelectorAll('[placeholder], [title], [aria-label]').forEach(element => {
            ['placeholder', 'title', 'aria-label'].forEach(attribute => {
                const value = element.getAttribute(attribute);
                if (translations[value]) element.setAttribute(attribute, translations[value]);
            });
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const select = document.getElementById('languageSelect');
        const language = localStorage.getItem('clinicLanguage') || 'es';
        document.documentElement.lang = language;
        if (select) {
            select.value = language;
            select.addEventListener('change', () => {
                localStorage.setItem('clinicLanguage', select.value);
                window.location.reload();
            });
        }
        if (language === 'en') {
            translateTextNodes(document.body);
            if (translations[document.title]) document.title = translations[document.title];
        }

        // Facturación: añadir acceso directo al comprobante imprimible.
        if (window.location.pathname.endsWith('/pages/facturacion.php')) {
            document.querySelectorAll('tbody tr').forEach(row => {
                const id = row.querySelector('td');
                const actions = row.querySelector('.actions');
                if (!id || !actions || !/^\d+$/.test(id.textContent.trim())) return;
                const link = document.createElement('a');
                link.className = 'btn btn-sm btn-secondary';
                link.href = `factura_imprimir.php?id=${encodeURIComponent(id.textContent.trim())}`;
                link.target = '_blank';
                link.rel = 'noopener';
                link.textContent = language === 'en' ? 'Print invoice' : 'Imprimir factura';
                actions.prepend(link);
            });
        }
    });
})();
