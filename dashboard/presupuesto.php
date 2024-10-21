<?php
session_start();

// Verificar si el usuario está logueado y es un administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_cargo'] !== 'administrador') {
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado']);
    exit();
}

// Conectar a la base de datos
include('../conexion.php');

// Verificar que los datos del presupuesto fueron enviados
if (isset($_POST['id_form']) && isset($_POST['monto']) && isset($_POST['comentario'])) {
    $id_form = $_POST['id_form'];
    $monto = $_POST['monto'];
    $comentario = $_POST['comentario'];

    // Verificar si el id_form está asociado a un usuario registrado en la tabla Citas
    $query_check_user = "SELECT id_form FROM Citas WHERE id_form = ?";
    $stmt_check_user = $conex->prepare($query_check_user);
    $stmt_check_user->bind_param('i', $id_form);
    $stmt_check_user->execute();
    $result_user = $stmt_check_user->get_result();

    if ($result_user->num_rows == 0) {
        echo json_encode(['success' => false, 'message' => 'No se puede generar un presupuesto para usuarios no registrados.']);
        exit();
    }

    // Verificar si ya existe un presupuesto asociado al id_form
    $query_check = "SELECT estado FROM Presupuesto WHERE id_form = ?";
    $stmt_check = $conex->prepare($query_check);
    $stmt_check->bind_param('i', $id_form);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        $presupuesto = $result_check->fetch_assoc();
        
        // Verificar si ya existe un presupuesto pendiente o aceptado
        if ($presupuesto['estado'] == 'pendiente' || $presupuesto['estado'] == 'aceptado') {
            echo json_encode(['success' => false, 'message' => 'Ya existe un presupuesto en estado ' . $presupuesto['estado'] . '.']);
            exit();
        }
    }

    // Si no hay un presupuesto pendiente o aceptado, se puede crear uno nuevo
    $query = "INSERT INTO Presupuesto (id_form, monto, comentario) VALUES (?, ?, ?)";
    $stmt = $conex->prepare($query);
    $stmt->bind_param('ids', $id_form, $monto, $comentario);
    
    if ($stmt->execute()) {
        // Obtener detalles de la cita para enviar los correos
        $query_detalles = "
            SELECT f.id_form, f.nombre, f.apellido, f.correo, f.rut, s.tipo_servicio, p.tipo_producto 
            FROM Formulario f
            JOIN Servicio s ON f.id_servicio = s.id_servicio
            JOIN Producto p ON f.id_producto = p.id_producto
            WHERE f.id_form = ?
        ";
        $stmt_detalles = $conex->prepare($query_detalles);
        $stmt_detalles->bind_param('i', $id_form);
        $stmt_detalles->execute();
        $result_detalles = $stmt_detalles->get_result();
        $cita = $result_detalles->fetch_assoc();

        // Enviar correos de notificación
        $correo_cliente = $cita['correo'];
        $correo_dueno = 'diegomarin939@gmail.com';
        $asunto_cliente = "Nuevo Presupuesto - Cita #" . $cita['id_form'];
        $asunto_dueno = "Presupuesto Enviado - Cita #" . $cita['id_form'];

        // Mensaje para el cliente
        $mensaje_cliente = "
            <h3>Estimado/a {$cita['nombre']} {$cita['apellido']}</h3>
            <p>Se ha generado un presupuesto para su cita <strong>#{$cita['id_form']}</strong>. Por favor, inicie sesión en nuestro sistema para revisarlo y decidir si desea aceptarlo o rechazarlo.</p>
            <ul>
                <li><strong>RUT:</strong> {$cita['rut']}</li>
                <li><strong>Servicio:</strong> {$cita['tipo_servicio']}</li>
                <li><strong>Producto:</strong> {$cita['tipo_producto']}</li>
                <li><strong>Monto del Presupuesto:</strong> $ {$monto}</li>
            </ul>
            <p>Gracias por utilizar nuestro servicio.</p>
        ";

        // Mensaje para el administrador/dueño
        $mensaje_dueno = "
            <h3>Estimado/a Administrador</h3>
            <p>Se ha generado un nuevo presupuesto para la cita <strong>#{$cita['id_form']}</strong> del cliente <strong>{$cita['nombre']} {$cita['apellido']}</strong> con RUT <strong>{$cita['rut']}</strong>.</p>
            <ul>
                <li><strong>Servicio:</strong> {$cita['tipo_servicio']}</li>
                <li><strong>Producto:</strong> {$cita['tipo_producto']}</li>
                <li><strong>Monto del Presupuesto:</strong> $ {$monto}</li>
                <li><strong>Correo Cliente:</strong> {$correo_cliente}</li>
            </ul>
            <p>El cliente ha sido notificado para revisar y decidir sobre el presupuesto.</p>
        ";

        // Configurar el encabezado de los correos
        $headers = "From: Servicio Técnico <no-reply@doncarlos.com>\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";

        // Enviar correos
        mail($correo_cliente, $asunto_cliente, $mensaje_cliente, $headers);
        mail($correo_dueno, $asunto_dueno, $mensaje_dueno, $headers);

        echo json_encode(['success' => true, 'message' => 'Presupuesto generado correctamente y correos enviados.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al generar el presupuesto.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
}
?>
