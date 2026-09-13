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

    // Reemplaza los textos visibles de una página por su traducción al inglés.
    function translateTextNodes(root) {
        const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT);
        const nodes = [];
        while (walker.nextNode()) nodes.push(walker.currentNode);
        nodes.forEach(node => {
            const original = node.nodeValue;
            const trimmed = original.trim();
            if (translations[trimmed]) node.nodeValue = original.replace(trimmed, translations[trimmed]);
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
        if (language === 'en') translateTextNodes(document.body);

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
