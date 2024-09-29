<?php
session_start();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="login.css">
    <style>
        .error {
            color: red;
        }

        /* Estilos para el mensaje flotante */
        .mensaje-flotante {
            display: none;
            position: fixed;
            top: 10%;
            left: 50%;
            transform: translateX(-50%);
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .mensaje-error {
            background-color: #f44336;
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

    
    <!-- Mensaje flotante -->
    <div id="mensajeFlotante" class="mensaje-flotante"></div>
    
    
    <form action="procesar_registro.php" method="POST" onsubmit="return validarFormulario()">
        <h2>Formulario de Registro</h2>

        <!-- Mostrar mensaje de error si ya existe un correo o RUT registrado -->
        <?php
        if (isset($_SESSION['error'])) {
            echo "<p class='error'>" . $_SESSION['error'] . "</p>";
            unset($_SESSION['error']);  // Eliminar el mensaje de error después de mostrarlo
        }
        ?>

        <label for="rut">RUT (Sin puntos y con guión):</label>
        <input type="text" name="rut" id="rut" placeholder="12345678-9" required>
        <p id="rutError" class="error" style="display: none;">Por favor, ingresa un RUT válido (ej: 12345678-9).</p>

        <label for="nombre">Nombre de usuario:</label>
        <input type="text" name="nombre" id="nombre" placeholder="Ingresa tu nombre" required>

        <label for="email">Correo:</label>
        <input type="email" name="email" id="email" placeholder="Ingresa tu correo" required>
        <p id="emailError" class="error" style="display: none;">Por favor, ingresa un correo válido.</p>

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

        <p id="passwordError" class="error" style="display: none;">Las contraseñas no coinciden. Por favor, verifica.</p>

        <button type="submit">Registrarse</button>
        <button onclick="window.location.href='login.php'" class="volver-btn">Volver</button> <!-- Botón Volver -->
    </form>

    <script>
        // Función para mostrar/ocultar contraseña
        function togglePasswordVisibility(id) {
            var passwordInput = document.getElementById(id);
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
            } else {
                passwordInput.type = "password";
            }
        }

        // Validar que las contraseñas coincidan antes de enviar el formulario
        function validarFormulario() {
            var rut = document.getElementById("rut").value;
            var email = document.getElementById("email").value;
            var password = document.getElementById("password").value;
            var confirmPassword = document.getElementById("confirm_password").value;
            var rutError = document.getElementById("rutError");
            var emailError = document.getElementById("emailError");
            var passwordError = document.getElementById("passwordError");

            var rutRegex = /^\d{7,8}-[0-9kK]$/;  // Formato RUT válido: 12345678-9 o 1234567-8
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;  // Formato de correo válido

            // Validar RUT
            if (!rutRegex.test(rut)) {
                rutError.style.display = "block";
                return false;  // Detener el envío del formulario
            } else {
                rutError.style.display = "none";
            }

            // Validar correo
            if (!emailRegex.test(email)) {
                emailError.style.display = "block";
                return false;  // Detener el envío del formulario
            } else {
                emailError.style.display = "none";
            }

            // Validar que las contraseñas coincidan
            if (password !== confirmPassword) {
                passwordError.style.display = "block";
                return false;  // No enviar el formulario
            } else {
                passwordError.style.display = "none";
            }

            return true;  // Si todo es válido, enviar el formulario
        }

        // Mostrar el mensaje flotante si hay un mensaje de éxito en sesión
        <?php if (isset($_SESSION['success'])): ?>
            var mensaje = "<?php echo $_SESSION['success']; ?>";
            var mensajeFlotante = document.getElementById('mensajeFlotante');
            mensajeFlotante.innerText = mensaje;
            mensajeFlotante.style.display = 'block';

            // Ocultar el mensaje después de 5 segundos
            setTimeout(function() {
                mensajeFlotante.style.display = 'none';
            }, 5000);

            <?php unset($_SESSION['success']); // Limpiar la sesión ?>
        <?php endif; ?>
    </script>
</body>
</html>
