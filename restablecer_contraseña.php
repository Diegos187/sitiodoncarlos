<?php
if (isset($_GET['token'])) {
    $token = $_GET['token'];
} else {
    die('Token no válido.');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="login.css">
    <style>
        .error-message {
            color: red;
            display: none;
            margin-top: 5px;
        }
        .show-password {
            cursor: pointer;
            margin-left: 10px;
        }
                /* Estilos para el ícono de ojo */
        .eye-icon {
            cursor: pointer;
            position: absolute;
            right: 10px;
            top: 33%;
            transform: translateY(-50%);
            color: black;
        }

        /* Posición relativa para el contenedor de la contraseña */
        .password-container {
            position: relative;
            width: 100%;
        }
    </style>
</head>
<body>

    <!-- Barra de navegación -->
    <header class="header-agendar">
        <div class="menu container">
            <a href="index.html" class="logo">DC</a>
            <input type="checkbox" id="menu"/>
            <label for="menu">
                <i class="bi bi-list"></i>
            </label>
            <nav class="navbar">
                <ul>
                    <li><a href="index.html">Inicio</a></li>
                    <li><a href="login.php">Iniciar Sesión</a></li>
                    <li><a href="registro.php">Registrarse</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <form action="procesar_restablecer.php" method="POST" onsubmit="return validarFormulario()">
        <h2>Restablecer tu contraseña</h2>
        <input type="hidden" name="token" value="<?php echo $token; ?>">
        
        <label for="password">Contraseña:</label>
        <div class="password-container">
            <input type="password" name="password" id="password" placeholder="Crea una contraseña" required>
            <span class="eye-icon" onclick="togglePasswordVisibility('password')"><i class="bi bi-eye"></i></span>
        </div>

        <label for="confirm-password">Confirmar Contraseña:</label>
        <div class="password-container">
            <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirma tu contraseña" required>
            <span class="eye-icon" onclick="togglePasswordVisibility('confirm_password')"><i class="bi bi-eye"></i></span>
        </div>

        <div class="error-message" id="error-message">Las contraseñas no coinciden.</div>
        
        <button type="submit">Restablecer contraseña</button>
        <button type="button" onclick="window.location.href='login.php'" class="volver-btn">Volver</button>
    </form>

    <script>
        function togglePasswordVisibility(id) {
            var field = document.getElementById(id);
            if (field.type === "password") {
                field.type = "text";
            } else {
                field.type = "password";
            }
        }

        function validarFormulario() {
            var password = document.getElementById('password').value;
            var confirmPassword = document.getElementById('confirm_password').value;
            var errorMessage = document.getElementById('error-message');

            if (password !== confirmPassword) {
                errorMessage.style.display = 'block';
                return false;
            } else {
                errorMessage.style.display = 'none';
                return true;
            }
        }
    </script>
</body>
</html>
