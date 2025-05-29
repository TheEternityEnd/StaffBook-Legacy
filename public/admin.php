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

    // Consulta para obtener todos los empleados ordenados alfabéticamente por nombre
    $query = "SELECT id, nombre, clave, funcion_empleado, area, puesto, escolaridad, sexo, tipo_sangre, fecha_nacimiento, estado_civil, curp, rfc, afiliacion, fecha_ingreso, fecha_baja, telefono, domicilio, email_personal, email_tecnm, img
            FROM empleados 
            ORDER BY nombre ASC"; // Ordenar alfabéticamente por nombre
    $result = $conexion->query($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administracion - StaffBook</title>
    <link rel="stylesheet" href="../css/form-empleadosStyles.css">
</head>
<body>
    <!--Header-->
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
            <form action="../php/redirigir_log.php">
                <button type="submit"><span>🗂️</span> Historial</button>
            </form>
            <button onclick="window.location.href='../php/exportar_excel.php'"><span>📤</span> Exportar a Excel</button>
            <button onclick="window.location.href='../php/redirigir_importar.php'"><span>📥</span> Importar de Excel</button>
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
            <?php if (isset($_SESSION['es_admin']) && $_SESSION['es_admin'] == 1): ?>
                <li>
                    <form action="../php/redirigir_admin.php" method="POST" style="display: inline;">
                        <button type="submit" style="background: none; border: none; font-size: inherit; cursor: pointer; color: black;">
                            <span>🖥️</span> Administración
                        </button>
                    </form>
                </li>
            <?php endif; ?>
            <li>
                <span>📖</span> 
                <a href="StaffBook - Guia de usuario.pdf" target="_blank" style="text-decoration: none; color: inherit;">Guía</a>
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

    

    <script src="../js/form_empleadosScript.js"></script>
    <script src="../js/mainScript.js"></script>
</body>
</html>
