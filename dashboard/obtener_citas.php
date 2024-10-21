<?php
session_start();

// Verificar si el usuario está logueado y es un administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_cargo'] !== 'administrador') {
    header('Location: ../login/login.php');
    exit();
}

// Conectar a la base de datos
include('../conexion.php');

// Obtener el RUT del cliente
$rut_cliente = isset($_GET['rut']) ? $_GET['rut'] : '';

if ($rut_cliente) {
    // Obtener las citas asociadas al cliente y el presupuesto más reciente
    $query = "
    SELECT f.*, pr.monto, pr.comentario, pr.estado AS estado_presupuesto, h.fecha, h.hora_disponible, c.rut_cliente,
           CASE 
               WHEN c.id_form IS NOT NULL THEN 'registrado' 
               ELSE 'no_registrado' 
           END AS usuario_estado,
           (SELECT COUNT(*) FROM Mensajes WHERE id_form = f.id_form AND leido = 0 AND id_usuario IN 
               (SELECT id FROM login WHERE cargo = 'cliente')) AS mensajes_no_leidos  -- Mensajes no leídos del cliente
    FROM Formulario f
    INNER JOIN Citas c ON f.id_form = c.id_form
    LEFT JOIN Horario h ON f.id_horario = h.id_horario
    LEFT JOIN (
        SELECT id_form, monto, comentario, estado 
        FROM Presupuesto
        WHERE fecha_creacion = (
            SELECT MAX(fecha_creacion) 
            FROM Presupuesto AS p2 
            WHERE p2.id_form = Presupuesto.id_form
        )
    ) pr ON f.id_form = pr.id_form
    WHERE c.rut_cliente = ?
    ORDER BY f.id_form DESC
";


    $stmt = $conex->prepare($query);
    $stmt->bind_param('s', $rut_cliente);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    echo "No se encontró un cliente válido.";
    exit();
}
?>

<div class="details">
    <div class="recentOrders">
        <div class="cardHeader">
            <h2>Citas del Cliente</h2>
            <button class="btn" onclick="loadContent('ver_clientes.php')">Volver a Clientes</button>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>#Cita</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Correo</th>
                    <th>Teléfono</th>
                    <th>Estado</th>
                    <th>Detalles</th>
                    <th>Presupuesto</th> <!-- Nueva columna para generar presupuesto -->
                    <th>Acciones</th> <!-- Nueva columna para Acciones -->
                    <th>Cambiar Estado</th>
                    <th>Mensajes</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $row['id_form']; ?></td>
                    <td><?php echo $row['nombre']; ?></td>
                    <td><?php echo $row['apellido']; ?></td>
                    <td><?php echo $row['correo']; ?></td>
                    <td><?php echo $row['telefono']; ?></td>
                    <td><?php echo $row['estado']; ?></td>
                    <td>
                        <button class="btn-detalles" data-detalles='<?php echo json_encode($row); ?>'>
                            Detalles
                        </button>
                    </td>
                    <td>
    <?php if ($row['usuario_estado'] == 'no_registrado') { ?>
        <!-- Mostrar que el usuario no está registrado -->
        <p>Usuario no registrado</p>
    <?php } elseif ($row['estado'] === 'cancelado' || $row['estado'] === 'pendiente') { ?>
        <p>No disponible</p>
    <?php } elseif (!empty($row['monto']) && $row['estado_presupuesto'] == 'pendiente') { ?>
        <button class="btn-ver-presupuesto-pendiente" data-idform="<?php echo $row['id_form']; ?>" data-monto="<?php echo $row['monto']; ?>" data-comentario="<?php echo $row['comentario']; ?>">
        Ver Presupuesto Pendiente
    </button>
    <?php } elseif (!empty($row['monto']) && $row['estado_presupuesto'] == 'aceptado') { ?>
        <!-- Mostrar botón para ver el presupuesto aceptado -->
        <button class="btn-ver-presupuesto" data-idform="<?php echo $row['id_form']; ?>" data-monto="<?php echo $row['monto']; ?>" data-comentario="<?php echo $row['comentario']; ?>">
            Ver Presupuesto Aceptado
        </button>
    <?php } elseif (!empty($row['monto']) && $row['estado_presupuesto'] == 'rechazado') { ?>
        <button class="btn-presupuesto" data-idform="<?php echo $row['id_form']; ?>">
            Generar Nuevo Presupuesto
        </button>
    <?php } else { ?>
        <button class="btn-presupuesto" data-idform="<?php echo $row['id_form']; ?>">
            Generar Presupuesto
        </button>
    <?php } ?>
