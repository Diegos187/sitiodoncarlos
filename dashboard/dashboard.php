<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login/login.php');
    exit();
}

// Verificar que el usuario sea un cliente
if ($_SESSION['user_cargo'] !== 'cliente') {
    header('Location: admin_dashboard.php');
    exit();
}

// Conectar a la base de datos
include('../conexion.php');

// Obtener las citas asociadas al usuario, ordenadas por estado
$rut_cliente = $_SESSION['user_rut'];
$query_citas = "
    SELECT f.*, c.rut_cliente, s.tipo_servicio, p.tipo_producto, h.fecha, h.hora_disponible
    FROM Formulario f
    INNER JOIN Citas c ON f.id_form = c.id_form
    INNER JOIN Servicio s ON f.id_servicio = s.id_servicio
    INNER JOIN Producto p ON f.id_producto = p.id_producto
    INNER JOIN Horario h ON f.id_horario = h.id_horario
    WHERE c.rut_cliente = ?
    ORDER BY 
        CASE 
            WHEN f.estado = 'pendiente' THEN 1
            WHEN f.estado = 'confirmado' THEN 2
            WHEN f.estado = 'cancelado' THEN 3
        END, h.fecha, h.hora_disponible
";
$stmt = $conex->prepare($query_citas);
$stmt->bind_param('s', $rut_cliente);
$stmt->execute();
$result_citas = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Cliente</title>
    <link rel="stylesheet" href="./dashboard.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>
<body>
    

<header>
    <div class="menu">
        <a href="../index.html" class="logo" style="color:white;">DC</a>
        <nav>
            <a href="../index.html">Volver al Inicio</a>
            <a href="./logout.php">Logout</a>
            <span class="config-icon" id="config-icon">&#9881;</span>
        </nav>
    </div>
</header>

