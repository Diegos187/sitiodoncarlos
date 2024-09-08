<?php
include('conexion.php');

// Obtener la fecha seleccionada
$fecha = $_POST['fecha'];

// Consultar los horarios disponibles para la fecha seleccionada
$queryHorarios = "SELECT * FROM Horario WHERE fecha = '$fecha' AND estado = 'disponible'";
$resultHorarios = mysqli_query($conex, $queryHorarios);

// Construir las opciones de horarios
$horarios = "<option value=''>Seleccionar Horario</option>";

while ($row = mysqli_fetch_assoc($resultHorarios)) {
    $horarios .= "<option value='" . $row['id_horario'] . "'>" . $row['hora_disponible'] . "</option>";
}

// Enviar los horarios de vuelta al cliente
echo $horarios;
?>
