<?php
include('../conexion.php');

session_start();
$user_cargo = $_SESSION['user_cargo'];  // Obtener el rol del usuario logueado

// Consultar todos los formularios y verificar si hay mensajes no leídos según el rol del usuario
$query = "
    SELECT f.id_form, 
           (SELECT COUNT(*) FROM Mensajes 
            WHERE id_form = f.id_form 
              AND leido = 0 
              AND id_usuario IN (
                  SELECT id FROM login WHERE cargo = " . ($user_cargo === 'cliente' ? "'administrador'" : "'cliente'") . "
              )
           ) AS mensajes_no_leidos
    FROM Formulario f
";

$result = $conex->query($query);

$formularios = [];
while ($row = $result->fetch_assoc()) {
    $formularios[] = [
        'id_form' => $row['id_form'],
        'mensajes_no_leidos' => $row['mensajes_no_leidos']
    ];
}

echo json_encode(['formularios' => $formularios]);
?>
