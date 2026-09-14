/**
 * JavaScript Principal
 * Clínica Dental "Mi Sonrisa Feliz"
 */

// Utilidades Globales
const app = {
    baseUrl: document.documentElement.getAttribute('data-base-url') || '/ClinicaSonrisaFeliz',
    
    // Mostrar notificación
    notify: function(message, type = 'info', duration = 3000) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, duration);
    },

    // Confirmar acción
    confirm: async function(message) {
        return new Promise((resolve) => {
            const modal = document.createElement('div');
            modal.className = 'confirm-modal';
            modal.innerHTML = `
                <div class="confirm-dialog">
                    <p>${message}</p>
                    <div class="confirm-buttons">
                        <button class="btn-cancel">Cancelar</button>
                        <button class="btn-confirm">Confirmar</button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            
            modal.querySelector('.btn-cancel').addEventListener('click', () => {
                modal.remove();
                resolve(false);
            });
            
            modal.querySelector('.btn-confirm').addEventListener('click', () => {
                modal.remove();
                resolve(true);
            });
        });
    },

    // Formatear moneda
    formatCurrency: function(value) {
        return new Intl.NumberFormat('es-ES', {
            style: 'currency',
            currency: 'EUR'
        }).format(value);
    },

    // Formatear fecha
    formatDate: function(date) {
        if (typeof date === 'string') {
            date = new Date(date);
        }
        return date.toLocaleDateString('es-ES');
    },

    // Formatear hora
    formatTime: function(time) {
        if (typeof time === 'string') {
            return time.substring(0, 5);
        }
        return time;
    }
};

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Menú de usuario dropdown
    const userMenuBtn = document.querySelector('.btn-user-menu');
    if (userMenuBtn) {
        const dropdown = userMenuBtn.nextElementSibling;
        
        userMenuBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
        });

        document.addEventListener('click', function(e) {
            if (!userMenuBtn.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });
    }

    // El comportamiento del menú lateral se centraliza en sidebar.js para evitar
    // que dos controladores alternen el mismo estado y se cancelen entre sí.

    // Las acciones rápidas abren directamente el formulario de alta.
    document.querySelectorAll('.quick-actions a.action-btn').forEach(link => {
        const target = new URL(link.href, window.location.origin);
        if (['pacientes.php', 'citas.php', 'facturacion.php', 'usuarios.php'].some(page => target.pathname.endsWith('/' + page))) {
            target.searchParams.set('new', '1');
            link.href = target.toString();
        }
    });

    // Marcar enlace activo
    const currentUrl = window.location.pathname;
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        if (link.href.includes(currentUrl)) {
            link.classList.add('active');
        }
    });

    // Notificación de cierre de sesión
    const logoutLink = document.querySelector('a[href*="logout"]');
    if (logoutLink) {
        logoutLink.addEventListener('click', function(e) {
            // Permitir el logout sin confirmación
        });
    }
});

// Estilos inyectados
const styles = `
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 16px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 2000;
        animation: slideInRight 0.3s ease;
        max-width: 400px;
    }

    .notification-success {
        background-color: #c8e6c9;
        color: #2e7d32;
        border-left: 4px solid #4CAF50;
    }

    .notification-error {
        background-color: #ffcdd2;
        color: #c62828;
        border-left: 4px solid #f44336;
    }

    .notification-warning {
        background-color: #ffe0b2;
        color: #e65100;
        border-left: 4px solid #ff9800;
    }

    .notification-info {
        background-color: #bbdefb;
        color: #0d47a1;
        border-left: 4px solid #2196F3;
    }

    .confirm-modal {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2000;
    }

    .confirm-dialog {
        background: white;
        padding: 30px;
        border-radius: 8px;
        max-width: 400px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
    }

    .confirm-dialog p {
        margin-bottom: 20px;
        color: #333;
        font-size: 15px;
    }

    .confirm-buttons {
        display: flex;
        gap: 12px;
        justify-content: flex-end;
    }

    .btn-cancel,
    .btn-confirm {
        padding: 8px 16px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-cancel {
        background-color: #eeeeee;
        color: #333;
    }

    .btn-cancel:hover {
        background-color: #e0e0e0;
    }

    .btn-confirm {
        background-color: #4CAF50;
        color: white;
    }

    .btn-confirm:hover {
        background-color: #388E3C;
    }

    @keyframes slideInRight {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
`;

// Inyectar estilos
const styleTag = document.createElement('style');
styleTag.textContent = styles;
document.head.appendChild(styleTag);

// Reemplaza los emojis heredados del tablero por iconos SVG, nítidos en cualquier resolución.
document.addEventListener('DOMContentLoaded', () => {
    const iconPaths = {
        users: '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
        patient: '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0M19 8v6M16 11h6"/>',
        doctor: '<path d="M4 4h16v16H4z"/><path d="M12 7v10M7 12h10"/>',
        calendar: '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/>',
        document: '<path d="M6 3h9l3 3v15H6z"/><path d="M15 3v4h4M9 12h6M9 16h6"/>',
        money: '<path d="M12 2v20M17 6.5c-1.2-1-2.8-1.5-5-1.5-3.2 0-5 1.5-5 3.5s1.8 3.1 5 3.7 5 1.7 5 3.8-1.8 3.5-5 3.5c-2.2 0-4-.7-5.3-1.9"/>',
        clock: '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        check: '<path d="m5 12 4 4L19 6"/>',
        report: '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>',
        add: '<path d="M12 5v14M5 12h14"/>'
    };
    const svg = (icon) => `<svg class="ui-icon" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">${iconPaths[icon]}</svg>`;
    const statIcons = ['users', 'patient', 'doctor', 'calendar', 'money', 'calendar', 'document', 'patient', 'calendar', 'patient', 'clock', 'calendar', 'document', 'money', 'document', 'money', 'check'];
    document.querySelectorAll('.stat-icon').forEach((element, index) => { element.innerHTML = svg(statIcons[index] || 'document'); });
    document.querySelectorAll('.action-btn').forEach((element) => {
        const href = element.getAttribute('href') || '';
        const icon = href.includes('reportes') ? 'report' : href.includes('facturacion') ? 'money' : href.includes('citas') ? 'calendar' : href.includes('pacientes') ? 'patient' : 'add';
        element.textContent = element.textContent.replace(/^[^\p{L}\p{N}]+/u, '').trim();
        element.insertAdjacentHTML('afterbegin', svg(icon));
    });

    // Recepción puede filtrar la facturación sin alterar el listado financiero del Contador.
    const isBillingPage = window.location.pathname.endsWith('/pages/facturacion.php');
    const roleName = document.querySelector('.user-role')?.textContent.trim();
    const billingSection = document.querySelector('.content-section.mt-20');
    if (isBillingPage && roleName === 'Recepcionista' && billingSection) {
        const query = new URLSearchParams(window.location.search);
        const form = document.createElement('form');
        form.method = 'get';
        form.className = 'billing-search';
        form.innerHTML = '<label for="buscar_factura">Buscar factura, paciente o cédula</label><div><input class="form-control" id="buscar_factura" type="search" name="buscar_factura" placeholder="Número de factura, nombre o cédula"><button class="btn btn-primary" type="submit">Buscar</button></div>';
        form.querySelector('input').value = query.get('buscar_factura') || '';
        if (query.has('buscar_factura')) {
            const clear = document.createElement('a');
            clear.className = 'btn btn-secondary';
            clear.href = 'facturacion.php';
            clear.textContent = 'Limpiar';
            form.querySelector('div').appendChild(clear);
        }
        billingSection.prepend(form);
    }
});
