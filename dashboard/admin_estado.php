<?php
session_start();
include('../conexion.php');

// Verificar si el usuario es administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_cargo'] !== 'administrador') {
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado']);
    exit();
}

if (isset($_POST['id_form']) && isset($_POST['nuevo_estado'])) {
    $id_form = $_POST['id_form'];
    $nuevo_estado = $_POST['nuevo_estado'];

    // Obtener detalles de la cita y del cliente
    $query_cita = "
        SELECT f.id_form, f.nombre, f.apellido, f.correo, f.estado, h.fecha, h.hora_disponible
        FROM Formulario f
        LEFT JOIN Horario h ON f.id_horario = h.id_horario
        WHERE f.id_form = ?";
    $stmt_cita = $conex->prepare($query_cita);
    $stmt_cita->bind_param('i', $id_form);
    $stmt_cita->execute();
    $cita = $stmt_cita->get_result()->fetch_assoc();

    if (!$cita) {
        echo json_encode(['success' => false, 'message' => 'Cita no encontrada']);
        exit();
    }

    // Iniciar transacción
    $conex->begin_transaction();

    // Actualizar el estado de la cita
    $query = "UPDATE Formulario SET estado = ? WHERE id_form = ?";
    $stmt = $conex->prepare($query);
    $stmt->bind_param('si', $nuevo_estado, $id_form);

    if ($stmt->execute()) {
        if ($nuevo_estado === 'cancelado') {
            // Obtener el id_horario de la cita
            $query_horario = "SELECT id_horario FROM Formulario WHERE id_form = ?";
            $stmt_horario = $conex->prepare($query_horario);
            $stmt_horario->bind_param('i', $id_form);
            $stmt_horario->execute();
            $result_horario = $stmt_horario->get_result();

            if ($row_horario = $result_horario->fetch_assoc()) {
                $id_horario = $row_horario['id_horario'];

                // Cambiar el estado del horario a disponible
                $query_liberar_horario = "UPDATE Horario SET estado = 'disponible' WHERE id_horario = ?";
                $stmt_liberar = $conex->prepare($query_liberar_horario);
                $stmt_liberar->bind_param('i', $id_horario);

                if (!$stmt_liberar->execute()) {
                    // Si ocurre un error al liberar el horario, deshacer la transacción
                    $conex->rollback();
                    echo json_encode(['success' => false, 'message' => 'Error al liberar el horario']);
                    exit();
                }
            }
        }

        // Enviar correos según el nuevo estado
        $correo_cliente = $cita['correo'];
        $correo_dueno = "diegomarin939@gmail.com"; // Cambia esto si es necesario

        if ($nuevo_estado === 'cancelado') {
            // Correo para el cliente
            $asunto_cliente = "Cita #{$cita['id_form']} Cancelada";
            $mensaje_cliente = "
                <h3>Estimado/a {$cita['nombre']} {$cita['apellido']}</h3>
                <p>Lamentamos informarle que su cita <strong>#{$cita['id_form']}</strong> ha sido cancelada.</p>
                <p>Por favor, contacte con nosotros para más detalles.</p>
            ";
            // Correo para el dueño
            $asunto_dueno = "Cita #{$cita['id_form']} Cancelada";
            $mensaje_dueno = "
                <h3>Estimado/a Administrador</h3>
                <p>La cita <strong>#{$cita['id_form']}</strong> del cliente {$cita['nombre']} {$cita['apellido']} ha sido cancelada.</p>
            ";

        } elseif ($nuevo_estado === 'confirmado') {
            // Correo para el cliente
            $asunto_cliente = "Cita #{$cita['id_form']} Confirmada";
            $mensaje_cliente = "
                <h3>Estimado/a {$cita['nombre']} {$cita['apellido']}</h3>
                <p>Su cita <strong>#{$cita['id_form']}</strong> ha sido confirmada.</p>
                <p>El pago ha sido recibido y la visita se realizará el día <strong>{$cita['fecha']}</strong> a las <strong>{$cita['hora_disponible']}</strong>.</p>
            ";
            // Correo para el dueño
            $asunto_dueno = "Cita #{$cita['id_form']} Confirmada";
            $mensaje_dueno = "
                <h3>Estimado/a Administrador</h3>
                <p>La cita <strong>#{$cita['id_form']}</strong> del cliente {$cita['nombre']} {$cita['apellido']} ha sido confirmada. La visita será el día {$cita['fecha']} a las {$cita['hora_disponible']}.</p>
            ";

        } elseif ($nuevo_estado === 'en proceso') {
            // Correo para el cliente
            $asunto_cliente = "Presupuesto aceptado - Cita #{$cita['id_form']}";
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

            // Correo para el dueño
            $asunto_dueno = "Presupuesto aceptado - Cita #{$cita['id_form']}";
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
        } 
        
        elseif ($nuevo_estado === 'finalizado') {
            // Correo para el cliente
            $asunto_cliente = "Cita #{$cita['id_form']} Finalizada";
            $mensaje_cliente = "
                <h3>Estimado/a {$cita['nombre']} {$cita['apellido']}</h3>
                <p>Nos complace informarle que el producto de su cita <strong>#{$cita['id_form']}</strong> ya está listo.</p>
                <p>Nos pondremos en contacto con usted para coordinar la entrega. Si el producto se encuentra en nuestro domicilio, puede recogerlo cuando lo desee.</p>
            ";
            // Correo para el dueño
            $asunto_dueno = "Cita #{$cita['id_form']} Finalizada";
            $mensaje_dueno = "
                <h3>Felicidades Administrador</h3>
                <p>El servicio de la cita <strong>#{$cita['id_form']}</strong> para el cliente {$cita['nombre']} {$cita['apellido']} ha sido finalizado con éxito.</p>
            ";
        }

        // Función para enviar el correo (puedes adaptar a PHPMailer si es necesario)
        function enviarCorreo($destinatario, $asunto, $mensaje) {
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= 'From: admin@tu-sitio.com' . "\r\n";
            return mail($destinatario, $asunto, $mensaje, $headers);
        }

        // Enviar correos
        enviarCorreo($correo_cliente, $asunto_cliente, $mensaje_cliente);
        enviarCorreo($correo_dueno, $asunto_dueno, $mensaje_dueno);

        // Confirmar transacción
        $conex->commit();
        echo json_encode(['success' => true, 'message' => 'Estado actualizado y correos enviados correctamente']);
    } else {
        // Deshacer la transacción en caso de error
        $conex->rollback();
        echo json_encode(['success' => false, 'message' => 'Estado actualizado y correos enviados correctamente']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
}
?>