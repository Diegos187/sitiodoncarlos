<?php
// Incluir el archivo de conexión a la base de datos
include('../conexion.php');
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Verificar si el correo está registrado
    $sql = "SELECT * FROM login WHERE email = ?";
    $stmt = $conex->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        // Generar un token único
        $token = bin2hex(random_bytes(50));
        $expira = date("Y-m-d H:i:s", strtotime('+1 hour'));

        // Insertar el token en la tabla de restablecimiento
        $sql = "INSERT INTO password_resets (email, token, expira) VALUES (?, ?, ?)";
        $stmt = $conex->prepare($sql);
        $stmt->bind_param("sss", $email, $token, $expira);
        $stmt->execute();

        // Configurar el enlace de restablecimiento de contraseña
        $resetLink = "https://7dfa-190-100-89-42.ngrok-free.app/doncarlos/login/restablecer_contraseña.php?token=$token";
        $asunto = "Recuperación de Contraseña";
        $mensaje = "
            <h3>Hola,</h3>
            <p>Hemos recibido una solicitud para restablecer tu contraseña. Haz clic en el enlace a continuación para restablecer tu contraseña:</p>
            <p><a href='$resetLink'>$resetLink</a></p>
            <p><em>Este enlace expirará en 1 hora.</em></p>
            <p>Si no solicitaste el cambio de contraseña, puedes ignorar este mensaje.</p>
            <p>Gracias,<br>Equipo de Soporte de Centro Técnico DC</p>
        ";

        // Configurar el encabezado del correo
        $headers = "From: Servicio Técnico <no-reply@doncarlos.com>\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";

        // Enviar el correo de notificación
        if (mail($email, $asunto, $mensaje, $headers)) {
            $_SESSION['success'] = "Si el correo está registrado, te enviaremos un enlace de recuperación.";
        } else {
            $_SESSION['error'] = "Hubo un error al enviar el enlace de recuperación.";
        }
    } else {
        $_SESSION['success'] = "Si el correo está registrado, te enviaremos un enlace de recuperación.";
    }

    header("Location: ./recuperar_contraseña.php");
    exit();
}
?>
