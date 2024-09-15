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
    <title>Agendar Cita</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/index.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- jQuery para el AJAX -->
</head>
<body>
    <header class="header-agendar">
        <div class="menu container">
            <a href="index.html" class="logo">DC</a>
            <input type="checkbox" id="menu"/>
            <label for="menu">
                <i class="bi bi-list"></i>
            </label>
            <nav class="navbar">
                <ul>
                    <li><a href="index.html">Inicio</a></li>
                    <li><a href="index.html">Nosotros</a></li>
                    <li><a href="servicios.html">Servicios</a></li>
                    <li><a href="index.html">Contacto</a></li>
                    <li><a href="info_pago.html">Sobre Costo-Servicio</a></li>
                </ul>
            </nav>
        </div>

        <div class="header-agendar container">
            <h1 class="">Agenda tu cita</h1>
        </div>
    </header>

<section class="formulario container">
    <form action="procesar_formulario.php" method="post" autocomplete="off">
        <div class="input-container">
            <label for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre" placeholder="Nombre" required>
            <i class="fa-solid fa-user"></i>
        </div>
        <div class="input-container">
            <label for="apellido">Apellido</label>
            <input type="text" id="apellido" name="apellido" placeholder="Apellido" required>
            <i class="fa-solid fa-user"></i>
        </div>
        <div class="input-container">
            <label for="rut">RUT</label>
            <input type="text" id="rut" name="rut" placeholder="RUT" required>
            <i class="fa-solid bi-person-vcard-fill"></i>
        </div>
        <div class="input-container">
            <label for="direccion">Dirección</label>
            <input type="text" id="direccion" name="direccion" placeholder="Dirección" required>
            <i class="fa-solid bi-geo-alt"></i>
        </div>
        <div class="input-container">
            <label for="telefono">Teléfono</label>
            <input type="text" id="telefono" name="telefono" placeholder="Teléfono" required>
            <i class="fa-solid fa-phone"></i>
        </div>
        <div class="input-container">
            <label for="correo">Correo</label>
            <input type="email" id="correo" name="correo" placeholder="Correo" required>
            <i class="fa-solid fa-envelope"></i>
        </div>

        <label for="servicio">Tipo de Servicio</label>
        <div class="input-container option-c" style="margin-bottom: 20px;">
        <select id="servicio" name="servicio" required>
        <option>Selecciona el servicio</option>
            <?php while($servicio = mysqli_fetch_assoc($resultServicios)) { ?>
                
                <option value="<?php echo $servicio['id_servicio']; ?>">
                    <?php echo $servicio['tipo_servicio']; ?>
                </option>
            <?php } ?>
        </select>
        </div>

        <label for="producto">Producto</label>
        <div class="input-container option-c" style="margin-bottom: 20px;">
        <select id="producto" name="producto" required>
        <option>Selecciona el producto</option>
            <?php while($producto = mysqli_fetch_assoc($resultProductos)) { ?>
                
                <option value="<?php echo $producto['id_producto']; ?>">
                    <?php echo $producto['tipo_producto']; ?>
                </option>
            <?php } ?>
        </select>
        </div>

        <div class="input-container">
            <label for="fecha">Fecha</label>
            <input type="date" id="fecha" name="fecha" min="<?php echo date('Y-m-d'); ?>" required>
            <i class="fa-solid fa-calendar"></i>   
        </div>


            <label for="horario">Hora Disponible</label>
            <div class="input-container option-c" style="margin-bottom: 20px;">
            <select id="horario" name="horario" required>
                <option value="">Selecciona una fecha primero</option>
            </select>
        </div>

        <div class="input-container">
            <label for="detalles">Detalles</label>
            <textarea id="detalles" name="detalles" placeholder="Detalles adicionales" required></textarea>
        </div>

        <input type="submit" name="send" class="btn" value="Agendar Cita">
    </form>
</section>

<script>
// Cargar horarios disponibles al seleccionar la fecha
$(document).ready(function() {
    $('#fecha').change(function() {
        var fechaSeleccionada = $(this).val();

        $.ajax({
            url: 'obtener_horarios.php',
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

<script>
    function mostrarMensajeCita(mensaje) {
    alert(mensaje); // Puedes reemplazar alert() por una función más personalizada para crear una ventana emergente con estilos CSS.
}
</script>

</body>
</html>
