<?php
include('conexion.php');

$servicio = $_GET['servicio'];
$producto = $_GET['producto'];

$query = "SELECT precio_minimo FROM PrecioServicioProducto WHERE id_servicio = '$servicio' AND id_producto = '$producto'";
$result = mysqli_query($conex, $query);

if (mysqli_num_rows($result) > 0) {
    $precio = mysqli_fetch_assoc($result)['precio_minimo'];
    echo json_encode($precio);
} else {
    echo json_encode(0);
}
?>
