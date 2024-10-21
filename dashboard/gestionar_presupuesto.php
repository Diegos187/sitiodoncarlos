<?php
session_start();
include('../conexion.php');

// Verificar si el usuario está logueado y es un cliente
if (!isset($_SESSION['user_id']) || $_SESSION['user_cargo'] !== 'cliente') {
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado']);
    exit();
}

// Verificar que los datos fueron enviados correctamente
if (isset($_POST['id_form']) && isset($_POST['estado'])) {
    $id_form = $_POST['id_form'];
    $estado = $_POST['estado'];

    // Iniciar una transacción para asegurar que ambas consultas se ejecuten correctamente
    $conex->begin_transaction();

    try {
        // Actualizar el estado del presupuesto en la base de datos
        $queryPresupuesto = "UPDATE Presupuesto SET estado = ? WHERE id_form = ?";
        $stmtPresupuesto = $conex->prepare($queryPresupuesto);
        $stmtPresupuesto->bind_param('si', $estado, $id_form);
        $stmtPresupuesto->execute();

        // Si el presupuesto es aceptado, actualizar el estado de la cita a 'en proceso'
        if ($estado === 'aceptado') {
            $queryCita = "UPDATE Formulario SET estado = 'en proceso' WHERE id_form = ?";
            $stmtCita = $conex->prepare($queryCita);
            $stmtCita->bind_param('i', $id_form);
            $stmtCita->execute();
        }

        // Obtener los detalles de la cita para enviar los correos
        $queryDetalles = "
            SELECT f.id_form, f.nombre, f.apellido, f.correo, f.telefono, s.tipo_servicio, p.tipo_producto, h.fecha, h.hora_disponible 
            FROM Formulario f
            JOIN Servicio s ON f.id_servicio = s.id_servicio
            JOIN Producto p ON f.id_producto = p.id_producto
            JOIN Horario h ON f.id_horario = h.id_horario
            WHERE f.id_form = ?
        ";
        $stmtDetalles = $conex->prepare($queryDetalles);
        $stmtDetalles->bind_param('i', $id_form);
        $stmtDetalles->execute();
        $resultDetalles = $stmtDetalles->get_result();
        $cita = $resultDetalles->fetch_assoc();

        // Enviar correos de notificación
        $correo_cliente = $cita['correo'];
        $correo_dueno = 'diegomarin939@gmail.com';

        if ($estado === 'aceptado') {
            $asunto_cliente = "Presupuesto aceptado - Cita #" . $cita['id_form'];
            $asunto_dueno = "Presupuesto aceptado - Cita #" . $cita['id_form'];

            $mensaje_cliente = "
                <h3>Estimado/a {$cita['nombre']} {$cita['apellido']}</h3>
                <p>Nos complace informarle que su presupuesto ha sido aceptado y hemos comenzado a trabajar en su solicitud.</p>
                <ul>
                    <li><strong>Servicio:</strong> {$cita['tipo_servicio']}</li>
                    <li><strong>Producto:</strong> {$cita['tipo_producto']}</li>
                    <li><strong>Fecha de la cita:</strong> {$cita['fecha']}</li>
                    <li><strong>Hora de la cita:</strong> {$cita['hora_disponible']}</li>
                </ul>
                <p>Por favor, esté atento a nuevas actualizaciones por correo electrónico.</p>
            ";

            $mensaje_dueno = "
                <h3>Estimado/a Administrador</h3>
                <p>El cliente {$cita['nombre']} {$cita['apellido']} ha aceptado el presupuesto para la cita <strong>#{$cita['id_form']}</strong>. Aquí están los detalles:</p>
                <ul>
                    <li><strong>Servicio:</strong> {$cita['tipo_servicio']}</li>
                    <li><strong>Producto:</strong> {$cita['tipo_producto']}</li>
                    <li><strong>Fecha de la cita:</strong> {$cita['fecha']}</li>
                    <li><strong>Hora de la cita:</strong> {$cita['hora_disponible']}</li>
                    <li><strong>Teléfono cliente:</strong> {$cita['telefono']}</li>
                    <li><strong>Correo Cliente:</strong> {$correo_cliente}</li>
                </ul>
                <p>Es momento de ponerse en marcha.</p>
            ";
        } elseif ($estado === 'rechazado') {
            $asunto_cliente = "Presupuesto rechazado - Cita #" . $cita['id_form'];
            $asunto_dueno = "Presupuesto rechazado - Cita #" . $cita['id_form'];

            $mensaje_cliente = "
                <h3>Estimado/a {$cita['nombre']} {$cita['apellido']}</h3>
                <p>Ha rechazado el presupuesto para su cita <strong>#{$cita['id_form']}</strong>. Nos pondremos en contacto con usted para presentarle una nueva propuesta de presupuesto.</p>
            ";

            $mensaje_dueno = "
                <h3>Estimado/a Administrador</h3>
                <p>El cliente {$cita['nombre']} {$cita['apellido']} ha rechazado el presupuesto para la cita <strong>#{$cita['id_form']}</strong>. Es necesario generar una nueva propuesta de presupuesto.</p>
            ";
        }

        // Configurar el encabezado de los correos
        $headers = "From: Servicio Técnico <no-reply@doncarlos.com>\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";

        // Enviar correos
        mail($correo_cliente, $asunto_cliente, $mensaje_cliente, $headers);
        mail($correo_dueno, $asunto_dueno, $mensaje_dueno, $headers);

        // Confirmar la transacción
        $conex->commit();

        // Devolver la respuesta correcta para el cliente
        if ($estado === 'aceptado') {
            echo json_encode(['success' => true, 'message' => 'Presupuesto aceptado con éxito. Revise su correo para más detalles.']);
        } else {
            echo json_encode(['success' => true, 'message' => 'Presupuesto rechazado con éxito. Revise su correo para más detalles.']);
        }

    } catch (Exception $e) {
        // En caso de error, revertir la transacción
        $conex->rollback();
        echo json_encode(['success' => false, 'message' => 'Error al actualizar el presupuesto y el estado de la cita.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
}
?>
