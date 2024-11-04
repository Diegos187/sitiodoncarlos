<?php
session_start();

// Verificar si el usuario está logueado y es un administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_cargo'] !== 'administrador') {
    echo json_encode(["success" => false, "message" => "No autorizado"]);
    exit();
}

include('../conexion.php');

$query = "
    SELECT f.*, pr.monto, pr.comentario, pr.estado AS estado_presupuesto, h.fecha, h.hora_disponible, c.rut_cliente, s.tipo_servicio, p.tipo_producto,
           CASE 
               WHEN c.id_form IS NOT NULL THEN 'registrado' 
               ELSE 'no_registrado' 
           END AS usuario_estado,
           (SELECT COUNT(*) FROM Mensajes WHERE id_form = f.id_form AND leido = 0 AND id_usuario IN 
               (SELECT id FROM login WHERE cargo = 'cliente')) AS mensajes_no_leidos
    FROM Formulario f
    LEFT JOIN Horario h ON f.id_horario = h.id_horario
    LEFT JOIN Citas c ON f.id_form = c.id_form
    LEFT JOIN Servicio s ON f.id_servicio = s.id_servicio 
    LEFT JOIN Producto p ON f.id_producto = p.id_producto  
    LEFT JOIN (
        SELECT id_form, monto, comentario, estado 
        FROM Presupuesto
        WHERE fecha_creacion = (
            SELECT MAX(fecha_creacion) 
            FROM Presupuesto AS p2 
            WHERE p2.id_form = Presupuesto.id_form
        )
    ) pr ON f.id_form = pr.id_form
     ORDER BY f.id_form DESC
";

$result = $conex->query($query);
$citas = [];

while ($row = $result->fetch_assoc()) {
    $citas[] = $row;
}

echo json_encode(["success" => true, "citas" => $citas]);
?>
