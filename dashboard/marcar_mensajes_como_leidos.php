<?php
include('../conexion.php');

session_start();
$user_cargo = $_SESSION['user_cargo'];  // Obtener el rol del usuario logueado

$id_form = $_GET['id_form'];

// Marcar como leídos los mensajes del otro tipo de usuario (administrador o cliente)
$query = "UPDATE Mensajes SET leido = 1 WHERE id_form = ? AND id_usuario IN 
          (SELECT id FROM login WHERE cargo = " . ($user_cargo === 'cliente' ? "'administrador'" : "'cliente'") . ") AND leido = 0";
$stmt = $conex->prepare($query);
$stmt->bind_param("i", $id_form);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al marcar los mensajes como leídos.']);
}
?>
