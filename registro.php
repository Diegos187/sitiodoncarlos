<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <form action="procesar_registro.php" method="POST">
        <h2>Formulario de Registro</h2>

        <label for="rut">RUT:</label>
        <input type="text" name="rut" id="rut" placeholder="Ingresa tu RUT" required>

        <label for="nombre">Nombre:</label>
        <input type="text" name="nombre" id="nombre" placeholder="Ingresa tu nombre" required>

        <label for="email">Correo:</label>
        <input type="email" name="email" id="email" placeholder="Ingresa tu correo" required>

        <label for="password">Contraseña:</label>
        <input type="password" name="password" id="password" placeholder="Crea una contraseña" required>

        <button type="submit">Registrarse</button>
    </form>
</body>
</html>
