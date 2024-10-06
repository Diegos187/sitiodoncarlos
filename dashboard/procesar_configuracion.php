<?php
session_start();
include('../conexion.php');

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No ha iniciado sesión.']);
    exit();
}

// Obtener los datos del formulario
$nuevo_nombre = isset($_POST['nuevo_nombre']) ? trim($_POST['nuevo_nombre']) : null;
$nuevo_correo = isset($_POST['nuevo_correo']) ? trim($_POST['nuevo_correo']) : null;
$confirmar_correo = isset($_POST['confirmar_correo']) ? trim($_POST['confirmar_correo']) : null;
$password_actual = isset($_POST['password_actual']) ? trim($_POST['password_actual']) : null;
$nueva_password = isset($_POST['nueva_password']) ? trim($_POST['nueva_password']) : null;
$user_id = $_SESSION['user_id'];

// Inicializar variables para la consulta dinámica
$campos_a_actualizar = [];
$parametros = [];
$tipos_parametros = "";

// Obtener el password actual del usuario
$query = "SELECT nombre, password, email FROM login WHERE id = ?";
$stmt = $conex->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

if (!$usuario) {
    echo json_encode(['success' => false, 'message' => 'Usuario no encontrado.']);
    exit();
}

// Verificar si el usuario quiere cambiar el nombre de usuario
if (!empty($nuevo_nombre)) {
    $campos_a_actualizar[] = "nombre = ?";
    $parametros[] = $nuevo_nombre;
    $tipos_parametros .= "s";
}

// Verificar si el usuario quiere cambiar el correo electrónico
if (!empty($nuevo_correo)) {
    // Verificar si los correos ingresados coinciden
    if ($nuevo_correo !== $confirmar_correo) {
        echo json_encode(['success' => false, 'message' => 'Los correos electrónicos no coinciden.']);
        exit();
    }

    // Verificar si el correo ya está registrado en otro usuario
    $query_check_email = "SELECT id FROM login WHERE email = ?";
    $stmt_check_email = $conex->prepare($query_check_email);
    $stmt_check_email->bind_param('s', $nuevo_correo);
    $stmt_check_email->execute();
    $stmt_check_email->store_result();

    if ($stmt_check_email->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'El correo ingresado ya está registrado.']);
        exit();
    } else {
        $campos_a_actualizar[] = "email = ?";
        $parametros[] = $nuevo_correo;
        $tipos_parametros .= "s";
    }
    $stmt_check_email->close();
}

// Verificar si el usuario quiere cambiar la contraseña
if (!empty($nueva_password)) {
    // Validar que la contraseña anterior esté ingresada
    if (empty($password_actual)) {
        echo json_encode(['success' => false, 'message' => 'Debe ingresar su contraseña actual para cambiar la contraseña.']);
        exit();
    }

    // Verificar que la contraseña anterior sea correcta
    if (!password_verify($password_actual, $usuario['password'])) {
        echo json_encode(['success' => false, 'message' => 'La contraseña actual no es correcta.']);
        exit();
    }

    // Si la contraseña anterior es correcta, procedemos a cambiarla
    $password_hash = password_hash($nueva_password, PASSWORD_DEFAULT);
    $campos_a_actualizar[] = "password = ?";
    $parametros[] = $password_hash;
    $tipos_parametros .= "s";
}

// Si hay al menos un campo que actualizar
if (count($campos_a_actualizar) > 0) {
    // Construir la consulta SQL dinámicamente
    $query = "UPDATE login SET " . implode(", ", $campos_a_actualizar) . " WHERE id = ?";
    $parametros[] = $user_id;
    $tipos_parametros .= "i"; // Agregar el tipo de parámetro para 'id' (entero)

    $stmt = $conex->prepare($query);
    $stmt->bind_param($tipos_parametros, ...$parametros);

    if ($stmt->execute()) {
        // Actualizar la información en la sesión si fue cambiado
        if (!empty($nuevo_nombre)) {
            $_SESSION['user_name'] = $nuevo_nombre;
        }
        if (!empty($nuevo_correo)) {
            $_SESSION['user_email'] = $nuevo_correo;
        }
        echo json_encode(['success' => true, 'message' => 'Datos actualizados correctamente. Recargando página...']);	
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar los datos.']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'No se ha proporcionado ningún dato para actualizar.']);
}

$conex->close();
?>
