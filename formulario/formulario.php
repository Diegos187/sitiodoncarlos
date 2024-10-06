<?php
// Incluir la conexión a la base de datos
include('../conexion.php');

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
    <link rel="stylesheet" href="../index.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <header class="header-agendar">
        <div class="menu container">
            <a href="../index.html" class="logo">DC</a>
            <input type="checkbox" id="menu"/>
            <label for="menu">
                <i class="bi bi-list"></i>
            </label>
            <nav class="navbar">
                <ul>
                    <li><a href="../index.html">Inicio</a></li>
                    <li><a href="../index.html">Nosotros</a></li>
                    <li><a href="servicios.html">Servicios</a></li>
                    <li><a href="../index.html">Contacto</a></li>
                    <li><a href="info_pago.html">Sobre Costo-Servicio</a></li>
                </ul>
            </nav>
        </div>
        <div class="header-agendar container">
            <h1>Agenda tu cita</h1>
        </div>
    </header>

    <section class="formulario container">
        <form id="formularioCita" action="procesar_formulario.php" method="post" autocomplete="off">
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
                <label for="rut">RUT (Sin puntos y con guión)</label>
                <input type="text" id="rut" name="rut" placeholder="12345678-9" required>
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
            <div id="precio-minimo" style="margin-top: 20px;">Desde $<span id="precio">0</span> mínimo</div>

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

        // Validar formulario y prevenir envío si no es válido
        $('#formularioCita').on('submit', function(e) {
            var campos = $('input[required], select[required], textarea[required]');
            var formularioValido = true;

            campos.each(function() {
                if (!$(this).val()) {
                    $(this).css('border', '2px solid red'); // Marcar en rojo los campos vacíos
                    formularioValido = false;
                } else {
                    $(this).css('border', '2px solid blue'); // Mantener borde azul en campos válidos
                }
            });

            // Validar RUT
            var rutInput = $('#rut').val();
            if (!validarRUT(rutInput)) {
                $('#rut').css('border', '2px solid red');
                formularioValido = false;
                alert("El RUT ingresado no es válido. Debe ser en el formato 12345678-9.");
            }

            // Validar Correo
            var correoInput = $('#correo').val();
            if (!validarCorreo(correoInput)) {
                $('#correo').css('border', '2px solid red');
                formularioValido = false;
                alert("El correo electrónico ingresado no es válido. Debe tener el formato nombre@dominio.com.");
            }

            if (!formularioValido) {
                e.preventDefault(); // Prevenir envío si hay errores
                alert('Por favor, completa todos los campos correctamente.');
            }
        });

        // Función de validación de RUT
        function validarRUT(rut) {
            const rutRegex = /^[0-9]{7,8}-[0-9kK]{1}$/;
            return rutRegex.test(rut);
        }

        // Función de validación de correo
        function validarCorreo(correo) {
            const correoRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            return correoRegex.test(correo);
        }
    });
    </script>

    <script>
$(document).ready(function() {
    $('#producto, #servicio').change(function() {
        var servicioSeleccionado = $('#servicio').val();
        var productoSeleccionado = $('#producto').val();
        
        // Verifica si ambos, servicio y producto, han sido seleccionados
        if (servicioSeleccionado && productoSeleccionado) {
            $.ajax({
                url: 'obtener_precio.php',
                method: 'GET',
                data: { servicio: servicioSeleccionado, producto: productoSeleccionado },
                success: function(data) {
                    var precio = JSON.parse(data);
                    var precioFormateado = Math.floor(precio);  // Eliminar los decimales
                    $('#precio').text('' + precioFormateado.toLocaleString('es-ES'));  // Formatear con separador de miles
                    $('#precio-minimo').fadeIn();  // Mostrar el contenedor con un efecto suave
                }
            });
        } else {
            $('#precio-minimo').fadeOut();  // Ocultar el contenedor si no hay selección
        }
    });
});



    </script>

</body>
</html>
