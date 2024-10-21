<?php
session_start();
include('../conexion.php');

if (isset($_POST['id_form']) && isset($_POST['mensaje'])) {
    $id_form = $_POST['id_form'];
    $mensaje = $_POST['mensaje'];
    $id_usuario = $_SESSION['user_id'];  // ID del usuario logueado (cliente o administrador)

    // Insertar el nuevo mensaje en la base de datos
    $query = "INSERT INTO Mensajes (id_form, id_usuario, mensaje) VALUES (?, ?, ?)";
    $stmt = $conex->prepare($query);
    $stmt->bind_param('iis', $id_form, $id_usuario, $mensaje);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Mensaje enviado.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar el mensaje.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
}
?>
