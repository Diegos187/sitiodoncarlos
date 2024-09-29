<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña</title>
    <link rel="stylesheet" href="login.css"> <!-- Incluir el CSS existente -->
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


    <form action="procesar_recuperar.php" method="POST">
        <h2>Recuperar tu contraseña</h2>
        
        <label for="email">Ingresa tu correo electrónico:</label>
        <input type="email" name="email" id="email" placeholder="Ingresa tu correo" required>

        <button type="submit">Enviar enlace de recuperación</button>
        <button onclick="window.location.href='login.php'" class="volver-btn">Volver</button> <!-- Botón Volver -->
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
