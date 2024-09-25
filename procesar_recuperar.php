<?php
// Incluir el archivo de conexión a la base de datos
include('conexion.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Verificar si el correo está registrado
    $sql = "SELECT * FROM login WHERE email = ?";
    $stmt = $conex->prepare($sql);  // Aquí usamos la variable $conexion de 'conexion.php'
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

        // Enviar el correo electrónico
        $resetLink = "http://localhost/doncarlos/restablecer_contraseña.php?token=$token";
        $subject = "Recuperación de Contraseña";
        $message = "Hola,\n\nHaz clic en el siguiente enlace para restablecer tu contraseña:\n$resetLink\n\nEste enlace expirará en 1 hora.";
        $headers = "From: no-reply@doncarlos.com";

        if (mail($email, $subject, $message, $headers)) {
            echo "Se ha enviado un enlace de restablecimiento a tu correo.";
        } else {
            echo "Error al enviar el correo.";
        }
    } else {
        echo "No existe una cuenta con ese correo.";
    }
}
?>