</td>
                    <td>
            <!-- Verificar que la cita esté en estado pendiente para habilitar reagendar -->
            <?php if ($row['estado'] === 'cancelado' || $row['estado'] === 'finalizado' || $row['estado'] === 'confirmado' || $row['estado'] === 'en proceso') { ?>
                <p>No disponible</p>
            <?php } if ($row['estado'] === 'pendiente') { ?>
                <button class="btn-reagendar" data-idform="<?php echo $row['id_form']; ?>">
                    Reagendar
                </button>
            <?php } ?>
        </td>
        <td>
    <?php if ($row['estado'] === 'cancelado') { ?>
        <p>Cita cancelada</p>
    <?php } elseif ($row['estado'] === 'finalizado') { ?>
        <p>Cita finalizada</p>
    <?php } else { ?>
        <button class="btn-estado" data-idform="<?php echo $row['id_form']; ?>" data-estado="<?php echo $row['estado']; ?>">
    Cambiar Estado
</button>
    <?php } ?>
<!-- Botón para abrir el chat -->
<td>
    <button class="btn-chat" 
        data-idform="<?php echo $row['id_form']; ?>"
        style="background-color: <?php echo ($row['mensajes_no_leidos'] > 0) ? '#FF0000' : '#02B1F4'; ?>">
        Chat
    </button>
</td>

                </tr>
                <?php } ?>

                
            </tbody>
        </table>
    </div>
</div>

<!-- Modal para mostrar detalles de la cita -->
<div id="modalDetalles" class="modal-detalles">
    <div class="modal-contenido-detalles">
        <span class="cerrar-detalles">&times;</span>
        <h2>Detalles de la Cita</h2>
        <div id="contenidoDetalles"></div>
    </div>
</div>

<!-- Modal para generar presupuesto -->
<div id="modalPresupuesto" class="modal-detalles">
    <div class="modal-contenido-detalles">
        <span class="cerrar-presupuesto">&times;</span>
        <h2>Presupuesto</h2>
        <form id="formPresupuesto" action="presupuesto.php" method="POST">
            <input type="hidden" id="id_form_presupuesto" name="id_form">
            <div class="input-container">
            <label for="monto">Monto:</label>
            <input type="number" step="0.01" id="monto" name="monto" required>
            </div>
            <label for="comentario">Comentario:</label>
            <textarea id="comentario" name="comentario"></textarea>
            <!-- Div para mostrar el mensaje de éxito o error -->
            <div id="mensaje-presupuesto" class="mensaje-reagendar"></div>
            <button type="submit" class="btn-mod">Guardar Presupuesto</button>
        </form>

    </div>
</div>

<!-- Modal para ver el presupuesto aceptado -->
<div id="modalPresupuestoAceptado" class="modal-detalles">
    <div class="modal-contenido-detalles">
        <span id="cerrarPresupuestoAceptado" class="cerrar-presupuesto">&times;</span>
        <h2>Presupuesto Aceptado</h2>
        <div id="presupuestoDetalles"></div>
    </div>
</div>

<!-- Modal para ver el presupuesto pendiente -->
<div id="modalPresupuestoPendiente" class="modal-detalles">
    <div class="modal-contenido-detalles">
        <span id="cerrarPresupuestoPendiente" class="cerrar-presupuesto">&times;</span>
        <h2>Presupuesto Pendiente</h2>
        <div id="presupuestoDetallesPendiente"></div> <!-- Aquí se mostrarán los detalles del presupuesto pendiente -->
    </div>
</div>


<!-- Modal para reagendar -->
<div id="modalReagendar" class="modal-detalles">
    <div class="modal-contenido-detalles">
        <span class="cerrar-reagendar">&times;</span>
        <h2>Reagendar Cita</h2>
        <form id="formReagendar" action="admin_reagendar.php" method="POST">
            <input type="hidden" id="id_form_reagendar" name="id_form">

            <!-- Selección de la fecha -->
            <div class="input-container">
            <label for="fecha">Fecha</label>
            <input type="date" id="fecha" name="fecha" min="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <!-- Selección de la hora -->
            <label for="horario">Hora Disponible</label>
            <div class="option-c">
            <select id="horario" name="horario" style="border: none;">
                <option value="">Selecciona una fecha primero</option>
            </select>
            </div>
            <!-- Mensaje para mostrar el estado del reagendamiento -->
            <div id="mensaje-reagendar" class="mensaje-reagendar"></div>

            <button type="submit" class="btn-mod">Confirmar Reagendamiento</button>
        </form>
    </div>
</div>

