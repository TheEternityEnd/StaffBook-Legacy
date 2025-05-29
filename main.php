<?php
    session_start();
    include 'php/conexion_be.php'; // Conexión a la base de datos

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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StaffBook</title>
    <link rel="stylesheet" href="css/mainStyles.css">
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
                <h1>StaffBook</h1>
            </div>
        </div>
        <div class="search-bar">
            <div class="search-container">
                <button class="search-button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </button>
                <input type="text" placeholder="Buscar">
            </div>
        </div>
        <div class="profile" onclick="toggleSidebar()">
            <img src="<?php echo htmlspecialchars($img_usuario); ?>" alt="Profile Picture" class="profile-img">
            <span><?php echo htmlspecialchars($nombre_usuario); ?></span>
        </div>

    </header>

    <!--Sidebar derecho del perfil de usuario-->
    <div class="sidebar-overlay" id="sidebar-overlay" onclick="toggleSidebar()"></div>
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="<?php echo htmlspecialchars($img_usuario); ?>" alt="Profile Picture" class="sidebar-img">
            <div>
                <h3><?php echo htmlspecialchars($nombre_usuario); ?></h3>
                <p><?php echo htmlspecialchars($email_usuario); ?></p>
            </div>
        </div>
        <ul class="sidebar-menu">
            <form action="php/redirigir_log.php">
                <button type="submit"><span>🗂️</span> Historial</button>
            </form>
            <button onclick="window.location.href='./php/exportar_excel.php'"><span>📤</span> Exportar a Excel</button>
            <button onclick="window.location.href='./php/redirigir_importar.php'"><span>📥</span> Importar de Excel</button>
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
                <form action="php/redirigirEmpleado.php" method="POST" style="display: inline;">
                    <button type="submit" style="background: none; border: none; font-size: inherit; cursor: pointer;">
                        <span>👤➕</span> Agregar Empleado
                    </button>
                </form>
            </li>
            <?php if (isset($_SESSION['es_admin']) && $_SESSION['es_admin'] == 1): ?>
                <li>
                    <form action="php/redirigir_admin.php" method="POST" style="display: inline;">
                        <button type="submit" style="background: none; border: none; font-size: inherit; cursor: pointer;">
                            <span>🖥️</span> Administración
                        </button>
                    </form>
                </li>
            <?php endif; ?>
            <li>
                <span>📖</span> 
                <a href="public/StaffBook - Guia de usuario.pdf" target="_blank" style="text-decoration: none; color: inherit;">Guía</a>
            </li>
        </ul>
    </div>

    <!--Filtros-->
    <main class="filters-section">
        <div class="categories">
            <button class="category active">Todos</button>
            <button class="category">Administrativo</button>
            <button class="category">Analista</button>
            <button class="category">Apoyo</button>
            <button class="category">Docencia</button>
            <!-- <button class="category">Incapacidad</button>
            <button class="category">Incapacidad Permanente</button> -->
            <button class="category">Servicio</button>
        </div>

        <div class="alphabet">
            <button class="letter active">All</button>
            <button class="letter">A</button>
            <button class="letter">B</button>
            <button class="letter">C</button>
            <button class="letter">D</button>
            <button class="letter">E</button>
            <button class="letter">F</button>
            <button class="letter">G</button>
            <button class="letter">H</button>
            <button class="letter">I</button>
            <button class="letter">J</button>
            <button class="letter">K</button>
            <button class="letter">L</button>
            <button class="letter">M</button>
            <button class="letter">N</button>
            <!--Me importa un carajo si falta la "ñ", ningun jodido nombre empieza con esa letra-->
            <button class="letter">O</button>
            <button class="letter">P</button>
            <button class="letter">Q</button>
            <button class="letter">R</button>
            <button class="letter">S</button>
            <button class="letter">T</button>
            <button class="letter">U</button>
            <button class="letter">V</button>
            <button class="letter">W</button>
            <button class="letter">X</button>
            <button class="letter">Y</button>
            <button class="letter">Z</button>
        </div>
    </main>

    <!-- Grid de tarjetas -->
    <div class="card-grid">
        <?php if ($result->num_rows > 0) { ?>
            <?php while ($row = $result->fetch_assoc()) { 
                // Verificar si hay una imagen, de lo contrario usar el placeholder
                $img_url = $row['img'];
                if (!$img_url) {
                    $img_url = ($row['sexo'] === 'Hombre') 
                        ? 'https://avatar.iran.liara.run/public/7' 
                        : 'https://avatar.iran.liara.run/public/55';
                }

                // Formatear la fecha de nacimiento
                $fecha_nacimiento = date("d/M/y", strtotime($row['fecha_nacimiento']));
            ?>
                <div class="card" onclick="openEmployeeDetails(this)" data-clave="<?php echo htmlspecialchars($row['clave'], ENT_QUOTES, 'UTF-8'); ?>">
                    <!-- Contenido de la tarjeta -->
                    <img src="<?php echo htmlspecialchars($img_url); ?>" alt="Foto de perfil" class="card-img">
                    <h3 class="card-name"><?php echo htmlspecialchars($row['nombre']); ?></h3>
                    <p class="card-role"><?php echo htmlspecialchars($row['funcion_empleado']); ?></p>
                    <p class="card-info">📞 <?php echo htmlspecialchars($row['telefono']); ?></p>
                    <p class="card-info">📅 <?php echo $fecha_nacimiento; ?></p>
                    <p class="card-info"><?php echo htmlspecialchars($row['email_personal']); ?></p>
                    <form action="./public/form_empleados_update.php" method="GET">
                        <div class="card-buttons">
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <button class="edit-button" type="submit" onclick="event.stopPropagation();">✏️</button>
                            <!-- Botón de eliminación que llama a la función JS -->
                            <button 
                                class="delete-button" 
                                type="button" 
                                onclick="event.stopPropagation(); showDeleteConfirmation(<?php echo $row['id']; ?>)">❌
                            </button>
                        </div>
                    </form>
                </div>
            <?php } ?>
        <?php } else { ?>
            <p style="
            grid-column: 1 / -1; /* Ocupa todas las columnas del grid */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 200px; /* Altura opcional */
            margin: 0; /* Elimina márgenes por defecto */
            font-size: 2.5rem;
            color: #666;
            ">No hay empleados registrados.</p>
        <?php } ?>
    </div>

    <!-- Cuadro de detalles del empleado -->
    <div class="employee-details-overlay" id="employee-details-overlay" onclick="closeEmployeeDetails()"></div>
    <div class="employee-details" id="employee-details">    
        <!-- Sección superior -->
        <div class="details-top">
            <img src="https://dummyimage.com/230x230/000/fff" alt="Foto de perfil">
            <div>
                <h1>Aaron Campbell</h1>
                <p>525</p>
                <p>Administrativo</p>
                <p>Jefe del departamento de Extraescolares y de Innovacion y Calidad</p>
                <p>Confianza</p>
                <p>jose.ag@puertopeñasco.tecnm.mx</p>
            </div>
        </div>

        <!-- Sección media -->
        <div class="details-middle">
            <p><strong>Email:</strong> <span>email@example.com</span></p>
            <p><strong>Teléfono:</strong> <span>638-105-5882</span></p>
            <p><strong>Cumpleaños:</strong> <span>17 marzo</span></p>
            <p><strong>Ingreso:</strong> <span>17 marzo</span></p>
            <p><strong>Baja:</strong> <span>NA</span></p>
            <p><strong>Afiliación:</strong> <span>11585501</span></p>
        </div>

        <!-- Sección inferior -->
        <div class="details-bottom">
            <p><strong>Tipo de sangre:</strong> <span>A+</span></p>
            <p><strong>Sexo:</strong> <span>Hombre</span></p>
            <p><strong>Estado Civil:</strong> <span>Soltero</span></p>
            <p><strong>CURP:</strong> <span>AEMA940317MSRNCL04</span></p>
            <p><strong>Puesto:</strong> <span>SUB. DE PLAN. Y VINCULACIÓN</span></p>
            <p><strong>Domicilio:</strong> <span>CALLEJÓN MELCHOR OCAMPO ENTRE AVE. LOS ANGELES Y 2 DE NOV. No.423</span></p>
            <p><strong>Escolaridad:</strong> <span>LIC. EN DERECHO</span></p>
            <p><strong>RFC:</strong> <span>AEMA940317N74</span></p>
        </div>
    </div>

    <!--Boton para ir arriba de la pagina-->
    <button class="scroll-to-top">
        <svg xmlns="http://www.w3.org/2000/svg" width="43" height="43" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="18 15 12 9 6 15"></polyline>
        </svg>
    </button>

    <!-- Ventana de confirmación para cerrar sesión -->
    <div class="confirm-logout-overlay" id="confirm-logout-overlay" onclick="closeLogoutConfirmation()"></div>
    <div class="confirm-logout" id="confirm-logout">
        <p>¿Seguro que quieres cerrar sesión?</p>
        <div class="confirm-buttons">
            <button class="confirm-logout-btn" onclick="cerrarSesion()">Cerrar sesión</button>
            <button class="cancel-logout-btn" onclick="closeLogoutConfirmation()">Cancelar</button>
        </div>
    </div>
    <!-- Ventana de confirmacion para eliminar empleado -->
    <div class="delete-confirm-overlay" id="delete-confirm-overlay" onclick="closeDeleteConfirmation()"></div>
    <div class="delete-confirm" id="delete-confirm">
        <p>¿Estás seguro de que deseas eliminar este empleado?</p>
        <form action="php/delete_employee.php" method="POST">
            <input type="hidden" id="delete-id" name="ids[]">
            <button type="submit" class="confirm-delete-btn">Eliminar</button>
            <button type="button" class="cancel-delete-btn" onclick="closeDeleteConfirmation()">Cancelar</button>
        </form>
    </div>

    <!--Script-->
    <script src="js/mainScript.js"></script>
</body>

</html>

<?php
// Cerrar la conexión a la base de datos
$result->close();
$conexion->close();
?>