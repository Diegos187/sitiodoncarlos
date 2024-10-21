<?php
session_start();

// Verificar si el usuario está logueado y es un administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_cargo'] !== 'administrador') {
    header('Location: ../login/login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insertar Fechas y Horarios</title>
    <!-- <style>
        .horas-container {
            margin-top: 10px;
        }
        .horas-container input {
            margin-bottom: 5px;
            display: block;
        }
        #agregar-hora {
            margin-top: 10px;
            background-color: #007bff;
            color: white;
            padding: 5px 10px;
            border: none;
            cursor: pointer;
        }
        #agregar-hora:hover {
            background-color: #0056b3;
        }
    </style> -->
</head>
<body>
    <div class="container">
        <h2 style="text-align: center; margin-top: 30px;">Insertar Fechas y Horarios</h2>
        <form id="insertarFechasForm" action="procesar_fechas.php" method="POST">
            <label for="fecha_final">Selecciona la fecha final:</label>
            <input type="date" id="fecha_final" name="fecha_final" min="<?php echo date('Y-m-d'); ?>"  required>

            <h2>Agregar Horarios Disponibles</h3>
            <div id="horas-container" class="horas-container">
                <input type="time" name="horas[]" required>
            </div>
            <button type="button" id="agregar-hora" style="align-items:center;">Agregar otra hora</button>

            <br><br>
            <button type="submit">Insertar Fechas</button>
        </form>
        <div id="mensaje"></div>
    </div>

    <script>
        // Script para agregar más campos de horas
        document.getElementById('agregar-hora').addEventListener('click', function () {
            const container = document.getElementById('horas-container');

            // Crear un nuevo campo de input de tipo 'time'
            const input = document.createElement('input');
            input.type = 'time';
            input.name = 'horas[]';
            input.required = true;

            // Agregar el nuevo input al contenedor de horas
            container.appendChild(input);
        });
    </script>
</body>
</html>
