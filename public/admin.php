<?php
session_start();
include '../php/conexion_be.php';

// Verificar si el usuario está logueado y es administrador
if (!isset($_SESSION['usuario']) || !isset($_SESSION['es_admin']) || $_SESSION['es_admin'] != 1) {
    header("location: ../index.php");
    session_destroy();
    die();
}

$usuario = $_SESSION['usuario'];

// Consultar los datos del usuario
$stmt = $conexion->prepare("SELECT nombre, apellido, email, img, admin FROM usuarios WHERE usuario = ?");
$stmt->bind_param('s', $usuario);
$stmt->execute();
$result_usuario = $stmt->get_result();

if ($result_usuario->num_rows > 0) {
    $user_data = $result_usuario->fetch_assoc();
    $nombre_usuario = $user_data['nombre'] . " " . $user_data['apellido'];
    $email_usuario = $user_data['email'];
    $img_usuario = $user_data['img'] ? $user_data['img'] : './images/avatar_ph.png';
    $es_admin = $user_data['admin'];
} else {
    header("location: ../index.php");
    session_destroy();
    die();
}

$stmt->close();

// Procesar cambios en los permisos
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['actualizar_permisos'])) {
        $id = $_POST['id'];
        $usuario_mod = $_POST['usuario'];
        
        if ($usuario_mod !== 'root') {
            $verificado = isset($_POST['verificar']) ? 1 : 0;
            $admin = isset($_POST['admin']) ? 1 : 0;
            
            $query = "UPDATE usuarios SET verificado = ?, admin = ? WHERE id = ?";
            $stmt = mysqli_prepare($conexion, $query);
            mysqli_stmt_bind_param($stmt, "iii", $verificado, $admin, $id);
            mysqli_stmt_execute($stmt);
            
            if (mysqli_stmt_affected_rows($stmt) > 0) {
                // Registrar en log
                $accion = "Actualización de permisos";
                $detalle = "Usuario ID: $id, Admin: $admin, Verificado: $verificado";
                registrarMovimiento($conexion, $usuario, $accion, $detalle);
                
                $_SESSION['mensaje'] = "Permisos actualizados correctamente";
                $_SESSION['tipo_mensaje'] = "success";
            } else {
                $_SESSION['mensaje'] = "Error al actualizar permisos";
                $_SESSION['tipo_mensaje'] = "error";
            }
        } else {
            $_SESSION['mensaje'] = "No se pueden modificar los permisos del usuario root";
            $_SESSION['tipo_mensaje'] = "error";
        }
        
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    } elseif (isset($_POST['eliminar'])) {
        $id = $_POST['id'];
        $usuario_mod = $_POST['usuario'];
        
        if ($usuario_mod !== 'root') {
            $query = "DELETE FROM usuarios WHERE id = ?";
            $stmt = mysqli_prepare($conexion, $query);
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            
            if (mysqli_stmt_affected_rows($stmt) > 0) {
                // Registrar en log
                $accion = "Eliminación de usuario";
                $detalle = "Usuario ID: $id eliminado";
                registrarMovimiento($conexion, $usuario, $accion, $detalle);
                
                $_SESSION['mensaje'] = "Usuario eliminado correctamente";
                $_SESSION['tipo_mensaje'] = "success";
            } else {
                $_SESSION['mensaje'] = "Error al eliminar usuario";
                $_SESSION['tipo_mensaje'] = "error";
            }
        } else {
            $_SESSION['mensaje'] = "No se puede eliminar el usuario root";
            $_SESSION['tipo_mensaje'] = "error";
        }
        
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    } elseif (isset($_POST['eliminar_todos_empleados'])) {
        $query = "DELETE FROM empleados";
        $result = mysqli_query($conexion, $query);
        
        if (mysqli_affected_rows($conexion) > 0) {
            // Registrar en log
            $accion = "Eliminación masiva de empleados";
            $detalle = "Todos los empleados fueron eliminados";
            registrarMovimiento($conexion, $usuario, $accion, $detalle);
            
            $_SESSION['mensaje'] = "Todos los empleados han sido eliminados";
            $_SESSION['tipo_mensaje'] = "success";
        } else {
            $_SESSION['mensaje'] = "Error al eliminar todos los empleados o no había empleados para eliminar";
            $_SESSION['tipo_mensaje'] = "error";
        }
        
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
}

