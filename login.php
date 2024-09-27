<?php
session_start();

// Verificar si ya hay una sesión activa
if (isset($_SESSION['user_id'])) {
    // Si ya está logueado, redirigir al dashboard
    header('Location: dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="login.css">
    <style>
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

        /* Estilo para el mensaje flotante de error */
        .mensaje-error {
            background-color: #f44336;
        }
    </style>
</head>
<body>

    <!-- Mensaje flotante -->
    <div id="mensajeFlotante" class="mensaje-flotante"></div>

    <form method="POST" action="procesar_login.php">
        <h2>Iniciar Sesión</h2>

        <label for="email">Correo Electrónico:</label>
        <input type="email" name="email" id="email" placeholder="Email" required>

        <label for="password">Contraseña:</label>
        <input type="password" name="password" id="password" placeholder="Password" required>

        <div class="remember-me">
            <label><input type="checkbox" name="remember"> Recuérdame</label>
            <a href="recuperar_contraseña.php">¿Olvidaste tu contraseña?</a>
        </div>

        <button type="submit">Iniciar Sesión</button>

        <p style="color: white;">¿No tienes una cuenta? <a href="registro.php">Regístrate</a></p>
    </form>

    <script>
        // Mostrar el mensaje flotante si hay un mensaje de éxito en la sesión
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
