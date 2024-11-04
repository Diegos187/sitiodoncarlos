<?php
session_start();
include('../conexion.php');

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id']) || $_SESSION['user_cargo'] !== 'cliente') {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

$rut_cliente = $_SESSION['user_rut'];
$query_citas = "
    SELECT f.*, c.rut_cliente, s.tipo_servicio, p.tipo_producto, h.fecha, h.hora_disponible, pr.monto, pr.comentario, pr.estado AS estado_presupuesto, pr.fecha_creacion
    FROM Formulario f
    INNER JOIN Citas c ON f.id_form = c.id_form
    INNER JOIN Servicio s ON f.id_servicio = s.id_servicio
    INNER JOIN Producto p ON f.id_producto = p.id_producto
    INNER JOIN Horario h ON f.id_horario = h.id_horario
    LEFT JOIN Presupuesto pr ON f.id_form = pr.id_form AND pr.fecha_creacion = (
        SELECT MAX(fecha_creacion) FROM Presupuesto WHERE id_form = f.id_form
    )
    WHERE c.rut_cliente = ?
    ORDER BY f.id_form DESC
";

$stmt = $conex->prepare($query_citas);
$stmt->bind_param('s', $rut_cliente);
$stmt->execute();
$result_citas = $stmt->get_result();

$citas = [];
while ($cita = $result_citas->fetch_assoc()) {
    $citas[] = $cita;
}

echo json_encode(['success' => true, 'citas' => $citas]);
?>
