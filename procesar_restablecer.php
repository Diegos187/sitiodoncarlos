<?php
// Conectar a la base de datos
include('conexion.php');
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        die("Las contraseñas no coinciden.");
    }

    // Verificar si el token es válido y no ha expirado
    $sql = "SELECT * FROM password_resets WHERE token = ? AND expira > NOW()";
    $stmt = $conex->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $row = $resultado->fetch_assoc();
        $email = $row['email'];

        // Actualizar la contraseña
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE login SET password = ? WHERE email = ?";
        $stmt = $conex->prepare($sql);
        $stmt->bind_param("ss", $password_hash, $email);
        $stmt->execute();

        // Eliminar el token
        $sql = "DELETE FROM password_resets WHERE email = ?";
        $stmt = $conex->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();

        // Redirigir al login con mensaje de éxito
        $_SESSION['success'] = "Tu contraseña ha sido restablecida exitosamente. Ahora puedes iniciar sesión.";
        header("Location: login.php");
        exit();
    } else {
        echo "El enlace de restablecimiento es inválido o ha expirado.";
    }
}
?>