// Función para registrar movimientos
function registrarMovimiento($conexion, $usuario, $accion, $detalle) {
    $logQuery = "INSERT INTO movimientos_log (fecha_hora, usuario, accion, detalle) VALUES (NOW(), ?, ?, ?)";
    $logStmt = mysqli_prepare($conexion, $logQuery);
    mysqli_stmt_bind_param($logStmt, "sss", $usuario, $accion, $detalle);
    
    if (!mysqli_stmt_execute($logStmt)) {
        error_log("Error al registrar en el log: " . mysqli_error($conexion));
    }
    
    mysqli_stmt_close($logStmt);
}

// Obtener todos los usuarios y conteos
$query_usuarios = "SELECT id, usuario, CONCAT(nombre, ' ', apellido) as nombre_completo, email, admin, verificado FROM usuarios";
$resultado_usuarios = mysqli_query($conexion, $query_usuarios);

$query_count_usuarios = "SELECT COUNT(*) as total FROM usuarios";
$result_count_usuarios = mysqli_query($conexion, $query_count_usuarios);
$total_usuarios = mysqli_fetch_assoc($result_count_usuarios)['total'];

$query_count_empleados = "SELECT COUNT(*) as total FROM empleados";
$result_count_empleados = mysqli_query($conexion, $query_count_empleados);
$total_empleados = mysqli_fetch_assoc($result_count_empleados)['total'];

