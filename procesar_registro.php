<?php
include('conexion.php');

// Obtener los datos del formulario
$rut = $_POST['rut'];
$nombre = $_POST['nombre'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Encriptar la contraseña
$cargo = '1';  // Administrador
$token_verificacion = bin2hex(random_bytes(50));  // Generar un token único
$token_expiracion = date('Y-m-d H:i:s', strtotime('+1 hour'));  // Expiración del token (1 hora)

// Insertar los datos en la tabla 'login'
$query = "INSERT INTO login (rut, nombre, email, password, cargo, token_verificacion, token_expiracion) 
          VALUES ('$rut', '$nombre', '$email', '$password', '$cargo', '$token_verificacion', '$token_expiracion')";

if (mysqli_query($conex, $query)) {
    // Enviar correo de verificación
    $subject = "Verificación de correo electrónico";
    $body = "Hola $nombre,\n\nHaz clic en el siguiente enlace para verificar tu cuenta:\n";
    $body .= "http://localhost/doncarlos/verificar.php?token=$token_verificacion\n\n";
    $body .= "Este enlace expirará en 1 hora.\n\nSaludos,\nCentro Técnico DC";
    $headers = "From: diegomarin939@gmail.com";

    if (mail($email, $subject, $body, $headers)) {
        echo "Por favor, revisa tu correo para verificar tu cuenta.";
    } else {
        echo "Error al enviar el correo de verificación.";
    }
} else {
    echo "Error al registrar al usuario.";
}

mysqli_close($conex);
?>