<div id="main-content">
    <main>
        <h1>Bienvenido, <?php echo $_SESSION['user_name']; ?></h1>
        <h2>Historial de Citas</h2>

        <table>
            <thead>
                <tr>
                    <th>#Cita</th>
                    <th>Servicio</th>
                    <th>Producto</th>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Estado</th>
                    <th>Detalles</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($cita = $result_citas->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $cita['id_form']; ?></td>
                    <td><?php echo $cita['tipo_servicio']; ?></td>
                    <td><?php echo $cita['tipo_producto']; ?></td>
                    <td><?php echo $cita['fecha']; ?></td>
                    <td><?php echo $cita['hora_disponible']; ?></td>
                    <td><?php echo $cita['estado']; ?></td>
                    <td>
                        <button class="btn-detalles" onclick="mostrarDetalles(<?php 
                            echo htmlspecialchars(json_encode([
                                'id_form' => $cita['id_form'],
                                'nombre' => $cita['nombre'],
                                'apellido' => $cita['apellido'],
                                'rut' => $cita['rut'],
                                'correo' => $cita['correo'],
                                'direccion' => $cita['direccion'],
                                'telefono' => $cita['telefono'],
                                'detalles' => $cita['detalles'],
                                'tipo_servicio' => $cita['tipo_servicio'],
                                'tipo_producto' => $cita['tipo_producto'],
                                'fecha' => $cita['fecha'],
                                'hora' => $cita['hora_disponible']
                            ]), ENT_QUOTES, 'UTF-8');
                        ?>)">Ver Detalles</button>
                    </td>
                    <td>
                        <?php if ($cita['estado'] == 'pendiente' || $cita['estado'] == 'confirmado') { ?>
                            <button class="btn-detalles" onclick="abrirModalReagendar(<?php echo $cita['id_form']; ?>)">Reagendar</button> |
                            <a href="cancelar.php?id_form=<?php echo $cita['id_form']; ?>" class="boton-accion">Cancelar</a>
                        <?php } else { ?>
                            <span>Cita cancelada</span>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </main>
</div>


<!-- Modal para configuración de cuenta -->
<div id="config-modal" class="modal">
    <div class="modal-content">
        <span class="close" id="close-modal">&times;</span>
        <h2>Configuración de Cuenta</h2>
        <p style="margin-bottom: 20px; text-align:center;">Rellene solo los campos que desea actualizar</p>
        
        <!-- Formulario de configuración de cuenta -->
        <form id="formConfiguracion" action="procesar_configuracion.php" method="POST">
            
            <!-- Campo de nombre de usuario -->
            <div class="input-container">
                <label for="nuevo_nombre">Nuevo nombre de usuario:</label>
                <input type="text" name="nuevo_nombre" id="nuevo_nombre" placeholder="Ingrese nuevo nombre de usuario">
            </div>   

            <!-- Campo de correo electrónico -->
            <div class="input-container">
                <label for="nuevo_correo">Nuevo correo electrónico:</label>
                <input type="email" name="nuevo_correo" id="nuevo_correo" placeholder="Ingrese nuevo correo electrónico">
            </div>

            <!-- Confirmar correo electrónico -->
            <div class="input-container">
                <label for="confirmar_correo">Confirmar nuevo correo electrónico:</label>
                <input type="email" name="confirmar_correo" id="confirmar_correo" placeholder="Confirme el nuevo correo">
            </div>

            <!-- Campo de contraseña actual -->
            <div class="input-container">
                <label for="password_actual">Contraseña anterior (Solo para nueva contraseña):</label>
                <input type="password" name="password_actual" id="password_actual" placeholder="Ingrese su contraseña actual">
            </div>

            <!-- Campo de nueva contraseña -->
            <div class="input-container">
                <label for="nueva_password">Nueva contraseña:</label>
                <input type="password" name="nueva_password" id="nueva_password" placeholder="Ingrese nueva contraseña">
            </div>
            
            <!-- Contenedor para mensajes de error y éxito -->

            <div id="mensaje-error" class="mensaje-reagendar"></div>

            
            <button type="submit" class="btn">Actualizar</button>
        </form>
    </div>
</div>






<!-- Modal para reagendar -->
<div id="modalReagendar" class="modal">
    <div class="modal-content">
        <span class="close" id="closeReagendar">&times;</span>
        <h2>Reagendar Cita</h2>
        <form id="formReagendar">
            <input type="hidden" id="id_form" name="id_form">
            
            <!-- Selección de la fecha -->
            <div class="input-container">
                <label for="fecha">Fecha</label>
                <input type="date" id="fecha" name="fecha" min="<?php echo date('Y-m-d'); ?>" required>
            </div>
            
            <!-- Selección del horario -->
            <label for="horario">Hora Disponible</label>
            <div class="option-c">
                <select id="horario" name="horario" style="border: none;" required>
                    <option value="">Selecciona una fecha primero</option>
                </select>
            </div>

            <!-- Contenedor para el mensaje de estado -->
            <div id="mensaje-reagendar" class="mensaje-reagendar"></div>

            <button type="submit" class="btn">Guardar</button>
        </form>
    </div>
</div>


<!-- Modal para mostrar detalles -->
<div id="modalDetalles" class="modal-detalles">
    <div class="modal-contenido-detalles">
        <span class="cerrar-detalles">&times;</span>
        <h2>Detalles de la Cita</h2>
        <div id="contenidoDetalles"></div>
    </div>
</div>

<script src="./dashboard.js"></script>
<script>
// Script para abrir el modal de reagendar
var modalReagendar = document.getElementById("modalReagendar");
var closeReagendar = document.getElementById("closeReagendar");

function abrirModalReagendar(id_form) {
    document.getElementById('id_form').value = id_form;
    modalReagendar.style.display = "block";
}

closeReagendar.onclick = function() {
    modalReagendar.style.display = "none";
}

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

// Enviar los datos del modal usando AJAX
document.getElementById("formReagendar").onsubmit = function(e) {
    e.preventDefault();

    var formData = new FormData(this);
    var mensajeReagendar = document.getElementById('mensaje-reagendar');

    // Mostrar el mensaje de "Por favor, espere"
    mensajeReagendar.textContent = "Por favor, espere y no cierre la ventana...";
    mensajeReagendar.classList.remove('exito', 'error'); // Remover estilos anteriores
    mensajeReagendar.classList.add('procesando'); // Mostrar mensaje en color naranja

    var xhr = new XMLHttpRequest();
    xhr.open("POST", "reagendar.php", true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);
            if (response.success) {
                mensajeReagendar.textContent = "¡Su cita fue reagendada exitosamente!";
                mensajeReagendar.classList.remove('procesando');
                mensajeReagendar.classList.add('exito'); // Cambiar a mensaje verde

                // Actualizar la tabla o cerrar el modal después de un tiempo
                setTimeout(function() {
                    window.location.reload(); // Recargar la página después de 2 segundos
                }, 2000);
            } else {
                mensajeReagendar.textContent = "Error: " + response.message;
                mensajeReagendar.classList.remove('procesando');
                mensajeReagendar.classList.add('error'); // Mostrar mensaje de error en rojo
            }
        } else {
            mensajeReagendar.textContent = "Error en la solicitud.";
            mensajeReagendar.classList.remove('procesando');
            mensajeReagendar.classList.add('error'); // Mostrar mensaje de error en rojo
        }
    };
    xhr.send(formData);
};


