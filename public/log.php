<?php
    session_start();
    include '../php/conexion_be.php'; // Conexión a la base de datos

    if (!isset($_SESSION['usuario'])) {
        header("location: index.php");
        session_destroy();
        die();
    }

    $usuario = $_SESSION['usuario'];

    // Consultar los datos del usuario
    $stmt = $conexion->prepare("SELECT nombre, apellido, email, img FROM usuarios WHERE usuario = ?");
    $stmt->bind_param('s', $usuario);
    $stmt->execute();
    $result_usuario = $stmt->get_result();

    // Verificar si se encontró el usuario
    if ($result_usuario->num_rows > 0) {
        $user_data = $result_usuario->fetch_assoc();
        $nombre_usuario = $user_data['nombre'] . " " . $user_data['apellido'];
        $email_usuario = $user_data['email'];
        $img_usuario = $user_data['img'] ? $user_data['img'] : './images/avatar_ph.png';
    } else {
        // Redirigir si no se encuentran datos
        header("location: index.php");
        session_destroy();
        die();
    }

    $stmt->close();

    // Obtener registros de la tabla movimientos_log
    $query = "SELECT id, fecha_hora, usuario, accion, detalle FROM movimientos_log ORDER BY id DESC";
    $result = $conexion->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log - StaffBook</title>
    <link rel="stylesheet" href="../css/logStyles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <!--Header de la pagina-->
    <header class="header">
        <div class="left-section">
            <div class="menu-icon" onclick="toggleMenuSidebar()">
                <span>&#9776;</span>
            </div>
            <div class="logo">
                <form action="../php/redirigirMain.php" method="POST" class="staffbook-button">
                    <button type="submit" class="staffbook-button">
                        <h1>StaffBook</h1>
                    </button>
                </form>
            </div>
        </div>
        <div class="profile" onclick="toggleSidebar()">
            <img src="../images/avatar_ph.png" alt="Profile Picture" class="profile-img">
            <span><?php echo htmlspecialchars($nombre_usuario); ?></span>
        </div>

    </header>

    <!--Sidebar derecho del perfil de usuario-->
    <div class="sidebar-overlay" id="sidebar-overlay" onclick="toggleSidebar()"></div>
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="../images/avatar_ph.png" alt="Profile Picture" class="sidebar-img">
            <div>
                <h3><?php echo htmlspecialchars($nombre_usuario); ?></h3>
                <p><?php echo htmlspecialchars($email_usuario); ?></p>
            </div>
        </div>
        <ul class="sidebar-menu">
            <button class="on"><span>🗂️</span> Historial</button>
            <button onclick="window.location.href='../php/exportar_excel.php'"><span>📊</span> Exportar a Excel</button>
        </ul>
        <button class="logout" onclick="showLogoutConfirmation()"><span>⬅️</span> Cerrar Sesión</button>
    </div>

    <!-- Sidebar izquierdo de menu-->
    <div class="menu-overlay" id="menu-overlay" onclick="toggleMenuSidebar()"></div>
    <div class="menu-sidebar" id="menu-sidebar">
        <div class="menu-header">
            <button class="menu-close" onclick="toggleMenuSidebar()">⬅️</button>
        </div>
        <ul class="menu-items">
            <li>
                <form action="../php/redirigirEmpleado.php" method="POST" style="display: inline;">
                    <button type="submit" style="background: none; border: none; font-size: inherit; cursor: pointer; color: black;">
                        <span>👤➕</span> Agregar Empleado
                    </button>
                </form>
            </li>
            <li>
                <span>📖</span> 
                <a href="public/StaffBook - Guia de usuario.pdf" target="_blank" style="text-decoration: none; color: inherit;">Guía</a>
            </li>
        </ul>
    </div>

    <!-- Ventana de confirmación para cerrar sesión -->
    <div class="confirm-logout-overlay" id="confirm-logout-overlay" onclick="closeLogoutConfirmation()"></div>
    <div class="confirm-logout" id="confirm-logout">
        <p>¿Seguro que quieres cerrar sesión?</p>
        <div class="confirm-buttons">
            <button class="confirm-logout-btn" onclick="window.location.href = '../php/cerrarSesion.php';">Cerrar sesión</button>
            <button class="cancel-logout-btn" onclick="closeLogoutConfirmation()">Cancelar</button>
        </div>
    </div>

    <div class="container">
        <h1>Ultimos 100 Movimientos</h1>
        <table id="movimientosLogTable" border="1" style="width:100%; text-align: left;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Fecha y Hora</th>
                    <th>Usuario</th>
                    <th>Acción</th>
                    <th>Detalle</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['fecha_hora']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['usuario']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['accion']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['detalle']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No hay registros disponibles.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!--Script-->
    <script src="../js/mainScript.js"></script>
</body>

</html>

<?php
// Cerrar la conexión a la base de datos
$result->close();
$conexion->close();
?>