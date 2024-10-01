<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirigir al login si no hay sesión
    exit();
}

// Verificar que el usuario sea un cliente
if ($_SESSION['user_cargo'] !== 'cliente') {
    // Si es un administrador, redirigir al dashboard de administrador
    header('Location: admin_dashboard.php');
    exit();
}

// Conectar a la base de datos
include('conexion.php');

// Obtener las citas asociadas al usuario
$rut_cliente = $_SESSION['user_rut'];
$query_citas = "SELECT * FROM Formulario f 
                INNER JOIN Citas c ON f.id_form = c.id_form 
                WHERE c.rut_cliente = ?";
$stmt = $conex->prepare($query_citas);
$stmt->bind_param('s', $rut_cliente);
$stmt->execute();
$result_citas = $stmt->get_result();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Cliente</title>
</head>
<body>
    <h1>Bienvenido, <?php echo $_SESSION['user_name']; ?></h1>
    <p>Estas son tus citas reservadas:</p>

    <ul>
    <?php while ($cita = $result_citas->fetch_assoc()) { ?>
        <li>
            Servicio: <?php echo $cita['id_servicio']; ?><br>
            Producto: <?php echo $cita['id_producto']; ?><br>
            Fecha y hora: <?php echo $cita['id_horario']; ?>
        </li>
    <?php } ?>
    </ul>

    <a href="logout.php">Cerrar sesión</a>
</body>
</html>

<?php
$stmt->close();
$conex->close();
?>
