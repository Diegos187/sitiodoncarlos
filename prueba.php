<?php
// Incluir la conexión a la base de datos
include('conexion.php');

// Obtener los servicios y productos desde la base de datos
$queryServicios = "SELECT * FROM Servicio";
$resultServicios = mysqli_query($conex, $queryServicios);

$queryProductos = "SELECT * FROM Producto";
$resultProductos = mysqli_query($conex, $queryProductos);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendar Cita - Don Carlos</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body>
    <h1>Agendar Cita con Don Carlos</h1>

    <form action="procesar_formulario.php" method="POST">
        <label for="nombre">Nombre:</label><br>
        <input type="text" name="nombre" id="nombre" required><br><br>

        <label for="correo">Correo:</label><br>
        <input type="email" name="correo" id="correo" required><br><br>

        <label for="detalles">Detalles del Servicio:</label><br>
        <textarea name="detalles" id="detalles" required></textarea><br><br>

        <label for="servicio">Tipo de Servicio:</label><br>
        <select name="servicio" id="servicio" required>
            <option value="">Seleccionar Servicio</option>
            <?php
            while ($row = mysqli_fetch_assoc($resultServicios)) {
                echo "<option value='" . $row['id_servicio'] . "'>" . $row['tipo_servicio'] . "</option>";
            }
            ?>
        </select><br><br>

        <label for="producto">Producto:</label><br>
        <select name="producto" id="producto" required>
            <option value="">Seleccionar Producto</option>
            <?php
            while ($row = mysqli_fetch_assoc($resultProductos)) {
                echo "<option value='" . $row['id_producto'] . "'>" . $row['tipo_producto'] . "</option>";
            }
            ?>
        </select><br><br>

        <label for="fecha">Seleccionar Fecha:</label><br>
        <input type="date" name="fecha" id="fecha" required><br><br>

        <label for="horario">Horario Disponible:</label><br>
        <select name="horario" id="horario" required>
            <option value="">Seleccionar Horario</option>
        </select><br><br>

        <input type="submit" value="Agendar Cita">
    </form>

    <script>
        // Función para cargar los horarios disponibles al seleccionar la fecha
        $(document).ready(function() {
            $('#fecha').change(function() {
                var fechaSeleccionada = $(this).val();

                // Realizar una solicitud AJAX al servidor para obtener los horarios
                $.ajax({
                    url: 'obtener_horarios.php',
                    method: 'POST',
                    data: { fecha: fechaSeleccionada },
                    success: function(data) {
                        // Actualizar el select de horarios con los horarios recibidos
                        $('#horario').html(data);
                    }
                });
            });
        });
    </script>
</body>
</html>
