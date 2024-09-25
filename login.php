<?php
session_start();

// Verificar si ya hay una sesión activa
if (isset($_SESSION['user_id'])) {
    // Si ya está logueado, redirigir al dashboard
    header('Location: dashboard.php');
    exit();
}

// Si no hay sesión activa, mostrar el formulario de login
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
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
</body>
</html>
