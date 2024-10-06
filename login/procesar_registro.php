<?php
include('../conexion.php');

// Obtener los datos del formulario
$rut = $_POST['rut'];
$nombre = $_POST['nombre'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Encriptar la contraseña
$cargo = 'cliente';  // Definir el cargo como cliente
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
    header('Location: ./registro.php');
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
        $body .= "http://localhost/doncarlos/login/verificar.php?token=$token_verificacion\n\n";
        $body .= "Este enlace expirará en 1 hora.\n\nSaludos,\nCentro Técnico DC";
        $headers = "From: diegomarin939@gmail.com";

        if (mail($email, $subject, $body, $headers)) {
            // Almacenar mensaje de éxito en sesión
            $_SESSION['success'] = "Por favor, revisa tu correo para verificar tu cuenta.";
            
            // Buscar citas realizadas sin estar registrado con el mismo RUT
            $query_citas = "SELECT id_form FROM Formulario WHERE rut = ? AND id_form NOT IN (SELECT id_form FROM Citas)";
            $stmt_citas = $conex->prepare($query_citas);
            $stmt_citas->bind_param('s', $rut);
            $stmt_citas->execute();
            $result_citas = $stmt_citas->get_result();

            // Asociar las citas encontradas al usuario
            while ($cita = $result_citas->fetch_assoc()) {
                $id_form = $cita['id_form'];
                $query_insert_cita = "INSERT INTO Citas (id_form, rut_cliente) VALUES (?, ?)";
                $stmt_insert_cita = $conex->prepare($query_insert_cita);
                $stmt_insert_cita->bind_param('is', $id_form, $rut);
                $stmt_insert_cita->execute();
            }

        } else {
            $_SESSION['error'] = "Error al enviar el correo de verificación.";
        }
    } else {
        $_SESSION['error'] = "Error al registrar al usuario.";
    }

    header('Location: ./registro.php');
    exit();
}

$stmt->close();
$stmt_insert->close();
mysqli_close($conex);
?>