<?php
session_start();
include('../conexion.php');

// Verificar si el usuario es administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_cargo'] !== 'administrador') {
    echo json_encode(["success" => false, "message" => "Acceso no autorizado"]);
    exit();
}

if (isset($_POST['id_form']) && isset($_POST['fecha']) && isset($_POST['horario'])) {
    $id_form = $_POST['id_form'];
    $fecha = $_POST['fecha'];
    $id_horario_nuevo = $_POST['horario'];

    // Obtener el horario actual de la cita
    $query_horario_actual = "SELECT id_horario FROM Formulario WHERE id_form = ?";
    $stmt_actual = $conex->prepare($query_horario_actual);
    $stmt_actual->bind_param('i', $id_form);
    $stmt_actual->execute();
    $result_actual = $stmt_actual->get_result();
    $horario_actual = $result_actual->fetch_assoc()['id_horario'];

    // Cambiar el estado del horario actual a 'disponible'
    $query_disponible = "UPDATE Horario SET estado = 'disponible' WHERE id_horario = ?";
    $stmt_disponible = $conex->prepare($query_disponible);
    $stmt_disponible->bind_param('i', $horario_actual);
    $stmt_disponible->execute();

    // Asignar el nuevo horario a la cita y actualizar su estado
    $query_reagendar = "UPDATE Formulario SET id_horario = ? WHERE id_form = ?";
    $stmt_reagendar = $conex->prepare($query_reagendar);
    $stmt_reagendar->bind_param('ii', $id_horario_nuevo, $id_form);

    if ($stmt_reagendar->execute()) {
        // Cambiar el nuevo horario a 'reservado'
        $query_reservado = "UPDATE Horario SET estado = 'reservado' WHERE id_horario = ?";
        $stmt_reservado = $conex->prepare($query_reservado);
        $stmt_reservado->bind_param('i', $id_horario_nuevo);
        $stmt_reservado->execute();

        // Obtener detalles de la cita y del cliente
        $query_cita = "
            SELECT f.id_form, f.nombre, f.apellido, f.correo, f.rut, s.tipo_servicio, p.tipo_producto, h.fecha AS fecha_anterior, h.hora_disponible AS hora_anterior,
            h_nuevo.fecha AS nueva_fecha, h_nuevo.hora_disponible AS nueva_hora
            FROM Formulario f
            JOIN Servicio s ON f.id_servicio = s.id_servicio
            JOIN Producto p ON f.id_producto = p.id_producto
            JOIN Horario h ON f.id_horario = h.id_horario
            JOIN Horario h_nuevo ON h_nuevo.id_horario = ?
            WHERE f.id_form = ?
        ";
        $stmt_cita = $conex->prepare($query_cita);
        $stmt_cita->bind_param('ii', $id_horario_nuevo, $id_form);
        $stmt_cita->execute();
        $result_cita = $stmt_cita->get_result();
        $cita = $result_cita->fetch_assoc();

        // Enviar correos de notificación
        $correo_cliente = $cita['correo'];
        $correo_dueno = 'diegomarin939@gmail.com';
        $asunto = "Reagendamiento de Cita - " . $cita['tipo_servicio'];

        // Mensaje para el cliente
        $mensaje_cliente = "
            <h3>Estimado/a {$cita['nombre']} {$cita['apellido']}</h3>
            <p>Su cita <strong>#{$cita['id_form']}</strong> ha sido reagendada con éxito. A continuación, los nuevos detalles de la cita:</p>
            <ul>
                <li><strong>RUT:</strong> {$cita['rut']}</li>
                <li><strong>Servicio:</strong> {$cita['tipo_servicio']}</li>
                <li><strong>Producto:</strong> {$cita['tipo_producto']}</li>
                <li><strong>Fecha Anterior:</strong> {$cita['fecha_anterior']}</li>
                <li><strong>Hora Anterior:</strong> {$cita['hora_anterior']}</li>
                <li><strong>Nueva Fecha:</strong> {$cita['nueva_fecha']}</li>
                <li><strong>Nueva Hora:</strong> {$cita['nueva_hora']}</li>
            </ul>
            <p>Gracias por utilizar nuestro servicio.</p>
        ";

        // Mensaje para el administrador/dueño
        $mensaje_dueno = "
            <h3>Estimado/a Administrador</h3>
            <p>La cita del cliente <strong>{$cita['nombre']} {$cita['apellido']}</strong> con RUT <strong>{$cita['rut']}</strong> ha sido reagendada. Aquí están los nuevos detalles:</p>
            <ul>
                <li><strong>Cita ID:</strong> {$cita['id_form']}</li>
                <li><strong>Servicio:</strong> {$cita['tipo_servicio']}</li>
                <li><strong>Producto:</strong> {$cita['tipo_producto']}</li>
                <li><strong>Fecha Anterior:</strong> {$cita['fecha_anterior']}</li>
                <li><strong>Hora Anterior:</strong> {$cita['hora_anterior']}</li>
                <li><strong>Nueva Fecha:</strong> {$cita['nueva_fecha']}</li>
                <li><strong>Nueva Hora:</strong> {$cita['nueva_hora']}</li>
                <li><strong>Correo Cliente:</strong> {$correo_cliente}</li>
            </ul>
            <p>Por favor, asegúrese de que todo esté listo para la nueva fecha.</p>
        ";

        // Configurar el encabezado de los correos
        $headers = "From: Servicio Técnico <no-reply@doncarlos.com>\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";

        // Enviar correos
        mail($correo_cliente, $asunto, $mensaje_cliente, $headers);
        mail($correo_dueno, $asunto, $mensaje_dueno, $headers);

        echo json_encode(["success" => true, "message" => "Cita reagendada con éxito y correos enviados."]);
    } else {
        echo json_encode(["success" => false, "message" => "Error al reagendar la cita"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Datos incompletos"]);
}
?>
