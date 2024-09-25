<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña</title>
</head>
<body>
    <h2>Recuperar tu contraseña</h2>
    <form action="procesar_recuperar.php" method="POST">
        <label for="email">Ingresa tu correo electrónico:</label>
        <input type="email" name="email" required>
        <button type="submit">Enviar enlace de recuperación</button>
    </form>
</body>
</html>
