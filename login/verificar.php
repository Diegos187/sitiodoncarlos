<?php
include('../conexion.php');
session_start(); // Iniciar sesión para manejar los mensajes flotantes

// Obtener el token de la URL
$token = $_GET['token'];

// Buscar el usuario con el token
$query = "SELECT * FROM login WHERE token_verificacion = '$token' AND token_expiracion > NOW() AND verificado = 0";
$result = mysqli_query($conex, $query);

if (mysqli_num_rows($result) == 1) {
    // Actualizar el estado de verificación
    $updateQuery = "UPDATE login SET verificado = 1, token_verificacion = NULL, token_expiracion = NULL WHERE token_verificacion = '$token'";
    if (mysqli_query($conex, $updateQuery)) {
        // Configurar un mensaje de éxito en la sesión
        $_SESSION['success'] = "Tu cuenta ha sido verificada exitosamente. Ahora puedes iniciar sesión.";
        // Redirigir a la página de login
        header('Location: ./login.php');
        exit();
    } else {
        echo "Error al verificar la cuenta.";
    }
} else {
    echo "El enlace de verificación es inválido o ha expirado.";
}

mysqli_close($conex);
?>
