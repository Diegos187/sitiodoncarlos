<?php
session_start();
include('../conexion.php');

// Verificar si el usuario está logueado y es un administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_cargo'] !== 'administrador') {
    echo json_encode(['success' => false, 'message' => 'No tiene permisos para acceder a esta información.']);
    exit();
}

// Obtener las últimas 5 citas desde la tabla Formulario
$query = "SELECT id_form FROM Formulario ORDER BY id_form DESC LIMIT 5";
$result = mysqli_query($conex, $query);
$citas = [];

while ($row = mysqli_fetch_assoc($result)) {
    $citas[] = "Aviso de nueva cita: Cita #" . $row['id_form'];
}

// Enviar respuesta JSON
echo json_encode([
    'success' => true,
    'notificaciones' => $citas
]);
?>
