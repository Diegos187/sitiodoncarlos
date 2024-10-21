<?php
session_start();
include('../conexion.php');

// Verificar si el usuario está logueado y es un administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_cargo'] !== 'administrador') {
    echo json_encode(['success' => false, 'message' => 'No ha iniciado sesión como administrador.']);
    exit();
}

// Verificar si se recibió la fecha final y las horas
if (isset($_POST['fecha_final']) && isset($_POST['horas']) && is_array($_POST['horas'])) {
    $fecha_final = $_POST['fecha_final'];
    $horas = $_POST['horas'];

    // Filtrar horas para eliminar entradas vacías o no válidas
    $horas = array_filter($horas, function($hora) {
        return preg_match("/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/", $hora);
    });

    if (count($horas) === 0) {
        echo json_encode(['success' => false, 'message' => 'Debe ingresar al menos una hora válida.']);
        exit();
    }

    function insertarFechasYHoras($fecha_final, $horas) {
        global $conex;
    
        // Obtener la fecha de inicio (fecha actual del sistema)
        $fecha_actual = date("Y-m-d");
        
        // Convertir las fechas a DateTime
        $fechaInicio = new DateTime($fecha_actual);
        $fechaFin = new DateTime($fecha_final);
    
        // Iterar sobre cada día desde la fecha actual hasta la fecha final
        while ($fechaInicio <= $fechaFin) {
            // Solo insertar si es lunes a sábado
            $diaSemana = $fechaInicio->format('N'); // 1 = Lunes, 7 = Domingo
            if ($diaSemana <= 6) { // 6 es sábado
                $fechaFormateada = $fechaInicio->format('Y-m-d');
    
                // Comprobar si ya hay horarios en esa fecha
                $consulta = "SELECT COUNT(*) AS total FROM Horario WHERE fecha = '$fechaFormateada'";
                $resultado = mysqli_query($conex, $consulta);
                $row = mysqli_fetch_assoc($resultado);
    
                if ($row['total'] == 0) {
                    // Insertar las horas proporcionadas para este día
                    foreach ($horas as $hora) {
                        $insertarHorario = "INSERT INTO Horario (fecha, hora_disponible, estado) 
                                            VALUES ('$fechaFormateada', '$hora', 'disponible')";
                        mysqli_query($conex, $insertarHorario);
                    }
                }
            }
            // Avanzar al siguiente día
            $fechaInicio->modify('+1 day');
        }
    }

    // Llamar a la función para insertar las fechas y horas
    insertarFechasYHoras($fecha_final, $horas);
    echo json_encode(['success' => true, 'message' => "Fechas y horas insertadas con éxito hasta $fecha_final."]);
} else {
    echo json_encode(['success' => false, 'message' => 'No se ha proporcionado una fecha final o horas válidas.']);
}
?>
