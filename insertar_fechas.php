<!-- ESTA FUNCION SE ENCARGA DE INGRESAR FECHAS HASTA UNA FECHA FINAL
DETERMINADA Y HORAS DISPONIBLES PARA CADA DÍA DE LA SEMANA,
SIEMPRE Y CUANDO NO EXISTAN YA EN LA BASE DE DATOS. SE TOMA COMO FECHA DE 
INICIO LA FECHA ACTUAL DEL SISTEMA.
LA FECHA SE INGRESA AL FINAL DEL CODIGO, PARA HACERLA FUNCIONAR, SE DEBE HACER EL LLAMADO DE ESTA FUNCION EN INTERNET POR EJEMPLO
http://localhost/doncarlos/insertar_fechas.php -->

<?php
// Conexión a la base de datos
include("conexion.php");

function insertarFechasYHoras($fecha_final) {
    global $conex;

    // Obtener la fecha de inicio (fecha actual del sistema)
    $fecha_actual = date("Y-m-d");
    
    // Convertir las fechas a DateTime
    $fechaInicio = new DateTime($fecha_actual);
    $fechaFin = new DateTime($fecha_final);

    // Horas disponibles
    $horas = ['10:00:00', '12:00:00', '14:00:00', '16:00:00'];

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
                // Insertar las horas disponibles para este día
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

// Ejemplo de uso de la función
$fecha_final = '2024-10-01'; // Puedes cambiar esta fecha al valor deseado
insertarFechasYHoras($fecha_final);

echo "Fechas y horas insertadas con éxito hasta $fecha_final.";
?>
