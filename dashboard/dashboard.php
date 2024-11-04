<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login/login.php');
    exit();
}

// Verificar que el usuario sea un cliente
if ($_SESSION['user_cargo'] !== 'cliente') {
    header('Location: ../adminDash/admin_dashboard.php');
    exit();
}

// Conectar a la base de datos
include('../conexion.php');

// Obtener las citas asociadas al usuario, ordenadas por estado
$rut_cliente = $_SESSION['user_rut'];
$query_citas = "
    SELECT f.*, c.rut_cliente, s.tipo_servicio, p.tipo_producto, h.fecha, h.hora_disponible, pr.monto, pr.comentario, pr.estado AS estado_presupuesto, pr.fecha_creacion, 
           (SELECT COUNT(*) FROM Presupuesto WHERE id_form = f.id_form) AS num_presupuestos
    FROM Formulario f
    INNER JOIN Citas c ON f.id_form = c.id_form
    INNER JOIN Servicio s ON f.id_servicio = s.id_servicio
    INNER JOIN Producto p ON f.id_producto = p.id_producto
    INNER JOIN Horario h ON f.id_horario = h.id_horario
    LEFT JOIN Presupuesto pr ON f.id_form = pr.id_form AND pr.fecha_creacion = (
        SELECT MAX(fecha_creacion) FROM Presupuesto WHERE id_form = f.id_form
    ) -- Obtener el presupuesto más reciente
    WHERE c.rut_cliente = ?
    ORDER BY f.id_form DESC
        
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
        <h2>Historial de Citas (<?php echo $_SESSION['user_rut'];?>)</h2>
        <?php if ($result_citas->num_rows > 0): ?>
        <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>#Cita</th>
                    <th>Servicio</th>
                    <th>Producto</th>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Estado</th>
                    <th>Detalles</th>
                    <th>Presupuesto</th> 
                    <th>Acciones</th>
                    <th>Mensajes</th>
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
                                        <td>
    <?php 
        // Asignar la clase de estilo según el estado
        $claseEstado = '';
        switch ($cita['estado']) {
            case 'pendiente':
                $claseEstado = 'estado-pendiente';
                break;
            case 'confirmado':
                $claseEstado = 'estado-confirmado';
                break;
            case 'en proceso':
                $claseEstado = 'estado-en-proceso';
                break;
            case 'finalizado':
                $claseEstado = 'estado-finalizado';
                break;
            case 'cancelado':
                $claseEstado = 'estado-cancelado';
                break;
        }
    ?>
    <!-- Aplicar la clase al estado -->
    <span class="<?php echo $claseEstado; ?>">
        <?php echo ($cita['estado']); ?>
    </span>
</td>

                    <!-- Detalles -->
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

                    <!-- Presupuesto -->
                    <td>
                        <?php if ($cita['estado'] === 'cancelado') { ?>
                            <span>No disponible</span>
                        <?php } elseif (!empty($cita['monto'])) { ?>
                            <button class="btn-presupuesto" data-idform="<?php echo $cita['id_form']; ?>"
                                    data-monto="<?php echo $cita['monto']; ?>"
                                    data-comentario="<?php echo htmlspecialchars($cita['comentario'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-estado="<?php echo $cita['estado_presupuesto']; ?>">
                                <?php 
                                if ($cita['estado_presupuesto'] === 'aceptado') {
                                    echo 'Ver Presupuesto Aceptado';
                                } elseif ($cita['estado_presupuesto'] === 'rechazado') {
                                    echo 'Ver Presupuesto Rechazado';
                                } else {
                                    echo 'Ver Presupuesto';
                                }
                                ?>
                            </button>
                        <?php } else { ?>
                            <span>No hay presupuesto</span>
                        <?php } ?>
                    </td>
                    
                    <!-- Acciones -->
                    <td>
                        <?php if ($cita['estado'] === 'finalizado') { ?>
                            <span>Cita finalizada</span>
                        <?php } elseif ($cita['estado'] === 'en proceso') { ?>
                            <span>Servicio en proceso</span>
                        <?php } elseif ($cita['estado'] === 'confirmado') { ?>
                            <a href="javascript:void(0);" onclick="abrirModalCancelar(<?php echo $cita['id_form']; ?>)" class="boton-accion">Cancelar</a>

                        <?php } elseif ($cita['estado'] == 'pendiente' || $cita['estado'] == 'confirmado') { ?>
                            <button class="btn-detalles" onclick="abrirModalReagendar(<?php echo $cita['id_form']; ?>)">Reagendar</button>
                            <a href="javascript:void(0);" onclick="abrirModalCancelar(<?php echo $cita['id_form']; ?>)" class="boton-accion">Cancelar</a>

                        <?php } else { ?>
                            <span>Cita cancelada</span>
                        <?php } ?>
                    </td>
                    <td>
                        <button class="btn-chat" data-idform="<?php echo $cita['id_form']; ?>" data-estado="<?php echo $cita['estado']; ?>">
                            Chat
                        </button>
                    </td>


                </tr>
            <?php } ?>
            </tbody>
        </table>
        <?php else: ?>
            <p>No existen registros de citas.</p>
        <?php endif; ?>
        <button onclick="location.href = '../formulario/formulario.php'" class="btn-agenda">Agenda aqui</button>
        </div>
    </main>
</div>

<!-- Modal para ver el presupuesto -->
<div id="modalPresupuesto" class="modal-detalles">
    <div class="modal-contenido-detalles">
        <span class="cerrar-presupuesto">&times;</span>
        <h2>Presupuesto</h2>
        
        <!--Contenedor para mensajes de estado (procesando, éxito, error) -->

        
        <p><strong>Monto:</strong> $<span id="presupuestoMonto"></span></p>
        <p><strong>Comentario:</strong> <span id="presupuestoComentario"></span></p> <!-- Mostrar comentario -->
        <p><strong>Estado:</strong> <span id="presupuestoEstado"></span></p>
        <div id="mensaje-error" class="mensaje-reagendar"></div> 
        <div id="presupuestoAcciones">
            <button id="btnAceptarPresupuesto" class="btn" style="margin-bottom: 10px; margin-top:10px">Aceptar</button>
            <button id="btnRechazarPresupuesto" class="btn">Rechazar</button>
        </div>
    </div>
</div>






<!-- Modal para configuración de cuenta -->
<div id="config-modal" class="modal">
    <div class="modal-content-confi">
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

            <div id="mensaje-configuracion" class="mensaje-reagendar"></div>

            
            <button type="submit" class="btn">Actualizar</button>
        </form>
    </div>
</div>

<div id="modalCancelar" class="modal">
    <div class="modal-content-cancelar">
        <span class="close" id="closeCancelar">&times;</span>
        <p>¿Está seguro de que desea cancelar esta cita?</p>
        <button class="confirCan" id="confirmarCancelar">Si</button>
        <button class="rechaCan" id="cancelarCancelar">No</button>
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
            <button type="submit" class="btn">Enviar</button>
        </form>

        <div id="mensaje-chat" class="mensaje-chat"></div>
    </div>
</div>


<script src="./dashboard.js"></script>

<footer>
    <div class="menu-footer">
        <a href="../index.html">Volver al Inicio</a>
        <a href="./logout.php">Logout</a>
    </div>
</footer>

</body>
</html>
