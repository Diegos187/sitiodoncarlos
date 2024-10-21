<?php
session_start();
include('../conexion.php');

// Verificar si el usuario está logueado y es un administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_cargo'] !== 'administrador') {
    echo json_encode(['success' => false, 'message' => 'No tiene permisos para acceder a esta información.']);
    exit();
}

// Obtener el total de citas desde la tabla Formulario
$queryCitas = "SELECT COUNT(*) as total_citas FROM Formulario";
$resultCitas = mysqli_query($conex, $queryCitas);
$rowCitas = mysqli_fetch_assoc($resultCitas);
$totalCitas = $rowCitas['total_citas'];

// Obtener el total de clientes desde la tabla login (sin contar administradores)
$queryClientes = "SELECT COUNT(*) as total_clientes FROM login WHERE cargo = 'cliente'";
$resultClientes = mysqli_query($conex, $queryClientes);
$rowClientes = mysqli_fetch_assoc($resultClientes);
$totalClientes = $rowClientes['total_clientes'];

// Obtener el total de citas pendientes desde la tabla Formulario
$queryCitasPendientes = "SELECT COUNT(*) as total_citas_pendientes FROM Formulario WHERE estado = 'pendiente'";
$resultCitasPendientes = mysqli_query($conex, $queryCitasPendientes);
$rowCitasPendientes = mysqli_fetch_assoc($resultCitasPendientes);
$totalCitasPendientes = $rowCitasPendientes['total_citas_pendientes'];

// Obtener el total de citas en proceso desde la tabla Formulario (suponiendo que el estado es 'en proceso')
$queryCitasEnProceso = "SELECT COUNT(*) as total_citas_en_proceso FROM Formulario WHERE estado = 'en proceso'";
$resultCitasEnProceso = mysqli_query($conex, $queryCitasEnProceso);
$rowCitasEnProceso = mysqli_fetch_assoc($resultCitasEnProceso);
$totalCitasEnProceso = $rowCitasEnProceso['total_citas_en_proceso'];


// Obtener las últimas 4 citas
$queryUltimasCitas = "SELECT id_form, nombre, apellido, correo, telefono, estado FROM Formulario ORDER BY id_form DESC LIMIT 4";
$resultUltimasCitas = mysqli_query($conex, $queryUltimasCitas);
$ultimasCitas = [];
while ($rowCita = mysqli_fetch_assoc($resultUltimasCitas)) {
    $ultimasCitas[] = $rowCita;
}

// Obtener los últimos 4 clientes registrados
$queryUltimosClientes = "SELECT id, rut, nombre, email, fecha_registro FROM login WHERE cargo = 'cliente' ORDER BY fecha_registro DESC LIMIT 4";
$resultUltimosClientes = mysqli_query($conex, $queryUltimosClientes);
$ultimosClientes = [];
while ($rowCliente = mysqli_fetch_assoc($resultUltimosClientes)) {
    $ultimosClientes[] = $rowCliente;
}

// Obtener el total de chats con mensajes no leídos del cliente
$queryChatsPendientes = "
    SELECT COUNT(DISTINCT id_form) as total_chats_pendientes
    FROM Mensajes 
    WHERE leido = 0 
    AND id_usuario IN (SELECT id FROM login WHERE cargo = 'cliente')
";
$resultChatsPendientes = mysqli_query($conex, $queryChatsPendientes);
$rowChatsPendientes = mysqli_fetch_assoc($resultChatsPendientes);
$totalChatsPendientes = $rowChatsPendientes['total_chats_pendientes'];

// Enviar la respuesta en formato JSON
echo json_encode([
    'success' => true,
    'total_citas' => $totalCitas,
    'total_clientes' => $totalClientes,
    'total_citas_pendientes' => $totalCitasPendientes,
    'total_citas_en_proceso' => $totalCitasEnProceso,
    'total_chats_pendientes' => $totalChatsPendientes,
    'ultimas_citas' => $ultimasCitas,
    'ultimos_clientes' => $ultimosClientes
]);
?>
