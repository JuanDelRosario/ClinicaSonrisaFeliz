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
                        <button type="button" class="toggle-password">
                            <i class="eye-icon">👁️</i>
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
                    <li>✓ Gestión de Pacientes</li>
                    <li>✓ Programación de Citas</li>
                    <li>✓ Registro de Consultas</li>
                    <li>✓ Control de Facturación</li>
                    <li>✓ Historial Médico</li>
                    <li>✓ Reporte de Ingresos</li>
                </ul>
                <div class="contact-info">
                    <p>📞(829)-282-5216</p>
                    <p>📧 ozunajuan07@clinicasonrisafeliz.com</p>
                    <p>🏥 Villa Mella, Vista Bella - Santo Domingo Norte</p>
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
