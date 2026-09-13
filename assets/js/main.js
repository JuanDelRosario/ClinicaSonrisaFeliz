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

    // Sidebar responsivo
    const menuToggle = document.getElementById('menuToggle');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');

    if (menuToggle) {
        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
    }

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
    }

    // Cerrar sidebar al hacer clic en un link
    if (sidebar) {
        const navLinks = sidebar.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                sidebar.classList.remove('active');
            });
        });
    }

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
