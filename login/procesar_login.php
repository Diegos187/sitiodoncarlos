<?php
session_start();
include('../conexion.php');

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
            $_SESSION['user_cargo'] = $row['cargo'];  // Guardar el cargo en la sesión
            $_SESSION['user_rut'] = $row['rut'];  // Guardar el RUT en la sesión

            // Verificar el cargo del usuario como cadena de texto
            if ($row['cargo'] === 'administrador') {
                // Si el cargo es 'administrador', redirigir al dashboard de administrador
                header('Location: ../dashboard/admin_dashboard.php');
                exit(); // Asegúrate de salir después de la redirección
            } elseif ($row['cargo'] === 'cliente') {
                // Si el cargo es 'cliente', redirigir al dashboard de cliente
                header('Location: ../dashboard/dashboard.php');
                exit(); // Asegúrate de salir después de la redirección
            } else {
                $_SESSION['error'] = "Cargo no reconocido.";
                header('Location: login.php');
                exit();
            }
        } else {
            $_SESSION['error'] = "Contraseña incorrecta.";
            header('Location: ./login.php');
            exit();
        }
    } else {
        $_SESSION['error'] = "El correo no está registrado o la cuenta no está verificada.";
        header('Location: ./login.php');
        exit();
    }

    $stmt->close();
}

mysqli_close($conex);
?>
