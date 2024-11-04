<?php
session_start();
include('../conexion.php');

if ($_SESSION['user_cargo'] !== 'administrador' || !isset($_POST['password'])) {
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado.']);
    exit();
}

// Recibimos los datos
$password = $_POST['password'];
$clienteId = $_POST['clienteId'];
$rutCliente = $_POST['rutCliente'];

// Validación de contraseña
$stmt = $conex->prepare("SELECT password FROM login WHERE id = ?");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($hashedPassword);
$stmt->fetch();
$stmt->close();

if (!password_verify($password, $hashedPassword)) {
    echo json_encode(['success' => false, 'message' => 'Contraseña incorrecta.']);
    exit();
}

// Obtener id_form de citas asociadas
$query_id_forms = "SELECT id_form FROM Citas WHERE rut_cliente = ?";
$stmt = $conex->prepare($query_id_forms);
$stmt->bind_param('s', $rutCliente);
$stmt->execute();
$result = $stmt->get_result();

$id_forms = [];
while ($row = $result->fetch_assoc()) {
    $id_forms[] = $row['id_form'];
}
$stmt->close();

// Eliminar mensajes
if (!empty($id_forms)) {
    $placeholders = implode(',', array_fill(0, count($id_forms), '?'));
    $query_delete_messages = "DELETE FROM Mensajes WHERE id_form IN ($placeholders)";
    $stmt = $conex->prepare($query_delete_messages);
    $stmt->bind_param(str_repeat('i', count($id_forms)), ...$id_forms);
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Error eliminando mensajes: ' . $stmt->error]);
        exit();
    }
    $stmt->close();
}

// Eliminar en citas
$stmt = $conex->prepare("DELETE FROM Citas WHERE rut_cliente = ?");
$stmt->bind_param('s', $rutCliente);
if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Error eliminando citas: ' . $stmt->error]);
    exit();
}
$stmt->close();

// Eliminar en login
$stmt = $conex->prepare("DELETE FROM login WHERE id = ?");
$stmt->bind_param('i', $clienteId);
if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Error eliminando cliente: ' . $stmt->error]);
    exit();
}
$stmt->close();

echo json_encode(['success' => true, 'message' => 'Cliente eliminado exitosamente.']);
?>
