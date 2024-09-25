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
</head>
<body>
    <h2>Restablecer tu contraseña</h2>
    <form action="procesar_restablecer.php" method="POST">
        <input type="hidden" name="token" value="<?php echo $token; ?>">
        <label for="password">Nueva contraseña:</label>
        <input type="password" name="password" required>
        <label for="confirm_password">Confirmar nueva contraseña:</label>
        <input type="password" name="confirm_password" required>
        <button type="submit">Restablecer contraseña</button>
    </form>
</body>
</html>
