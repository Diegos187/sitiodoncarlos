<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña</title>
    <style>
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
            z-index: 1000;
        }
        .mensaje-error {
            background-color: #f44336;
        }
    </style>
</head>
<body>
    <h2>Recuperar tu contraseña</h2>
    <form action="procesar_recuperar.php" method="POST">
        <label for="email">Ingresa tu correo electrónico:</label>
        <input type="email" name="email" required>
        <button type="submit">Enviar enlace de recuperación</button>
    </form>

    <div id="mensajeFlotante" class="mensaje-flotante"></div>

    <script>
        // Mostrar el mensaje flotante si hay un mensaje en la sesión
        <?php session_start(); ?>
        <?php if (isset($_SESSION['success'])): ?>
            var mensaje = "<?php echo $_SESSION['success']; ?>";
            var mensajeFlotante = document.getElementById('mensajeFlotante');
            mensajeFlotante.innerText = mensaje;
            mensajeFlotante.style.display = 'block';
            setTimeout(function() {
                mensajeFlotante.style.display = 'none';
            }, 5000);
            <?php unset($_SESSION['success']); ?>
        <?php elseif (isset($_SESSION['error'])): ?>
            var mensaje = "<?php echo $_SESSION['error']; ?>";
            var mensajeFlotante = document.getElementById('mensajeFlotante');
            mensajeFlotante.innerText = mensaje;
            mensajeFlotante.classList.add('mensaje-error');
            mensajeFlotante.style.display = 'block';
            setTimeout(function() {
                mensajeFlotante.style.display = 'none';
            }, 5000);
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
    </script>
</body>
</html>
