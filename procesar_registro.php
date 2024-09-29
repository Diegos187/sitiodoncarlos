<?php
include('conexion.php');

// Obtener los datos del formulario
$rut = $_POST['rut'];
$nombre = $_POST['nombre'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Encriptar la contraseña
$cargo = '2';  // Administrador
$token_verificacion = bin2hex(random_bytes(50));  // Generar un token único
$token_expiracion = date('Y-m-d H:i:s', strtotime('+1 hour'));  // Expiración del token (1 hora)

// Verificar si el RUT o el correo ya existen en la base de datos
$query_verificar = "SELECT * FROM login WHERE email = ? OR rut = ?";
$stmt = $conex->prepare($query_verificar);
$stmt->bind_param('ss', $email, $rut);
$stmt->execute();
$result = $stmt->get_result();

session_start();

if ($result->num_rows > 0) {
    // Si el RUT o el correo ya existen, redirigir de nuevo al formulario con un mensaje de error
    $_SESSION['error'] = "El correo electrónico o el RUT ya están registrados. Por favor, intenta con otro.";
    header('Location: registro.php');
    exit();
} else {
    // Si no existen duplicados, insertar los datos en la tabla 'login'
    $query = "INSERT INTO login (rut, nombre, email, password, cargo, token_verificacion, token_expiracion) 
              VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt_insert = $conex->prepare($query);
    $stmt_insert->bind_param('sssssss', $rut, $nombre, $email, $password, $cargo, $token_verificacion, $token_expiracion);

    if ($stmt_insert->execute()) {
        // Enviar correo de verificación
        $subject = "Verificación de correo electrónico";
        $body = "Hola $nombre,\n\nHaz clic en el siguiente enlace para verificar tu cuenta:\n";
        $body .= "http://localhost/doncarlos/verificar.php?token=$token_verificacion\n\n";
        $body .= "Este enlace expirará en 1 hora.\n\nSaludos,\nCentro Técnico DC";
        $headers = "From: diegomarin939@gmail.com";

        if (mail($email, $subject, $body, $headers)) {
            // Almacenar mensaje de éxito en sesión
            $_SESSION['success'] = "Por favor, revisa tu correo para verificar tu cuenta.";
        } else {
            $_SESSION['error'] = "Error al enviar el correo de verificación.";
        }
    } else {
        $_SESSION['error'] = "Error al registrar al usuario.";
    }

    header('Location: registro.php');
    exit();
}

$stmt->close();
$stmt_insert->close();
mysqli_close($conex);
?>
