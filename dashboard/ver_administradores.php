<?php
session_start();

// Verificar si el usuario está logueado y es un administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_cargo'] !== 'administrador') {
    header('Location: ../login/login.php');
    exit();
}

// Conectar a la base de datos
include('../conexion.php');

// Obtener todos los administradores
$query = "SELECT id, rut, nombre, email, fecha_registro FROM login WHERE cargo = 'administrador'";
$result = $conex->query($query);
?>

<div class="details">
    <div class="recentOrders">
        <div class="cardHeader">
            <h2>Lista de Administradores</h2>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>RUT</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Fecha de Registro</th>
                    <th>Bajar Cargo</th> <!-- Nuevo campo para bajar el cargo -->
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
                        <!-- Botón para abrir el modal de cambio de cargo -->
                        <button class="btn-bajar-cargo" data-id="<?php echo $row['id']; ?>" data-nombre="<?php echo $row['nombre']; ?>">
                            Bajar a Cliente
                        </button>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal para bajar cargo -->
<div id="modalBajarCargo" class="modal">
    <div class="modal-content">
        <span class="cerrar-modal" id="cerrarModalBajarCargo">&times;</span>
        <h2>Bajar a Cliente</h2>
        <form id="formBajarCargo">
            <input type="hidden" id="adminId" name="userId"> <!-- Cambia el name a userId -->
            <p>¿Estás seguro que deseas bajar a <span id="adminNombre"></span> a cliente?</p>
            <label for="password">Contraseña actual:</label>
            <input type="password" id="password" name="password" required>
            <button type="submit">Confirmar</button>
        </form>
        <div id="mensaje-bajar-cargo"></div>
    </div>
</div>

