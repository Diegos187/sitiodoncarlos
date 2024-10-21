<?php
include('../conexion.php');

$id_form = $_GET['id_form'];

// Obtener los mensajes relacionados con la cita
$query = "SELECT m.mensaje, m.fecha_envio, u.nombre, u.cargo, m.leido 
          FROM Mensajes m 
          JOIN login u ON m.id_usuario = u.id 
          WHERE m.id_form = ? 
          ORDER BY m.fecha_envio ASC";
$stmt = $conex->prepare($query);
$stmt->bind_param("i", $id_form);
$stmt->execute();
$result = $stmt->get_result();

$mensajes = [];
while ($row = $result->fetch_assoc()) {
    $mensajes[] = [
        'mensaje' => $row['mensaje'],
        'nombre' => $row['nombre'],
        'esAdmin' => $row['cargo'] === 'administrador',
        'leido' => $row['leido']  // Añadir estado de lectura
    ];
}

echo json_encode(['mensajes' => $mensajes]);
?>
