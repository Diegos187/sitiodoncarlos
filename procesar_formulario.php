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
$servicio = $_POST['servicio'];
$producto = $_POST['producto'];
$horario = $_POST['horario'];

// Insertar datos en la tabla Formulario
$query = "INSERT INTO Formulario (nombre, apellido, rut, direccion, telefono, correo, detalles, id_servicio, id_producto, id_horario) 
          VALUES ('$nombre', '$apellido', '$rut', '$direccion', '$telefono', '$correo', '$detalles', '$servicio', '$producto', '$horario')";

if (mysqli_query($conex, $query)) {
    // Actualizar el horario a 'reservado'
    $queryUpdateHorario = "UPDATE Horario SET estado = 'reservado' WHERE id_horario = '$horario'";
    mysqli_query($conex, $queryUpdateHorario);

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
