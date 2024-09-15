<?php
include 'conexion.php';

if (isset($_GET['date'])) {
    $fecha = $_GET['date'];

    $query = "SELECT id_horario, hora_disponible FROM Horario WHERE fecha='$fecha' AND estado='disponible'";
    $resultado = mysqli_query($conex, $query);

    $horarios = [];
    while ($row = mysqli_fetch_assoc($resultado)) {
        $horarios[] = $row;
    }

    echo json_encode($horarios);
}

mysqli_close($conex);
?>
