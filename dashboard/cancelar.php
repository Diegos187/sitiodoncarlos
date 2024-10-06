<?php
session_start();
include('../conexion.php');

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login/login.php');
    exit();
}

// Obtener los datos de la cita a cancelar
$id_form = $_GET['id_form'];

// Obtener los detalles de la cita y el horario relacionado
$query = "SELECT f.*, h.fecha, h.hora_disponible, s.tipo_servicio, p.tipo_producto 
          FROM Formulario f 
          INNER JOIN Horario h ON f.id_horario = h.id_horario
          INNER JOIN Servicio s ON f.id_servicio = s.id_servicio 
          INNER JOIN Producto p ON f.id_producto = p.id_producto
          WHERE f.id_form = ?";
$stmt = $conex->prepare($query);
$stmt->bind_param('i', $id_form);
$stmt->execute();
$result = $stmt->get_result();
$cita = $result->fetch_assoc();

if (!$cita) {
    $_SESSION['error'] = "Cita no encontrada.";
    header('Location: ./dashboard.php');
    exit();
}

// Obtener el id del horario
$horario_id = $cita['id_horario'];

// Actualizar el estado del horario a 'disponible'
$query_update_horario = "UPDATE Horario SET estado = 'disponible' WHERE id_horario = ?";
$stmt = $conex->prepare($query_update_horario);
$stmt->bind_param('i', $horario_id);
$stmt->execute();
$stmt->close();

// Actualizar el estado de la cita a 'cancelado'
$query_update_formulario = "UPDATE Formulario SET estado = 'cancelado' WHERE id_form = ?";
$stmt = $conex->prepare($query_update_formulario);
$stmt->bind_param('i', $id_form);
$stmt->execute();
$stmt->close();

// Enviar correos de notificación de cancelación
$correo_cliente = $cita['correo'];
$correo_dueno = 'diegomarin939@gmail.com';
$asunto = "Cancelación de Cita - " . $cita['tipo_servicio'];

// Mensaje para el cliente
$mensaje_cliente = "
    <h3>Estimado/a {$cita['nombre']} {$cita['apellido']}</h3>
    <p>Le informamos que su cita <strong>#{$cita['id_form']}</strong> ha sido cancelada con éxito. A continuación, los detalles de la cita cancelada:</p>
    <ul>
        <li><strong>Servicio:</strong> {$cita['tipo_servicio']}</li>
        <li><strong>Producto:</strong> {$cita['tipo_producto']}</li>
        <li><strong>Fecha:</strong> {$cita['fecha']}</li>
        <li><strong>Hora:</strong> {$cita['hora_disponible']}</li>
    </ul>
    <p>Si necesita más asistencia, no dude en contactarnos. Gracias por utilizar nuestro servicio.</p>
";

// Mensaje para el administrador (dueño)
$mensaje_dueno = "
    <h3>Estimado/a Administrador</h3>
    <p>El cliente {$cita['nombre']} {$cita['apellido']} ha cancelado su cita <strong>#{$cita['id_form']}</strong>. Aquí están los detalles de la cita cancelada:</p>
    <ul>
        <li><strong>Servicio:</strong> {$cita['tipo_servicio']}</li>
        <li><strong>Producto:</strong> {$cita['tipo_producto']}</li>
        <li><strong>Fecha:</strong> {$cita['fecha']}</li>
        <li><strong>Hora:</strong> {$cita['hora_disponible']}</li>
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

// Redirigir al dashboard del cliente con mensaje de éxito
$_SESSION['success'] = "Cita cancelada con éxito y correos enviados.";
header('Location: ./dashboard.php');
exit();
?>