<!-- Modal para cambiar estado -->
<div id="modalEstado" class="modal-detalles">
    <div class="modal-contenido-detalles">
        <span class="cerrar-estado">&times;</span>
        <h2>Cambiar Estado</h2>
        <form id="formEstado" action="admin_estado.php" method="POST">
            <input type="hidden" id="id_form_estado" name="id_form">
            <label for="nuevo_estado">Seleccionar Estado:</label>
            <div class="option-c">
            <select id="nuevo_estado" name="nuevo_estado" required>
                <option value="pendiente">Pendiente</option>
                <option value="confirmado">Confirmado</option>
                <option value="cancelado">Cancelado</option>
                <option value="en proceso">En proceso</option>
                <option value="finalizado">Finalizado</option>
            </select>
            </div>
            
            <!-- Contenedor para el mensaje de procesando, éxito o error -->
            <div id="mensaje-estado" class="mensaje-reagendar"></div>

            <button type="submit" class="btn-mod">Confirmar Cambio</button>
        </form>
    </div>
</div>

<!-- Modal para el chat -->
<div id="modalChat" class="modal-chat">
    <div class="modal-contenido-chat">
        <span class="cerrar-chat">&times;</span>
        <h2>Chat de la Cita #<span id="chatIdForm"></span></h2>
        
        <!-- Área de mensajes -->
        <div id="chatMessages" class="chat-messages">
            <!-- Aquí se cargarán los mensajes -->
        </div>

        <!-- Formulario para enviar un nuevo mensaje -->
        <form id="formEnviarMensaje">
            <input type="hidden" id="id_form_chat" name="id_form">
            <input type="hidden" id="id_usuario_chat" name="id_usuario" value="<?php echo $_SESSION['user_id']; ?>">
            <textarea id="mensaje_chat" name="mensaje" placeholder="Escribe tu mensaje..." required></textarea>
            <button type="submit" class="btn-enviar-mensaje">Enviar</button>
        </form>

        <div id="mensaje-chat" class="mensaje-chat"></div>
    </div>
</div>




<script>
// Mostrar el modal de presupuesto solo si es necesario
document.querySelectorAll('.btn-presupuesto').forEach(button => {
    button.onclick = function() {
        const idForm = this.getAttribute('data-idform');
        document.getElementById('id_form_presupuesto').value = idForm;
        document.getElementById('modalPresupuesto').style.display = 'block';
    };
});

// Cerrar el modal
document.querySelector(".cerrar-presupuesto").onclick = function() {
    document.getElementById("modalPresupuesto").style.display = "none";
};

// Cerrar modal si se hace clic fuera
window.onclick = function(event) {
    const modalPresupuesto = document.getElementById("modalPresupuesto");
    if (event.target == modalPresupuesto) {
        modalPresupuesto.style.display = "none";
    }
};
</script>

<script>
document.querySelectorAll('.btn-reagendar').forEach(button => {
    button.onclick = function() {
        const idForm = this.getAttribute('data-idform');
        document.getElementById('id_form_reagendar').value = idForm;
        document.getElementById('modalReagendar').style.display = 'block';
    };
});

// Cerrar el modal de reagendar
document.querySelector(".cerrar-reagendar").onclick = function() {
    document.getElementById("modalReagendar").style.display = "none";
};

// Cargar horarios disponibles cuando se seleccione una fecha
document.getElementById('fecha').addEventListener('change', function() {
    const fechaSeleccionada = this.value;
    const horarioSelect = document.getElementById('horario');

    if (fechaSeleccionada) {
        fetch('../formulario/obtener_horarios.php?date=' + fechaSeleccionada)
            .then(response => response.json())
            .then(horarios => {
                horarioSelect.innerHTML = ''; // Limpiar las opciones anteriores
                if (horarios.length > 0) {
                    horarios.forEach(horario => {
                        const option = document.createElement('option');
                        option.value = horario.id_horario;
                        option.textContent = horario.hora_disponible;
                        horarioSelect.appendChild(option);
                    });
                } else {
                    horarioSelect.innerHTML = '<option value="">No hay horarios disponibles</option>';
                }
            });
    }
});

// Cerrar modal si se hace clic fuera
window.onclick = function(event) {
    const modalReagendar = document.getElementById("modalReagendar");
    if (event.target == modalReagendar) {
        modalReagendar.style.display = "none";
    }
};
</script>

<script>
// Mostrar modal de cambiar estado
document.querySelectorAll('.btn-estado').forEach(button => {
    button.onclick = function() {
        const idForm = this.getAttribute('data-idform');
        document.getElementById('id_form_estado').value = idForm;
        document.getElementById('modalEstado').style.display = 'block';
    };
});

// Cerrar el modal de cambiar estado
document.querySelector(".cerrar-estado").onclick = function() {
    document.getElementById("modalEstado").style.display = "none";
};

// Cerrar modal si se hace clic fuera
window.onclick = function(event) {
    const modalEstado = document.getElementById("modalEstado");
    if (event.target == modalEstado) {
        modalEstado.style.display = "none";
    }
};
</script>