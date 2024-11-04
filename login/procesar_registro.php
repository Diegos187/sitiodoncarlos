<?php
include('../conexion.php');
session_start();

// Función de validación de RUT en PHP
function validarRUT($rut) {
    // Verificar que el formato sea correcto
    if (!preg_match('/^[0-9]{7,8}-[0-9kK]{1}$/', $rut)) {
        return false;
    }

    // Separar el número y el dígito verificador
    list($numero, $digitoVerificador) = explode('-', $rut);

    // Calcular el dígito verificador esperado
    $suma = 0;
    $multiplicador = 2;
    for ($i = strlen($numero) - 1; $i >= 0; $i--) {
        $suma += intval($numero[$i]) * $multiplicador;
        $multiplicador = $multiplicador < 7 ? $multiplicador + 1 : 2;
    }

    $resto = $suma % 11;
    $dvCalculado = 11 - $resto;
    $dvCalculado = $dvCalculado == 10 ? 'K' : ($dvCalculado == 11 ? '0' : (string) $dvCalculado);

    return strtoupper($digitoVerificador) === $dvCalculado;
}

// Sanitización y validación de RUT
$rut = isset($_POST['rut']) ? trim($_POST['rut']) : '';
if (!validarRUT($rut)) {
    $_SESSION['error'] = "RUT inválido. Debe ser sin puntos y con guión (ej: 12345678-9).";
    header('Location: ./registro.php');
    exit();
}

// Sanitización y validación del nombre
$nombre = isset($_POST['nombre']) ? htmlspecialchars(trim($_POST['nombre'])) : '';
if (empty($nombre) || strlen($nombre) > 50) {
    $_SESSION['error'] = "Nombre inválido o demasiado largo.";
    header('Location: ./registro.php');
    exit();
}

// Validación y sanitización de email
$email = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : '';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "Correo electrónico inválido.";
    header('Location: ./registro.php');
    exit();
}

// Validación de la contraseña
if (empty($_POST['password']) || strlen($_POST['password']) < 8) {
    $_SESSION['error'] = "La contraseña debe tener al menos 8 caracteres.";
    header('Location: ./registro.php');
    exit();
}
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

// Cargo fijo como 'cliente' para este formulario
$cargo = 'cliente';

// Generación de token de verificación y su expiración
$token_verificacion = bin2hex(random_bytes(50));
$token_expiracion = date('Y-m-d H:i:s', strtotime('+1 hour'));

// Verificar si el RUT o el correo ya existen en la base de datos
$query_verificar = "SELECT * FROM login WHERE email = ? OR rut = ?";
$stmt = $conex->prepare($query_verificar);
$stmt->bind_param('ss', $email, $rut);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Si el RUT o el correo ya existen, redirigir con un mensaje de error
    $_SESSION['error'] = "El correo o el RUT ya están registrados. Intenta con otro.";
    header('Location: ./registro.php');
    exit();
} else {
    // Insertar los datos si no hay duplicados
    $query = "INSERT INTO login (rut, nombre, email, password, cargo, token_verificacion, token_expiracion) 
              VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt_insert = $conex->prepare($query);
    $stmt_insert->bind_param('sssssss', $rut, $nombre, $email, $password, $cargo, $token_verificacion, $token_expiracion);

    if ($stmt_insert->execute()) {
        // Enviar correo de verificación
        $verificationLink = "https://7dfa-190-100-89-42.ngrok-free.app/doncarlos/login/verificar.php?token=$token_verificacion";
        $asunto = "Verificación de correo electrónico";
        $mensaje = "
            <h3>Hola, $nombre</h3>
            <p>Gracias por registrarte en nuestro sistema. Por favor, haz clic en el enlace a continuación para verificar tu cuenta:</p>
            <p><a href='$verificationLink'>$verificationLink</a></p>
            <p><em>Este enlace expirará en 1 hora.</em></p>
            <p>Gracias por utilizar nuestro servicio.<br>Centro Técnico DC</p>
        ";

        // Configurar el encabezado del correo
        $headers = "From: Servicio Técnico <no-reply@doncarlos.com>\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";

        if (mail($email, $asunto, $mensaje, $headers)) {
            $_SESSION['success'] = "Revisa tu correo para verificar tu cuenta. Si no lo encuentras, revisa en spam.";

            // Asociar citas previas registradas con el mismo RUT si es necesario
            $query_citas = "SELECT id_form FROM Formulario WHERE rut = ? AND id_form NOT IN (SELECT id_form FROM Citas)";
            $stmt_citas = $conex->prepare($query_citas);
            $stmt_citas->bind_param('s', $rut);
            $stmt_citas->execute();
            $result_citas = $stmt_citas->get_result();

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
