<?php
session_start();

// Verificar si el usuario está logueado y es un administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_cargo'] !== 'administrador') {
    header('Location: ../login/login.php');
    exit();
}

// Conectar a la base de datos
include('../conexion.php');

// Obtener todos los clientes
$query = "SELECT id, rut, nombre, email, fecha_registro FROM login WHERE cargo = 'cliente'";
$result = $conex->query($query);
?>

<div class="details">
    <div class="recentOrders">
        <div class="cardHeader">
            <h2>Lista de Clientes</h2>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>RUT</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Fecha de Registro</th>
                    <th>Citas</th>
                    <th>Cambiar Cargo</th>
                    <th>Eliminar Cliente</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['rut']; ?></td>
                    <td><?php echo $row['nombre']; ?></td>
                    <td><?php echo $row['email']; ?></td>
                    <td><?php echo $row['fecha_registro']; ?></td>
                    <td>
                        <button class="btn-ver-citas" onclick="verCitas('<?php echo $row['rut']; ?>')">
                            <ion-icon name="search-outline"></ion-icon>
                        </button>
                    </td>
                    <td>
                        <!-- Botón para abrir el modal de cambio de cargo -->
                        <button class="btn-cambiar-cargo" data-id="<?php echo $row['id']; ?>" data-nombre="<?php echo $row['nombre']; ?>">
                            Cambiar a Administrador
                        </button>
                    </td>
                    <td>
                        <button class="btn-eliminar-cliente" data-id="<?php echo $row['id']; ?>" data-rut="<?php echo $row['rut']; ?>" data-nombre="<?php echo $row['nombre']; ?>">
                            Eliminar Cliente
                        </button>
                    </td>

                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal para cambiar cargo -->
<div id="modalCambiarCargo" class="modal">
    <div class="modal-content">
        <span class="cerrar-modal" id="cerrarModal">&times;</span>
        <h2>Cambiar a Administrador</h2>
        <form id="formCambiarCargo">
            <input type="hidden" id="clienteId" name="userId"> 
            <p>¿Estás seguro que deseas cambiar a <span id="clienteNombre"></span> a administrador?</p>
            <label for="password">Contraseña actual:</label>
            <input type="password" id="password" name="password" required>
            <button type="submit">Confirmar</button>
        </form>
        <div id="mensaje-cambio-cargo"></div>
    </div>
</div>

<div id="modalEliminarCliente" class="modal">
    <div class="modal-content">
        <span class="cerrar-modal" id="cerrarModalEliminar">&times;</span>
        <h2>Eliminar Cliente</h2>
        <form id="formEliminarCliente">
            <input type="hidden" id="clienteIdEliminar" name="clienteId">
            <input type="hidden" id="rutClienteEliminar" name="rutCliente">
            <p>¿Está seguro de que desea eliminar al cliente <span id="nombreClienteEliminar"></span>?</p>
            <label for="password">Contraseña del Administrador:</label>
            <input type="password" id="passwordAdmin" name="password" required>
            <button type="submit">Confirmar Eliminación</button>
        </form>
        <div id="mensaje-eliminacion"></div>
    </div>
</div>



