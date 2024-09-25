<?php
session_start();
include('conexion.php');

// Verificar si el formulario fue enviado correctamente
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener los datos del formulario
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Consultar el usuario en la base de datos
    $query = "SELECT * FROM login WHERE email = ? AND verificado = 1";
    $stmt = $conex->prepare($query);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Verificar la contraseña
        if (password_verify($password, $row['password'])) {
            // Guardar los datos de sesión
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['nombre'];
            header('Location: dashboard.php');  // Redirigir al dashboard
            exit();
        } else {
            echo "Contraseña incorrecta.";
        }
    } else {
        echo "El correo no está registrado o la cuenta no está verificada.";
    }

    $stmt->close();
}

mysqli_close($conex);
?>
