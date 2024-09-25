<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    // Si no está logueado, redirigir al login
    header('Location: login.php');
    exit();
}

// Si está logueado, mostrar el dashboard
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
</head>
<body>
    <h1>Bienvenido, <?php echo $_SESSION['user_name']; ?></h1>
    <p>Este es tu panel de control.</p>
    <a href="logout.php">Cerrar sesión</a>
</body>
</html>
