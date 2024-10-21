<?php
session_start();

// Verificar si el usuario está logueado y es un administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_cargo'] !== 'administrador') {
    header('Location: ../login/login.php');
    exit();
}

?>

<form id="configuracionForm" method="POST">
    <h2>Configuración de cuenta</h2>
    <p>Rellene solo los campos que desea actualizar</p>
    <div>
        <label for="nuevo_nombre">Nuevo nombre de usuario:</label>
        <input type="text" id="nuevo_nombre" name="nuevo_nombre">
    </div>

    <div>
        <label for="nuevo_correo">Nuevo correo electrónico:</label>
        <input type="email" id="nuevo_correo" name="nuevo_correo">
    </div>

    <div>
        <label for="confirmar_correo">Confirmar correo electrónico:</label>
        <input type="email" id="confirmar_correo" name="confirmar_correo">
    </div>

    <div>
        <label for="password_actual">Contraseña actual:</label>
        <input type="password" id="password_actual" name="password_actual">
    </div>

    <div>
        <label for="nueva_password">Nueva contraseña:</label>
        <input type="password" id="nueva_password" name="nueva_password">
    </div>

    <button type="submit">Guardar cambios</button>

    <p id="mensaje-configuracion"></p> <!-- Aquí se mostrará el mensaje de éxito o error -->
</form>
