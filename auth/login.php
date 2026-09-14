<?php header('Content-Type: text/html; charset=UTF-8'); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Clínica Dental Mi Sonrisa Feliz</title>
    <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <!-- Logo/Encabezado -->
            <div class="login-header">
                <div class="clinic-logo">
                    <img src="../assets/images/logo-mi-sonrisa-feliz.png?v=<?php echo filemtime(__DIR__ . '/../assets/images/logo-mi-sonrisa-feliz.png'); ?>" alt="Logo de Mi Sonrisa Feliz">
                </div>
                <h1>Mi Sonrisa Feliz</h1>
                <p>Clínica Dental</p>
            </div>

            <!-- Formulario de Login -->
            <form id="loginForm" class="login-form" action="authenticate.php" method="POST">
                <div class="form-group">
                    <label for="usuario_login">Usuario</label>
                    <input 
                        type="text" 
                        id="usuario_login" 
                        name="usuario_login" 
                        class="form-control" 
                        placeholder="Ingrese su usuario"
                        required
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label for="contraseña">Contraseña</label>
                    <div class="password-field">
                        <input 
                            type="password" 
                            id="contraseña" 
                            name="contraseña" 
                            class="form-control" 
                            placeholder="Ingrese su contraseña"
                            required
                        >
                        <button type="button" class="toggle-password" aria-label="Mostrar contraseña">
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="remember_me">
                        Recuérdame
                    </label>
                </div>

                <button type="submit" class="btn-login">Iniciar Sesión</button>

                <!-- Mensaje de alerta -->
                <div id="alertMessage" class="alert alert-danger" style="display: none;"></div>
                <div id="successMessage" class="alert alert-success" style="display: none;"></div>
            </form>

            <!-- Información de la clínica -->
            <div class="login-footer">
                
            </div>
        </div>

        <!-- Panel derecho con información -->
        <div class="login-info">
            <div class="info-content">
                <h2>Bienvenido</h2>
                <p>Sistema de Gestión para Clínica Dental</p>
                <ul class="features">
                    <li><svg aria-hidden="true" viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"/></svg>Gestión de Pacientes</li>
                    <li><svg aria-hidden="true" viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"/></svg>Programación de Citas</li>
                    <li><svg aria-hidden="true" viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"/></svg>Registro de Consultas</li>
                    <li><svg aria-hidden="true" viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"/></svg>Control de Facturación</li>
                    <li><svg aria-hidden="true" viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"/></svg>Historial Médico</li>
                    <li><svg aria-hidden="true" viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"/></svg>Reporte de Ingresos</li>
                </ul>
                <div class="contact-info">
                    <p><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 2 .8 2.9a2 2 0 0 1-.5 2.1L8.1 10a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.5c.9.4 1.9.7 2.9.8A2 2 0 0 1 22 16.9Z"/></svg>+1 (849) 282-5216</p>
                    <p><svg aria-hidden="true" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>info@clinicasonrisafeliz.com</p>
                    <p><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/></svg>Villa Mella, Vista Bella - Santo Domingo Norte</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        document.querySelector('.toggle-password').addEventListener('click', function(e) {
            e.preventDefault();
            const input = document.querySelector('#contraseña');
            input.type = input.type === 'password' ? 'text' : 'password';
            this.classList.toggle('active');
        });

        // Manejar envío del formulario
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const alertDiv = document.getElementById('alertMessage');
            const successDiv = document.getElementById('successMessage');

            // Limpiar mensajes previos
            alertDiv.style.display = 'none';
            successDiv.style.display = 'none';

            try {
                const response = await fetch('authenticate.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    successDiv.textContent = data.message;
                    successDiv.style.display = 'block';
                    
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1000);
                } else {
                    alertDiv.textContent = data.message;
                    alertDiv.style.display = 'block';
                }
            } catch (error) {
                alertDiv.textContent = 'Error al conectar con el servidor';
                alertDiv.style.display = 'block';
            }
        });

        // Mostrar mensaje de timeout si existe
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('timeout') === '1') {
            const alertDiv = document.getElementById('alertMessage');
            alertDiv.textContent = 'Su sesión ha expirado. Por favor inicie sesión nuevamente.';
            alertDiv.style.display = 'block';
        }
    </script>
    <script src="../assets/js/i18n.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/i18n.js'); ?>"></script>
</body>
</html>
