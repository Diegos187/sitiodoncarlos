<?php
include('conexion.php');

// Obtener los datos del formulario
$nombre = $_POST['nombre'];
$correo = $_POST['correo'];
$detalles = $_POST['detalles'];
$servicio = $_POST['servicio'];
$producto = $_POST['producto'];
$horario = $_POST['horario'];

// Insertar datos en la tabla Formulario
$query = "INSERT INTO Formulario (nombre, correo, detalles, id_servicio, id_producto, id_horario) 
          VALUES ('$nombre', '$correo', '$detalles', '$servicio', '$producto', '$horario')";

if (mysqli_query($conex, $query)) {
    // Actualizar el horario a 'reservado'
    $queryUpdateHorario = "UPDATE Horario SET estado = 'reservado' WHERE id_horario = '$horario'";
    mysqli_query($conex, $queryUpdateHorario);

    echo "Cita agendada correctamente.";
} else {
    echo "Error: " . $query . "<br>" . mysqli_error($conex);
}

mysqli_close($conex);
?>
