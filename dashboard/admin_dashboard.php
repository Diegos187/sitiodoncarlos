<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login/login.php');
    exit();
}

// Verificar que el usuario sea un administrador
if ($_SESSION['user_cargo'] !== 'administrador') {
    header('Location: ./dashboard.php');
    exit();
}

$nombreUsuario = $_SESSION['user_name'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrador</title>
    <!-- ======= Styles ====== -->
    <link rel="stylesheet" href="./admin.css">
</head>

<body>
    <!-- =============== Navigation ================ -->
    <div class="container">
        <div class="navigation">
            <ul>
                <li>
                    <a href="#">
                        <span class="title" style="text-align: center;"> Admin  <?php echo htmlspecialchars($nombreUsuario); ?></span>
                    </a>
                </li>

                <li>
                    <a href="./admin_dashboard.php" id="dashboard">
                        <span class="icon">
                            <ion-icon name="home-outline"></ion-icon>
                        </span>
                        <span class="title">Dashboard</span>
                    </a>
                </li>

                <li>
                    <a href="#" id="ver-clientes">
                        <span class="icon">
                            <ion-icon name="people-outline"></ion-icon>
                        </span>
                        <span class="title">Clientes</span>
                    </a>
                </li>

                <li>
                    <a href="#" id="ver-citas">
                        <span class="icon">
                            <ion-icon name="chatbubble-outline"></ion-icon>
                        </span>
                        <span class="title">Todas las citas</span>
                    </a>
                </li>

                <li>
                    <a href="#" id="ver-administradores">
                        <span class="icon">
                            <ion-icon name="person-outline"></ion-icon>
                        </span>
                        <span class="title">Administradores</span>
                    </a>
                </li>

                <li>
                    <a href="#" id="insertar-horarios">
                        <span class="icon">
                            <ion-icon name="time-outline"></ion-icon>
                        </span>
                        <span class="title">Insertar Horarios</span>
                    </a>
                </li>



<li>
    <a href="#" id="configuracion">
        <span class="icon">
            <ion-icon name="settings-outline"></ion-icon>
        </span>
        <span class="title">Configuración</span>
    </a>
</li>

<li>
                    <a href="generar_reporte.php" id="descargar-reporte">
                        <span class="icon">
                            <ion-icon name="cloud-download-outline"></ion-icon>
                        </span>
                        <span class="title">Descargar Reporte</span>
                    </a>
                </li>


                <li>
                    <a href="./logout.php">
                        <span class="icon">
                            <ion-icon name="log-out-outline"></ion-icon>
                        </span>
                        <span class="title">Cerrar sesión</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- ========================= Main ==================== -->
        <div class="main">
    <div class="topbar">
        <div class="toggle">
            <ion-icon name="menu-outline"></ion-icon>
        </div>
        <!-- Icono de notificaciones -->
        <div class="notifications">
    <ion-icon name="notifications-outline" id="icono-notificaciones" style="font-size: 30px;"></ion-icon>
    <span id="punto-notificacion" class="punto-notificacion"></span>
    <div id="notificaciones-dropdown" class="notificaciones-dropdown">
        <ul id="lista-notificaciones"></ul>
    </div>
</div>



        <div class="user">
            <img src="../css/imagenes/adminn.png" alt="">
        </div>
    </div>

  <!-- Tarjetas del Dashboard -->
  <div class="cardBox">
                <!-- Tarjeta para Total de Clientes -->
                <div class="card">
                    <div>
                        <div id="total-clientes" class="numbers">0</div>
                        <div id="total-clientes-nombre" class="cardName">Cargando...</div>
                    </div>
                    <div class="iconBx">
                        <ion-icon name="people-outline"></ion-icon>
                    </div>
                </div>

                <!-- Tarjeta para Total de Citas -->
                <div class="card">
                    <div>
                        <div id="total-citas-en-proceso" class="numbers">0</div>
                        <div id="total-citas-en-proceso-nombre" class="cardName">Cargando...</div>
                    </div>
                    <div class="iconBx">
                        <ion-icon name="calendar-outline"></ion-icon>
                    </div>
                </div>

                <!-- Tarjeta para Citas Pendientes -->
                <div class="card">
                    <div>
                        <div id="total-citas-pendientes" class="numbers">0</div>
                        <div id="total-citas-pendientes-nombre" class="cardName">Cargando...</div>
                    </div>
                    <div class="iconBx">
                        <ion-icon name="time-outline"></ion-icon>
                    </div>
                </div>

                <!-- Tarjeta para Chats Pendientes -->
                <div class="card">
                    <div>
                        <div id="total-chats-pendientes" class="numbers">0</div>
                        <div id="total-chats-pendientes-nombre" class="cardName">Cargando...</div>
                    </div>
                    <div class="iconBx">
                        <ion-icon name="chatbubbles-outline"></ion-icon>
                    </div>
                </div>
            </div>




            <!-- Contenido inicial -->
            <div id="content">
            <div class="recentOrders">
                <h2 style="text-align: center;">Últimas Citas</h2>
                <table class="table" style="margin: 30px;">
                    <thead>
                        <tr>
                            <th>#Cita</th>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Correo</th>
                            <th>Teléfono</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-ultimas-citas">
                        <!-- Aquí se insertarán las filas de citas -->
                    </tbody>
                </table>

                <h2 style="text-align: center;">Últimos Clientes Registrados</h2>
                <table class="table" style="margin: 30px;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>RUT</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Fecha de Registro</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-ultimos-clientes">
                        <!-- Aquí se insertarán las filas de clientes -->
                    </tbody>
                </table>
            </div>
        </div>
        </div>
    </div>

    <script src="./main.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</body>
</html>