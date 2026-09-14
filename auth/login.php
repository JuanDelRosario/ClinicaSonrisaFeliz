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
    <main class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="clinic-logo">
                    <img src="../assets/images/logo-mi-sonrisa-feliz.png?v=<?php echo filemtime(__DIR__ . '/../assets/images/logo-mi-sonrisa-feliz.png'); ?>" alt="Logo de Mi Sonrisa Feliz">
                </div>
                <span class="login-kicker">ACCESO SEGURO</span>
                <h1>Bienvenido de nuevo</h1>
                <p>Ingresa tus credenciales para continuar en el sistema.</p>
            </div>

            <form id="loginForm" class="login-form" action="authenticate.php" method="POST">
                <div class="form-group">
                    <label for="usuario_login">Usuario</label>
                    <div class="input-with-icon">
                        <svg class="field-icon" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
                        <input type="text" id="usuario_login" name="usuario_login" class="form-control" placeholder="Ingrese su usuario" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label for="contraseña">Contraseña</label>
                    <div class="password-field">
                        <svg class="field-icon" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
                        <input type="password" id="contraseña" name="contraseña" class="form-control" placeholder="Ingrese su contraseña" required>
                        <button type="button" class="toggle-password" aria-label="Mostrar contraseña">
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                        </button>
                    </div>
                </div>

                <div class="form-options">
                    <label class="remember-option">
                        <input type="checkbox" name="remember_me">
                        Recuérdame
                    </label>
                </div>

                <button type="submit" class="btn-login"><span>Iniciar sesión</span><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></button>

                <div id="alertMessage" class="alert alert-danger" style="display: none;"></div>
                <div id="successMessage" class="alert alert-success" style="display: none;"></div>
            </form>

            <p class="login-footer">Sistema de gestión odontológica · Acceso autorizado</p>
        </div>

        <div class="login-info">
            <div class="info-content">
                <div class="info-brand">Mi Sonrisa Feliz</div>
                <span class="info-badge">CLÍNICA DENTAL</span>
                <h2>Gestión clínica con claridad y confianza.</h2>
                <p>Un espacio integrado para acompañar cada etapa de la atención odontológica.</p>
                <ul class="features">
                    <li><span>✓</span> Agenda, pacientes y consultas</li>
                    <li><span>✓</span> Facturación y control administrativo</li>
                    <li><span>✓</span> Información segura y centralizada</li>
                </ul>
                <div class="contact-info">
                    <p>+1 (849) 282-5216</p>
                    <p>info@clinicasonrisafeliz.com</p>
                    <p>Villa Mella, Santo Domingo Norte</p>
                </div>
            </div>
        </div>
    </main>

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
