/**
 * Funciones de Formularios
 */

// Clase para manejar formularios
class FormHandler {
    constructor(formId) {
        this.form = document.getElementById(formId);
        if (!this.form) {
            console.error(`Formulario con ID '${formId}' no encontrado`);
            return;
        }
        
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));
    }

    async handleSubmit(e) {
        e.preventDefault();

        // Validar formulario
        if (!this.form.checkValidity()) {
            this.showError('Por favor complete todos los campos requeridos');
            return;
        }

        // Mostrar loading
        this.showLoading();

        try {
            const formData = new FormData(this.form);
            const response = await fetch(this.form.action, {
                method: this.form.method || 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.showSuccess(data.message || 'Operación realizada exitosamente');
                if (data.redirect) {
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1500);
                } else {
                    this.form.reset();
                }
            } else {
                this.showError(data.message || 'Error al procesar la solicitud');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showError('Error de conexión. Por favor intente nuevamente.');
        } finally {
            this.hideLoading();
        }
    }

    showError(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-danger';
        alertDiv.textContent = message;
        this.form.insertBefore(alertDiv, this.form.firstChild);
        
        setTimeout(() => alertDiv.remove(), 5000);
    }

    showSuccess(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-success';
        alertDiv.textContent = message;
        this.form.insertBefore(alertDiv, this.form.firstChild);
        
        setTimeout(() => alertDiv.remove(), 5000);
    }

    showLoading() {
        const button = this.form.querySelector('button[type="submit"]');
        if (button) {
            button.disabled = true;
            button.textContent = 'Procesando...';
        }
    }

    hideLoading() {
        const button = this.form.querySelector('button[type="submit"]');
        if (button) {
            button.disabled = false;
            button.textContent = button.getAttribute('data-original-text') || 'Guardar';
        }
    }
}

// Validación de formularios
function validateForm(form) {
    let isValid = true;

    const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.classList.add('is-invalid');
            showFieldError(input, 'Este campo es requerido');
        } else {
            input.classList.remove('is-invalid');
            removeFieldError(input);
        }
    });

    return isValid;
}

// Mostrar error en campo
function showFieldError(input, message) {
    let errorDiv = input.parentElement.querySelector('.field-error');
    
    if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.className = 'field-error';
        errorDiv.textContent = message;
        input.parentElement.appendChild(errorDiv);
    }
}

// Remover error de campo
function removeFieldError(input) {
    const errorDiv = input.parentElement.querySelector('.field-error');
    if (errorDiv) {
        errorDiv.remove();
    }
}

// Validación de email
function validateEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

// Validación de teléfono
function validatePhone(phone) {
    const regex = /^[\d\s\-\+\(\)]+$/;
    return regex.test(phone) && phone.replace(/\D/g, '').length >= 9;
}

// Inicializar tooltips
function initTooltips() {
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(element => {
        element.addEventListener('mouseenter', function() {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = this.getAttribute('data-tooltip');
            document.body.appendChild(tooltip);

            const rect = this.getBoundingClientRect();
            tooltip.style.top = (rect.top - tooltip.offsetHeight - 10) + 'px';
            tooltip.style.left = (rect.left + rect.width / 2 - tooltip.offsetWidth / 2) + 'px';
        });

        element.addEventListener('mouseleave', function() {
            const tooltip = document.querySelector('.tooltip');
            if (tooltip) tooltip.remove();
        });
    });
}

// Inicializar datepicker
function initDatePickers() {
    const dateInputs = document.querySelectorAll('input[type="date"]');
    // Los navegadores modernos tienen datepicker nativo
}

// Inicializar timepicker
function initTimePickers() {
    const timeInputs = document.querySelectorAll('input[type="time"]');
    // Los navegadores modernos tienen timepicker nativo
}

// Formatear input de moneda
function initCurrencyInputs() {
    const currencyInputs = document.querySelectorAll('.currency-input');
    
    currencyInputs.forEach(input => {
        input.addEventListener('input', function() {
            let value = this.value.replace(/[^\d.]/g, '');
            if (value) {
                value = parseFloat(value).toFixed(2);
                this.value = value;
            }
        });
    });
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    initTooltips();
    initDatePickers();
    initTimePickers();
    initCurrencyInputs();

    // Prevenir envío múltiple de formularios
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const submitButtons = this.querySelectorAll('button[type="submit"]');
            submitButtons.forEach(btn => {
                btn.disabled = true;
                btn.setAttribute('data-original-text', btn.textContent);
            });
        });
    });

    // Validación en tiempo real
    const requiredInputs = document.querySelectorAll('input[required], textarea[required], select[required]');
    
    requiredInputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (!this.value.trim()) {
                this.classList.add('is-invalid');
                showFieldError(this, 'Este campo es requerido');
            } else {
                this.classList.remove('is-invalid');
                removeFieldError(this);
            }
        });

        input.addEventListener('input', function() {
            if (this.value.trim()) {
                this.classList.remove('is-invalid');
                removeFieldError(this);
            }
        });
    });
});

// Estilos CSS para validación
const validationStyles = `
    .is-invalid {
        border-color: #f44336 !important;
        background-color: #ffebee;
    }

    .field-error {
        color: #c62828;
        font-size: 12px;
        margin-top: 4px;
        display: block;
    }

    .tooltip {
        position: fixed;
        background-color: #333;
        color: white;
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 12px;
        z-index: 1000;
        pointer-events: none;
    }

    .tooltip::after {
        content: '';
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        border: 4px solid transparent;
        border-top-color: #333;
    }
`;

// Inyectar estilos de validación
const styleTag = document.createElement('style');
styleTag.textContent = validationStyles;
document.head.appendChild(styleTag);
