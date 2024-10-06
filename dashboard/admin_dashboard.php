<?php
session_start();



// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    // Si no está logueado, redirigir al login
    header('Location: ../login/login.php');
    exit();
}

// Verificar que el usuario sea un cliente
if ($_SESSION['user_cargo'] !== 'administrador') {
    // Si es un administrador, redirigir al dashboard de administrador
    header('Location: ./dashboard.php');
    exit();
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
</head>
<body>
    <h1>Bienvenido, admin  <?php echo $_SESSION['user_name']; ?></h1>
    <p>Este es tu panel de control.</p>
    <a href="./logout.php">Cerrar sesión</a>

    <script>
        // Redirigir al index.html si el usuario intenta retroceder en el historial
        window.onpopstate = function(event) {
            window.location.href = '../index.html';
        };
    </script>
</body>
</html>
