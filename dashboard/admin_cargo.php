<?php
session_start();
include('../conexion.php');

// Verificar si el usuario está logueado como administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_cargo'] !== 'administrador') {
    echo json_encode(['success' => false, 'message' => 'No ha iniciado sesión como administrador.']);
    exit();
}

// Verificar si se recibieron los datos necesarios
if (isset($_POST['userId']) && isset($_POST['password']) && isset($_POST['accion'])) {
    $userId = $_POST['userId'];  // ID del usuario cuyo cargo cambiará
    $password = $_POST['password'];  // Contraseña del administrador actual
    $adminId = $_SESSION['user_id'];  // ID del administrador actualmente logueado
    $accion = $_POST['accion'];

    // Obtener la contraseña actual del administrador que está realizando la acción
    $query = "SELECT password FROM login WHERE id = ?";
    $stmt = $conex->prepare($query);
    $stmt->bind_param("i", $adminId);  // Aquí usamos el ID del administrador logueado
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();

    // Verificar si la contraseña ingresada coincide con la del administrador logueado
    if (password_verify($password, $admin['password'])) {
        if ($accion === 'promover') {
            // Cambiar el cargo del cliente a administrador
            $query = "UPDATE login SET cargo = 'administrador' WHERE id = ?";
            $stmt = $conex->prepare($query);
            $stmt->bind_param("i", $userId);  // Cambiamos el cargo del usuario objetivo
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'El usuario ha sido promovido a administrador.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar el cargo.']);
            }
        } elseif ($accion === 'degradar') {
            // Cambiar el cargo del administrador a cliente
            $query = "UPDATE login SET cargo = 'cliente' WHERE id = ?";
            $stmt = $conex->prepare($query);
            $stmt->bind_param("i", $userId);  // Cambiamos el cargo del usuario objetivo
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'El usuario ha sido degradado a cliente.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar el cargo.']);
            }
        }
    } else {
        // La contraseña ingresada es incorrecta
        echo json_encode(['success' => false, 'message' => 'La contraseña ingresada no es correcta.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Faltan datos para realizar la operación.']);
}
?>
