<?php
session_start();



// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirigir al login si no hay sesión
    exit();
}

// Verificar que el usuario sea un cliente
if ($_SESSION['user_cargo'] !== 'cliente') {
    // Si es un administrador, redirigir al dashboard de administrador
    header('Location: admin_dashboard.php');
    exit();
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Cliente</title>
</head>
<body>
    <h1>Bienvenido, cliente <?php echo $_SESSION['user_name']; ?></h1>
    <p>Este es tu panel de control.</p>
    <a href="logout.php">Cerrar sesión</a>
</body>
</html>
