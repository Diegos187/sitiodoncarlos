<?php
require('../fpdf/fpdf.php');
include('../conexion.php');

// Configurar la fecha actual y los últimos 15 días
$fecha_actual = date('Y-m-d');
$fecha_15_dias = date('Y-m-d', strtotime('-15 days'));

// Crear instancia de PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 12);

// Título
$pdf->SetFillColor(0, 51, 102);  // Color azul oscuro
$pdf->SetTextColor(255, 255, 255);  // Texto blanco
$pdf->Cell(0, 10, 'Reporte de las Ultimas 2 Semanas', 0, 1, 'C', true);

// Fechas del reporte
$pdf->Ln(5);
$pdf->SetFont('Arial', 'I', 10);
$pdf->SetTextColor(0, 0, 0);  // Texto negro
$pdf->Cell(0, 10, 'Desde: ' . $fecha_15_dias . ' Hasta: ' . $fecha_actual, 0, 1, 'C');

// Espacio
$pdf->Ln(3);

// Sección: Gestión de citas
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, 'Gestion de Citas:', 0, 1);
$pdf->Ln(1);

$pdf->SetFont('Arial', '', 10); // Cambia a fuente regular para los datos

// Cantidad de citas gestionadas
$queryCitasGestionadas = "SELECT COUNT(*) as total_citas_gestionadas 
                          FROM Formulario 
                          WHERE DATE(fecha) BETWEEN '$fecha_15_dias' AND '$fecha_actual'";
$resultCitasGestionadas = mysqli_query($conex, $queryCitasGestionadas);
$rowCitasGestionadas = mysqli_fetch_assoc($resultCitasGestionadas);
$totalCitasGestionadas = $rowCitasGestionadas['total_citas_gestionadas'];

$pdf->Cell(140, 7, 'Cantidad de citas gestionadas:', 0);
$pdf->Cell(40, 7, number_format($totalCitasGestionadas, 0, ',', '.'), 0, 1, 'R');

// Total de citas por estado
$queryEstadosCitas = "SELECT estado, COUNT(*) as total 
                      FROM Formulario 
                      WHERE DATE(fecha) BETWEEN '$fecha_15_dias' AND '$fecha_actual'
                      GROUP BY estado";
$resultEstadosCitas = mysqli_query($conex, $queryEstadosCitas);

while ($rowEstado = mysqli_fetch_assoc($resultEstadosCitas)) {
    $pdf->Cell(140, 7, 'Total de citas ' . ucfirst($rowEstado['estado']) . ':', 0);
    $pdf->Cell(40, 7, number_format($rowEstado['total'], 0, ',', '.'), 0, 1, 'R');
}


$pdf->Ln(3); // Espacio

// Sección: Historial de citas
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, 'Historial de Citas:', 0, 1);
$pdf->Ln(1);

$pdf->SetFont('Arial', '', 10); // Cambia a fuente regular para los datos

// Citas agendadas por día
$queryCitasPorDia = "SELECT DATE(fecha) as dia, COUNT(*) as total 
                     FROM Formulario 
                     WHERE DATE(fecha) BETWEEN '$fecha_15_dias' AND '$fecha_actual'
                     GROUP BY DATE(fecha)";
$resultCitasPorDia = mysqli_query($conex, $queryCitasPorDia);

while ($rowCitaDia = mysqli_fetch_assoc($resultCitasPorDia)) {
    $pdf->Cell(140, 7, 'Citas agendadas el ' . $rowCitaDia['dia'] . ':', 0);
    $pdf->Cell(40, 7, number_format($rowCitaDia['total'], 0, ',', '.'), 0, 1, 'R');
}

// Obtener total de clientes registrados en los últimos 15 días
$queryClientesRegistrados = "SELECT COUNT(*) as total_clientes 
                             FROM login
                             WHERE cargo = 'cliente' AND DATE(fecha_registro) BETWEEN '$fecha_15_dias' AND '$fecha_actual'";
$resultClientesRegistrados = mysqli_query($conex, $queryClientesRegistrados);
$rowClientesRegistrados = mysqli_fetch_assoc($resultClientesRegistrados);
$totalClientesRegistrados = $rowClientesRegistrados['total_clientes'];

$pdf->Ln(3); // Espacio
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, 'Clientes Registrados:', 0, 1);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(140, 7, 'Total de Clientes Registrados:', 0);
$pdf->Cell(40, 7, number_format($totalClientesRegistrados, 0, ',', '.'), 0, 1, 'R');

$pdf->Ln(2); // Espacio

// Sección: Presupuesto
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, 'Presupuesto:', 0, 1);
$pdf->Ln(1);

$pdf->SetFont('Arial', '', 10); // Cambia a fuente regular para los datos

// Presupuestos generados
$queryPresupuestosGenerados = "SELECT COUNT(*) as total_presupuestos 
                               FROM Presupuesto 
                               WHERE DATE(fecha_creacion) BETWEEN '$fecha_15_dias' AND '$fecha_actual'";
$resultPresupuestosGenerados = mysqli_query($conex, $queryPresupuestosGenerados);
$rowPresupuestosGenerados = mysqli_fetch_assoc($resultPresupuestosGenerados);
$totalPresupuestosGenerados = $rowPresupuestosGenerados['total_presupuestos'];

$pdf->Cell(140, 7, 'Total de Presupuestos Generados:', 0);
$pdf->Cell(40, 7, number_format($totalPresupuestosGenerados, 0, ',', '.'), 0, 1, 'R');

// Total de presupuestos por estado
$queryPresupuestosPorEstado = "SELECT estado, COUNT(*) as total 
                               FROM Presupuesto 
                               WHERE DATE(fecha_creacion) BETWEEN '$fecha_15_dias' AND '$fecha_actual'
                               GROUP BY estado";
$resultPresupuestosPorEstado = mysqli_query($conex, $queryPresupuestosPorEstado);

while ($rowPresupuestoEstado = mysqli_fetch_assoc($resultPresupuestosPorEstado)) {
    $pdf->Cell(140, 7, 'Presupuestos ' . ucfirst($rowPresupuestoEstado['estado']) . ':', 0);
    $pdf->Cell(40, 7, number_format($rowPresupuestoEstado['total'], 0, ',', '.'), 0, 1, 'R');
}

// Monto total generado en presupuestos aceptados
$queryMontoAceptado = "SELECT SUM(monto) as total_aceptado 
                       FROM Presupuesto 
                       WHERE estado = 'aceptado' AND DATE(fecha_creacion) BETWEEN '$fecha_15_dias' AND '$fecha_actual'";
$resultMontoAceptado = mysqli_query($conex, $queryMontoAceptado);
$rowMontoAceptado = mysqli_fetch_assoc($resultMontoAceptado);
$totalMontoAceptado = $rowMontoAceptado['total_aceptado'];

$pdf->Cell(140, 7, 'Monto Total Aceptado:', 0);
$pdf->Cell(40, 7, '$' . number_format($totalMontoAceptado, 2, ',', '.'), 0, 1, 'R');

// Salida del PDF
$pdf->Output('D', 'Reporte_15_dias_tabla.pdf');
?>
