<?php
session_start();
include('../conexion.php');

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No ha iniciado sesión']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verificar si el formulario tiene una cita válida
    if (isset($_POST['id_form']) && isset($_POST['horario'])) {
        $id_form = $_POST['id_form'];
        $nuevo_horario_id = $_POST['horario'];

        // Obtener la cita actual
        $query = "SELECT f.*, h.fecha, h.hora_disponible, s.tipo_servicio, p.tipo_producto 
                  FROM Formulario f 
                  INNER JOIN Servicio s ON f.id_servicio = s.id_servicio 
                  INNER JOIN Producto p ON f.id_producto = p.id_producto
                  INNER JOIN Horario h ON f.id_horario = h.id_horario
                  WHERE f.id_form = ?";
        $stmt = $conex->prepare($query);
        $stmt->bind_param('i', $id_form);
        $stmt->execute();
        $result = $stmt->get_result();
        $cita = $result->fetch_assoc();

        // Verificar si la cita no existe o está cancelada
        if (!$cita || $cita['estado'] == 'cancelado') {
            echo json_encode(['success' => false, 'message' => 'Esta cita no puede ser reagendada.']);
            exit();
        }

        // Obtener el horario actual asociado a la cita
        $horario_anterior_id = $cita['id_horario'];

        // Actualizar el estado del horario anterior a 'disponible'
        $query_update_horario_anterior = "UPDATE Horario SET estado = 'disponible' WHERE id_horario = ?";
        $stmt_update_horario_anterior = $conex->prepare($query_update_horario_anterior);
        $stmt_update_horario_anterior->bind_param('i', $horario_anterior_id);
        $stmt_update_horario_anterior->execute();

        // Actualizar el estado del nuevo horario a 'reservado'
        $query_update_nuevo_horario = "UPDATE Horario SET estado = 'reservado' WHERE id_horario = ?";
        $stmt_update_nuevo_horario = $conex->prepare($query_update_nuevo_horario);
        $stmt_update_nuevo_horario->bind_param('i', $nuevo_horario_id);
        $stmt_update_nuevo_horario->execute();

        // Actualizar el id_horario de la cita en la tabla Formulario
        $query_update_cita = "UPDATE Formulario 
                              SET id_horario = ?, estado = 'pendiente' 
                              WHERE id_form = ?";
        $stmt_update_cita = $conex->prepare($query_update_cita);
        $stmt_update_cita->bind_param('ii', $nuevo_horario_id, $id_form);
        $stmt_update_cita->execute();

        // Obtener los detalles del nuevo horario
        $query_nuevo_horario = "SELECT fecha, hora_disponible FROM Horario WHERE id_horario = ?";
        $stmt_nuevo_horario = $conex->prepare($query_nuevo_horario);
        $stmt_nuevo_horario->bind_param('i', $nuevo_horario_id);
        $stmt_nuevo_horario->execute();
        $result_nuevo_horario = $stmt_nuevo_horario->get_result();
        $nuevo_horario = $result_nuevo_horario->fetch_assoc();

        // Enviar correos de notificación
        $correo_cliente = $cita['correo'];
        $correo_dueno = 'diegomarin939@gmail.com';
        $asunto = "Reagendamiento de Cita - " . $cita['tipo_servicio'];

        $mensaje_cliente = "
            <h3>Estimado/a {$cita['nombre']} {$cita['apellido']}</h3>
            <p>Su cita <strong>#{$cita['id_form']}</strong> ha sido reagendada con éxito. A continuación, los nuevos detalles de la cita:</p>
            <ul>
                <li><strong>Servicio:</strong> {$cita['tipo_servicio']}</li>
                <li><strong>Producto:</strong> {$cita['tipo_producto']}</li>
                <li><strong>Fecha Anterior:</strong> {$cita['fecha']}</li>
                <li><strong>Hora Anterior:</strong> {$cita['hora_disponible']}</li>
                <li><strong>Nueva Fecha:</strong> {$nuevo_horario['fecha']}</li>
                <li><strong>Nueva Hora:</strong> {$nuevo_horario['hora_disponible']}</li>
            </ul>
            <p>Gracias por utilizar nuestro servicio.</p>
        ";

        $mensaje_dueno = "
            <h3>Estimado/a Administrador</h3>
            <p>El cliente {$cita['nombre']} {$cita['apellido']} ha reagendado su cita <strong>#{$cita['id_form']}</strong>. Aquí están los nuevos detalles:</p>
            <ul>
                <li><strong>Servicio:</strong> {$cita['tipo_servicio']}</li>
                <li><strong>Producto:</strong> {$cita['tipo_producto']}</li>
                <li><strong>Fecha Anterior:</strong> {$cita['fecha']}</li>
                <li><strong>Hora Anterior:</strong> {$cita['hora_disponible']}</li>
                <li><strong>Nueva Fecha:</strong> {$nuevo_horario['fecha']}</li>
                <li><strong>Nueva Hora:</strong> {$nuevo_horario['hora_disponible']}</li>
                <li><strong>Correo Cliente:</strong> {$correo_cliente}</li>
            </ul>
        ";

        // Configurar el encabezado de los correos
        $headers = "From: Servicio Técnico <no-reply@doncarlos.com>\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";

        // Enviar correos
        mail($correo_cliente, $asunto, $mensaje_cliente, $headers);
        mail($correo_dueno, $asunto, $mensaje_dueno, $headers);

        // Responder con éxito
        echo json_encode(['success' => true, 'message' => 'Cita reagendada con éxito y correos enviados.']);
        exit();
    } else {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
        exit();
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit();
}
?>