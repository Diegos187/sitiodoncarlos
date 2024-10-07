<?php
session_start();
include('../conexion.php');

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login/login.php');
    exit();
}

// Verificar que el formulario tiene una cita válida
if (isset($_GET['id_form'])) {
    $id_form = $_GET['id_form'];

    // Obtener la cita actual
    $query = "SELECT * FROM Formulario WHERE id_form = ?";
    $stmt = $conex->prepare($query);
    $stmt->bind_param('i', $id_form);
    $stmt->execute();
    $result = $stmt->get_result();
    $cita = $result->fetch_assoc();

    // Verificar si la cita no existe o está cancelada
    if (!$cita || $cita['estado'] == 'cancelado') {
        echo "Esta cita no puede ser reagendada.";
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Procesar el nuevo horario seleccionado
        $nueva_direccion = $_POST['direccion'];
        $nuevos_detalles = $_POST['detalles'];
        $nuevo_telefono = $_POST['telefono'];


        // Actualizar el id_horario de la cita en la tabla Formulario
        $query_update_cita = "UPDATE Formulario 
                              SET direccion = ?, detalles = ?, telefono = ?, estado = 'pendiente' 
                              WHERE id_form = ?";
        $stmt_update_cita = $conex->prepare($query_update_cita);
        $stmt_update_cita->bind_param('sssi', $nueva_direccion, $nuevos_detalles, $nuevo_telefono, $id_form);
        $stmt_update_cita->execute();

        // Redirigir al dashboard
        header('Location: ./dashboard.php');
        exit();
    }
} else {
    echo "Cita no encontrada.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reagendar Cita</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../index.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <header class="header-agendar container">
    
        <div class="header-agendar container">
        <h1>Modificar Cita</h1>
        </div>
    </header>

    <section class="formulario containter">
    <form method="POST">
    <div class="input-container">
    <label for="direccion">Dirección</label>
        <input type="text" id="direccion" name="direccion" value="<?php echo htmlspecialchars($cita['direccion']); ?>">
        <i class="fa-solid bi-geo-alt"></i>
        </div>
    
        <div class="input-container">
        <label for="telefono">Teléfono</label>
        <input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($cita['telefono']); ?>">
        <i class="fa-solid fa-phone"></i>
        </div>


        <label for="detalles">Detalles</label>
        <textarea id="detalles" name="detalles"><?php echo htmlspecialchars($cita['detalles']); ?></textarea>

        <button type="submit" class="btn">Confirmar modificación</button>
        <button href="./dashboard.php">Regresar</a>
        
    </form>
    </section>
    <a style="align-items: center;" href="./dashboard.php">Regresar</a>

    <script>
        // Cargar horarios disponibles al seleccionar la fecha
        $(document).ready(function() {
            $('#fecha').change(function() {
                var fechaSeleccionada = $(this).val();
                $.ajax({
                    url: '../formulario/obtener_horarios.php',
                    method: 'GET',
                    data: { date: fechaSeleccionada },
                    success: function(data) {
                        var horarios = JSON.parse(data);
                        $('#horario').html(''); // Limpiar opciones anteriores
                        horarios.forEach(function(horario) {
                            $('#horario').append('<option value="' + horario.id_horario + '">' + horario.hora_disponible + '</option>');
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>