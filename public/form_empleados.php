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
    <title>Formulario de Registro de Empleado</title>
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
            <img src="<?php echo htmlspecialchars($img_usuario); ?>" alt="Profile Picture" class="sidebar-img">
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
            <button onclick="window.location.href='../php/importar_excel.php'"><span>📥</span> Importar de Excel</button>
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

    <!--Formulario de registro-->
    <div class="container">
        <h1>Formulario de Registro de Empleado</h1>
        <form action="../php/procesar_empleado.php" method="POST" enctype="multipart/form-data">
            <div>
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" required>
            </div>
            <div>
                <label for="clave">Clave</label>
                <input type="text" id="clave" name="clave" required>
            </div>
            <div>
                <label for="funcion_empleado">Función del Empleado</label>
                <select id="funcion_empleado" name="funcion_empleado" required>
                    <option value="NA">-Seleccione una opcion-</option>
                    <option value="Administrativo">Administrativo</option>
                    <option value="Analista">Analista</option>
                    <option value="Apoyo">Apoyo</option>
                    <option value="Docencia">Docencia</option>
                    <!-- <option value="Incapacidad">Incapacidad</option>
                    <option value="Incapacidad Permanente">Incapacidad Permanente</option> -->
                    <option value="Servicio">Servicio</option>
                </select>
            </div>
            <div>
                <label for="tipo_empleado">Tipo de Empleado</label>
                <select type="text" id="tipo_empleado" name="tipo_empleado" required>
                    <option value="NA">-Seleccione una opcion-</option>
                    <option value="Confianza">Confianza</option>
                    <option value="Determinado">Determinado</option>
                    <option value="Indeterminado">Indeterminado</option>
                    <option value="Eventual">Eventual</option>
                </select>
            </div>
            <div>
                <label for="area">Área</label>
                <select id="area" name="area" required>
                    <option value="NA">-Seleccione una opcion-</option>
                    <option value="Sub. de Serv. Administrativos">Sub. de Serv. Administrativos</option>
                    <option value="Direccion General">Direccion General</option>
                    <option value="Sub. Academica">Sub. Academica</option>
                    <option value="Sub. de Admon. y Finanzas">Sub. de Admon. y Finanzas</option>
                    <option value="Sub. de Plan. y Vinculacion">Sub. de Plan. y Vinculacion</option>
                </select>
            </div>
            <div>
                <label for="puesto">Puesto</label>
                <input type="text" id="puesto" name="puesto" required>
            </div>
            <div>
                <label for="escolaridad">Escolaridad</label>
                <select id="escolaridad" name="escolaridad" required>
                    <option value="NA">-Selecciona una opcion-</option>
                    <option value="Ing. en Sistemas Computacionales">Ing. en Sistemas Computacionales</option>
                    <option value="Ing. Civil">Ing. Civil</option>
                    <option value="Ing. Industrial">Ing. Industrial</option>
                    <option value="Lic. en Administracion">Lic. en Administracion</option>
                    <option value="No es Docente">No es Docente</option>
                </select>
            </div>
            <div>
                <label for="sexo">Sexo</label>
                <select id="sexo" name="sexo" required>
                    <option value="NA">-Seleccione una opcion-</option>
                    <option value="Hombre">Hombre</option>
                    <option value="Mujer">Mujer</option>
                    <option value="Otro">Otro</option>
                </select>
            </div>
            <div>
                <label for="tipo_sangre">Tipo de Sangre</label>
                <select id="tipo_sangre" name="tipo_sangre" required>
                    <option value="NA">-Seleccione una opcion-</option>
                    <option value="A+">A+</option>
                    <option value="A-">A-</option>
                    <option value="B+">B+</option>
                    <option value="B-">B-</option>
                    <option value="AB+">AB+</option>
                    <option value="AB+">AB+</option>
                    <option value="O+">O+</option>
                    <option value="O-">O-</option>
                </select>
            </div>
            <div>
                <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required>
            </div>
            <div>
                <label for="estado_civil">Estado Civil</label>
                <select id="estado_civil" name="estado_civil" required>
                    <option value="NA">-Seleccione una opcion-</option>
                    <option value="Casado">Casado</option>
                    <option value="Soltero">Soltero</option>
                    <option value="Viudo">Viudo</option>
                    <option value="Divorciado">Divorciado</option>
                </select>
            </div>
            <div>
                <label for="curp">CURP</label>
                <input type="text" id="curp" name="curp" required>
            </div>
            <div>
                <label for="rfc">RFC</label>
                <input type="text" id="rfc" name="rfc" required>
            </div>
            <div>
                <label for="afiliacion">Afiliación</label>
                <input type="text" id="afiliacion" name="afiliacion" required>
            </div>
            <div>
                <label for="fecha_ingreso">Fecha de Ingreso</label>
                <input type="date" id="fecha_ingreso" name="fecha_ingreso" required>
            </div>
            <div>
                <label for="fecha_baja">Fecha de Baja</label>
                <input type="date" id="fecha_baja" name="fecha_baja">
            </div>
            <div>
                <label for="telefono">Teléfono</label>
                <input type="text" id="telefono" name="telefono" required>
            </div>
            <div>
                <label for="domicilio">Domicilio</label>
                <input type="text" id="domicilio" name="domicilio" required>
            </div>
            <div>
                <label for="email_personal">Email Personal</label>
                <input type="email" id="email_personal" name="email_personal" required>
            </div>
            <div>
                <label for="email_tecnm">Email TecNM</label>
                <input type="email" id="email_tecnm" name="email_tecnm" required>
            </div>
            <div class="upload-image-section">
                <label for="imagen_empleado">Subir Imagen del Empleado (PNG o JPG, 5MB)</label>
                <input 
                    type="file" 
                    id="imagen_empleado" 
                    name="imagen_empleado" 
                    accept=".png, .jpg" 
                    onchange="validateImage()" 
                >
                <p id="image-error" style="color: red; font-size: 14px; display: none;">El archivo debe ser una imagen PNG o JPG menor a 5 MB.</p>
            </div>

            <button type="submit">Registrar Empleado</button>
        </form>
    </div>

    <script src="../js/form_empleadosScript.js"></script>
    <script src="../js/mainScript.js"></script>
</body>
</html>