// Script para el modal de detalles (igual al anterior)
var modalDetalles = document.getElementById("modalDetalles");
var spanDetalles = document.getElementsByClassName("cerrar-detalles")[0];

spanDetalles.onclick = function() {
    modalDetalles.style.display = "none";
}

window.onclick = function(event) {
    if (event.target == modalDetalles) {
        modalDetalles.style.display = "none";
    }
}

function mostrarDetalles(detallesCita) {
    var contenidoDetalles = document.getElementById("contenidoDetalles");
    contenidoDetalles.innerHTML = `
        <p><strong>Número de Cita:</strong> ${detallesCita.id_form}</p>
        <p><strong>Nombre:</strong> ${detallesCita.nombre} ${detallesCita.apellido}</p>
        <p><strong>RUT:</strong> ${detallesCita.rut}</p>
        <p><strong>Correo:</strong> ${detallesCita.correo}</p>
        <p><strong>Dirección:</strong> ${detallesCita.direccion}</p>
        <p><strong>Teléfono:</strong> ${detallesCita.telefono}</p>
        <p><strong>Detalles:</strong> ${detallesCita.detalles}</p>
        <p><strong>Servicio:</strong> ${detallesCita.tipo_servicio}</p>
        <p><strong>Producto:</strong> ${detallesCita.tipo_producto}</p>
        <p><strong>Fecha:</strong> ${detallesCita.fecha}</p>
        <p><strong>Hora:</strong> ${detallesCita.hora}</p>
    `;
    modalDetalles.style.display = "block";
}
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const formConfiguracion = document.getElementById('formConfiguracion');
    const mensajeReagendar = document.getElementById('mensaje-error'); // Reutilizar el mismo contenedor para mensajes
    const nuevoCorreoInput = document.getElementById('nuevo_correo');
    const confirmarCorreoInput = document.getElementById('confirmar_correo');

    formConfiguracion.onsubmit = function (e) {
        e.preventDefault();

        // Limpiar mensajes antes de validar
        mensajeReagendar.textContent = "";
        mensajeReagendar.classList.remove("exito", "error", "procesando");

        // Validar que los correos coincidan
        if (nuevoCorreoInput.value && nuevoCorreoInput.value !== confirmarCorreoInput.value) {
            mensajeReagendar.textContent = "Los correos electrónicos no coinciden.";
            mensajeReagendar.classList.add("error");
            return;
        }

        const formData = new FormData(formConfiguracion);

        // Mostrar mensaje de espera
        mensajeReagendar.textContent = "Por favor, espere y no cierre la ventana...";
        mensajeReagendar.classList.add("procesando");

        const xhr = new XMLHttpRequest();
        xhr.open("POST", "procesar_configuracion.php", true);
        xhr.onload = function () {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    mensajeReagendar.textContent = response.message;
                    mensajeReagendar.classList.remove("procesando");
                    mensajeReagendar.classList.add("exito"); // Mostrar mensaje en verde

                    // Recargar la página después de 5 segundos
                    setTimeout(function () {
                        window.location.reload();
                    }, 5000);
                } else {
                    mensajeReagendar.textContent = response.message;
                    mensajeReagendar.classList.remove("procesando");
                    mensajeReagendar.classList.add("error"); // Mostrar mensaje en rojo
                }
            } else {
                mensajeReagendar.textContent = "Error en la solicitud. Inténtelo de nuevo.";
                mensajeReagendar.classList.remove("procesando");
                mensajeReagendar.classList.add("error"); // Mostrar mensaje de error
            }
        };
        xhr.send(formData);
    };
});
</script>



<footer>
    <div class="menu-footer">
        <a href="../index.html">Volver al Inicio</a>
        <a href="./logout">Logout</a>
    </div>
</footer>

</body>
</html>