$mensaje = $_SESSION['mensaje'] ?? '';
$tipo_mensaje = $_SESSION['tipo_mensaje'] ?? '';
unset($_SESSION['mensaje']);
unset($_SESSION['tipo_mensaje']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administracion - StaffBook</title>
    <link rel="stylesheet" href="../css/form-empleadosStyles.css">
    <link rel="stylesheet" href="../css/adminStyles.css">
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
            <?php if ($es_admin == 1): ?>
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

    <!-- Contenido principal -->
    <div class="container">
        <!-- Sección de administración de usuarios -->
        <div class="admin-section">
            <h2 class="admin-title">Administración de Usuarios</h2>
            
            <!-- Cuadros de estadísticas -->
            <div class="stats-container">
                <div class="stat-box">
                    <div class="stat-number"><?php echo $total_empleados; ?></div>
                    <div class="stat-label">Empleados Registrados</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?php echo $total_usuarios; ?></div>
                    <div class="stat-label">Usuarios Registrados</div>
                </div>
            </div>
            
            <?php if (!empty($mensaje)): ?>
                <div id="flash-message" class="mensaje <?php echo $tipo_mensaje; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>
            
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Nombre Completo</th>
                        <th>Email</th>
                        <th>Verificado</th>
                        <th>Admin</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($usuario_row = mysqli_fetch_assoc($resultado_usuarios)): ?>
                    <?php $isRoot = ($usuario_row['usuario'] === 'root'); ?>
                    <tr class="<?php echo $isRoot ? 'root-user' : ''; ?>">
                        <td><?php echo $usuario_row['id']; ?></td>
                        <td><?php echo htmlspecialchars($usuario_row['usuario']); ?></td>
                        <td><?php echo htmlspecialchars($usuario_row['nombre_completo']); ?></td>
                        <td><?php echo htmlspecialchars($usuario_row['email']); ?></td>
                        
                        <form method="post">
                            <input type="hidden" name="id" value="<?php echo $usuario_row['id']; ?>">
                            <input type="hidden" name="usuario" value="<?php echo htmlspecialchars($usuario_row['usuario']); ?>">
                            <input type="hidden" name="actualizar_permisos" value="1">
                            
                            <td>
                                <input type="checkbox" name="verificar" class="form-checkbox" value="1"
                                    <?php echo $usuario_row['verificado'] ? 'checked' : ''; ?>
                                    <?php echo $isRoot ? 'disabled' : ''; ?>>
                            </td>
                            <td>
                                <input type="checkbox" name="admin" class="form-checkbox" value="1"
                                    <?php echo $usuario_row['admin'] ? 'checked' : ''; ?>
                                    <?php echo $isRoot ? 'disabled' : ''; ?>>
                            </td>
                            <td class="acciones">
                                <button type="submit" class="btn btn-actualizar" <?php echo $isRoot ? 'disabled' : ''; ?>>Guardar</button>
                                <button type="button" class="btn btn-eliminar" 
                                    onclick="<?php echo !$isRoot ? 'confirmarEliminacion('.$usuario_row['id'].', \''.htmlspecialchars($usuario_row['usuario']).'\')' : ''; ?>"
                                    <?php echo $isRoot ? 'disabled' : ''; ?>>
                                    Eliminar
                                </button>
                            </td>
                        </form>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            
            <!-- Botón de eliminación masiva con doble confirmación -->
            <form method="post" id="deleteAllForm">
                <input type="hidden" name="eliminar_todos_empleados" value="1">
                <button type="button" class="btn btn-danger" onclick="showDeleteAllConfirmation()">
                    ⚠️ ELIMINAR TODOS LOS EMPLEADOS ⚠️
                </button>
                
                <!-- Primer cuadro de confirmación (modal) -->
                <div id="deleteAllModal" class="modal">
                    <div class="modal-content">
                        <h3>⚠️ ADVERTENCIA: ELIMINACIÓN MASIVA ⚠️</h3>
                        <p class="warning-text">Estás a punto de eliminar <strong>TODOS</strong> los registros de empleados.</p>
                        <p>Esta acción es <strong>IRREVERSIBLE</strong> y eliminará permanentemente todos los datos.</p>
                        <p>¿Estás seguro que deseas continuar?</p>
                        
                        <div class="modal-buttons">
                            <button type="button" class="btn btn-cancelar" onclick="closeDeleteAllModal()">Cancelar</button>
                            <button type="button" class="btn btn-confirmar" onclick="showFinalConfirmation()">Continuar</button>
                        </div>
                    </div>
                </div>
                
                <!-- Segunda confirmación -->
                <div id="doubleConfirm" class="double-confirm" style="display: none;">
                    <p class="warning-text">¡CONFIRMACIÓN FINAL REQUERIDA!</p>
                    <p>Para confirmar que realmente deseas eliminar <strong>TODOS</strong> los empleados, escribe exactamente:</p>
                    <p><strong>"ELIMINAR TODOS LOS EMPLEADOS"</strong></p>
                    <input type="text" id="confirmText" style="width: 100%; padding: 8px; margin: 10px 0;" 
                           placeholder="Escribe ELIMINAR TODOS LOS EMPLEADOS aquí">
                    <div style="display: flex; justify-content: space-between;">
                        <button type="button" class="btn btn-cancelar" onclick="cancelDeleteAll()">Cancelar</button>
                        <button type="submit" class="btn btn-confirmar" id="finalConfirmBtn" disabled>Confirmar Eliminación Total</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de confirmación -->
    <div id="confirmModal" class="modal">
        <div class="modal-content">
            <h3>Confirmar eliminación</h3>
            <p>¿Estás seguro que deseas eliminar al usuario <span id="usuarioEliminar"></span>? Esta acción no se puede deshacer.</p>
            <div class="modal-buttons">
                <button onclick="cerrarModal()" class="btn btn-cancelar">Cancelar</button>
                <form id="deleteForm" method="post" style="display: inline;">
                    <input type="hidden" name="id" id="deleteUserId">
                    <input type="hidden" name="usuario" id="deleteUserUsuario">
                    <input type="hidden" name="eliminar" value="1">
                    <button type="submit" class="btn btn-confirmar">Eliminar</button>
                </form>
            </div>
        </div>
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
    <script src="../js/admin_script.js"></script>
</body>
</html>