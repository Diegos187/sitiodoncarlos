<?php
include('conexion.php');

// Obtener los datos del formulario
$nombre = $_POST['nombre'];
$apellido = $_POST['apellido'];
$rut = $_POST['rut'];
$direccion = $_POST['direccion'];
$telefono = $_POST['telefono'];
$correo = $_POST['correo'];
$detalles = $_POST['detalles'];
$servicio_id = $_POST['servicio'];
$producto_id = $_POST['producto'];
$horario_id = $_POST['horario'];

// Consultar el nombre del servicio
$queryServicio = "SELECT tipo_servicio FROM Servicio WHERE id_servicio = '$servicio_id'";
$resultServicio = mysqli_query($conex, $queryServicio);
$servicio = mysqli_fetch_assoc($resultServicio)['tipo_servicio'];

// Consultar el nombre del producto
$queryProducto = "SELECT tipo_producto FROM Producto WHERE id_producto = '$producto_id'";
$resultProducto = mysqli_query($conex, $queryProducto);
$producto = mysqli_fetch_assoc($resultProducto)['tipo_producto'];

// Consultar la hora del horario
$queryHorario = "SELECT hora_disponible FROM Horario WHERE id_horario = '$horario_id'";
$resultHorario = mysqli_query($conex, $queryHorario);
$horario = mysqli_fetch_assoc($resultHorario)['hora_disponible'];

// Insertar datos en la tabla Formulario
$query = "INSERT INTO Formulario (nombre, apellido, rut, direccion, telefono, correo, detalles, id_servicio, id_producto, id_horario) 
          VALUES ('$nombre', '$apellido', '$rut', '$direccion', '$telefono', '$correo', '$detalles', '$servicio_id', '$producto_id', '$horario_id')";

if (mysqli_query($conex, $query)) {
    // Obtener el ID del formulario recién insertado
    $id_form = mysqli_insert_id($conex);

    // Verificar si el RUT ingresado ya está registrado en la tabla login
    $query_verificar_rut = "SELECT * FROM login WHERE rut = '$rut'";
    $result_rut = mysqli_query($conex, $query_verificar_rut);

    if (mysqli_num_rows($result_rut) > 0) {
        // Si el RUT está registrado en login, asociar la cita a la tabla Citas
        $query_insert_cita = "INSERT INTO Citas (id_form, rut_cliente) VALUES ('$id_form', '$rut')";
        mysqli_query($conex, $query_insert_cita);
    }

    // Actualizar el horario a 'reservado'
    $queryUpdateHorario = "UPDATE Horario SET estado = 'reservado' WHERE id_horario = '$horario_id'";
    mysqli_query($conex, $queryUpdateHorario);

    // Enviar el correo electrónico de confirmación
    $subject = "Siguiente paso de Cita";
    $body = "Hola $nombre $apellido,\n\nGracias por agendar una cita con nosotros.\n\nDetalles de la cita:\n\nServicio: $servicio\nProducto: $producto\nFecha y hora: $horario\n\n ESTE ES SOLO UN MENSAJE DE PRUEBA \n\nNos pondremos en contacto con usted en el teléfono: $telefono.\n\nSaludos,\nCentro Técnico DC";
    $headers = "From: diegomarin939@gmail.com";

    // Envía el correo electrónico
    if (mail($correo, $subject, $body, $headers)) {
        echo "El correo electrónico se envió correctamente.";
    } else {
        echo "Hubo un error al enviar el correo electrónico.";
    }

    // Redirigir a la página de éxito
    header('Location: cita_exitosa.html');
    exit;
} else {
    // Redirigir a la página de error
    header('Location: cita_mal.html');
    exit;
}

mysqli_close($conex);
?>
