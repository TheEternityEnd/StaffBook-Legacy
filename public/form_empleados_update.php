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

// Lógica para obtener detalles de un empleado específico
if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Asegúrate de sanitizar el valor
    $query_detalle = "SELECT * FROM empleados WHERE id = ?";
    $stmt_detalle = $conexion->prepare($query_detalle);
    $stmt_detalle->bind_param("i", $id);
    $stmt_detalle->execute();
    $result_detalle = $stmt_detalle->get_result();

    if ($result_detalle->num_rows > 0) {
        $employee = $result_detalle->fetch_assoc();
    } else {
        echo "Empleado no encontrado.";
        exit;
    }

    $stmt_detalle->close();
} else {
    echo "ID de empleado no proporcionado.";
    exit;
}

$conexion->close();
?>



<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Actualizacion de Empleado</title>
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

    <!--Formulario de registro-->
    <div class="container">
        <h1>Actualizar la Informacion del Empleado: <?php echo htmlspecialchars($employee['nombre']); ?></h1>
        <form action="../php/update_empleado.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $employee['id']; ?>">
                <div>
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($employee['nombre']); ?>" required>
                </div>  
                <div>  
                    <label for="clave">Clave:</label>
                    <input type="text" id="clave" name="clave" value="<?php echo htmlspecialchars($employee['clave']); ?>" required>
                </div>
                <div>    
                    <label for="funcion_empleado">Función del Empleado:</label>
                    <select id="funcion_empleado" name="funcion_empleado" required>
                        <option value="Administrativo" <?php echo ($employee['funcion_empleado'] == 'Administrativo' ? 'selected' : ''); ?>>Administrativo</option>
                        <option value="Analista" <?php echo ($employee['funcion_empleado'] == 'Analista' ? 'selected' : ''); ?>>Analista</option>
                        <option value="Apoyo" <?php echo ($employee['funcion_empleado'] == 'Apoyo' ? 'selected' : ''); ?>>Apoyo</option>
                        <option value="Docencia" <?php echo ($employee['funcion_empleado'] == 'Docencia' ? 'selected' : ''); ?>>Docencia</option>
                        <!-- <option value="Incapacidad" <?php echo ($employee['funcion_empleado'] == 'Incapacidad' ? 'selected' : ''); ?>>Incapacidad</option>
                        <option value="Incapacidad Permanente" <?php echo ($employee['funcion_empleado'] == 'Incapacidad Permanente' ? 'selected' : ''); ?>>Incapacidad Permanente</option> -->
                        <option value="Servicio" <?php echo ($employee['funcion_empleado'] == 'Servicio' ? 'selected' : ''); ?>>Servicio</option>
                    </select>
                </div>  
                <div> 
                    <label for="tipo_empleado">Tipo de Empleado:</label>
                    <select id="tipo_empleado" name="tipo_empleado" required>
                        <option value="Confianza" <?php echo ($employee['tipo_empleado'] == 'Confianza' ? 'selected' : ''); ?>>Confianza</option>
                        <option value="Determinado" <?php echo ($employee['tipo_empleado'] == 'Determinado' ? 'selected' : ''); ?>>Determinado</option>
                        <option value="Indeterminado" <?php echo ($employee['tipo_empleado'] == 'Indeterminado' ? 'selected' : ''); ?>>Indeterminado</option>
                        <option value="Eventual" <?php echo ($employee['tipo_empleado'] == 'Eventual' ? 'selected' : ''); ?>>Eventual</option>
                    </select>
                </div>  
                <div>
                    <label for="area">Área:</label>
                    <select id="area" name="area" required>
                        <option value="Sub. de Serv. Administrativos" <?php echo ($employee['area'] == 'Sub. de Serv. Administrativos' ? 'selected' : ''); ?>>Sub. de Serv. Administrativos</option>
                        <option value="Direccion General" <?php echo ($employee['area'] == 'Direccion General' ? 'selected' : ''); ?>>Dirección General</option>
                        <option value="Sub. Academica" <?php echo ($employee['area'] == 'Sub. Academica' ? 'selected' : ''); ?>>Sub. Académica</option>
                        <option value="Sub. de Admon. y Finanzas" <?php echo ($employee['area'] == 'Sub. de Admon. y Finanzas' ? 'selected' : ''); ?>>Sub. de Administración y Finanzas</option>
                        <option value="Sub. de Plan. y Vinculacion" <?php echo ($employee['area'] == 'Sub. de Plan. y Vinculacion' ? 'selected' : ''); ?>>Sub. de Planificación y Vinculación</option>
                    </select>
                </div>  
                <div>
                    <label for="puesto">Puesto:</label>
                    <input type="text" id="puesto" name="puesto" value="<?php echo htmlspecialchars($employee['puesto']); ?>" required>
                </div>  
                <div>
                    <label for="escolaridad">Escolaridad:</label>
                    <select id="escolaridad" name="escolaridad" required>
                        <option value="Ing. en Sistemas Computacionales" <?php echo ($employee['escolaridad'] == 'Ing. en Sistemas Computacionales' ? 'selected' : ''); ?>>Ing. en Sistemas Computacionales</option>
                        <option value="Ing. Civil" <?php echo ($employee['escolaridad'] == 'Ing. Civil' ? 'selected' : ''); ?>>Ing. Civil</option>
                        <option value="Ing. Industrial" <?php echo ($employee['escolaridad'] == 'Ing. Industrial' ? 'selected' : ''); ?>>Ing. Industrial</option>
                        <option value="Lic. en Administracion" <?php echo ($employee['escolaridad'] == 'Lic. en Administracion' ? 'selected' : ''); ?>>Lic. en Administración</option>
                        <option value="No es Docente" <?php echo ($employee['escolaridad'] == 'No es Docente' ? 'selected' : ''); ?>>No es Docente</option>
                    </select>
                </div>  
                <div>
                    <label for="sexo">Sexo:</label>
                    <select id="sexo" name="sexo" required>
                        <option value="Hombre" <?php echo ($employee['sexo'] == 'Hombre' ? 'selected' : ''); ?>>Hombre</option>
                        <option value="Mujer" <?php echo ($employee['sexo'] == 'Mujer' ? 'selected' : ''); ?>>Mujer</option>
                        <option value="Otro" <?php echo ($employee['sexo'] == 'Otro' ? 'selected' : ''); ?>>Otro</option>
                    </select>
                </div>  
                <div>
                    <label for="tipo_sangre">Tipo de Sangre:</label>
                    <select id="tipo_sangre" name="tipo_sangre" required>
                        <option value="A+" <?php echo ($employee['tipo_sangre'] == 'A+' ? 'selected' : ''); ?>>A+</option>
                        <option value="A-" <?php echo ($employee['tipo_sangre'] == 'A-' ? 'selected' : ''); ?>>A-</option>
                        <option value="B+" <?php echo ($employee['tipo_sangre'] == 'B+' ? 'selected' : ''); ?>>B+</option>
                        <option value="B-" <?php echo ($employee['tipo_sangre'] == 'B-' ? 'selected' : ''); ?>>B-</option>
                        <option value="AB+" <?php echo ($employee['tipo_sangre'] == 'AB+' ? 'selected' : ''); ?>>AB+</option>
                        <option value="AB-" <?php echo ($employee['tipo_sangre'] == 'AB-' ? 'selected' : ''); ?>>AB-</option>
                        <option value="O+" <?php echo ($employee['tipo_sangre'] == 'O+' ? 'selected' : ''); ?>>O+</option>
                        <option value="O-" <?php echo ($employee['tipo_sangre'] == 'O-' ? 'selected' : ''); ?>>O-</option>
                    </select>
                </div>  
                <div>
                    <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
                    <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="<?php echo $employee['fecha_nacimiento']; ?>" required>
                </div>  
                <div>
                    <label for="estado_civil">Estado Civil:</label>
                    <select id="estado_civil" name="estado_civil" required>
                        <option value="Casado" <?php echo ($employee['estado_civil'] == 'Casado' ? 'selected' : ''); ?>>Casado</option>
                        <option value="Soltero" <?php echo ($employee['estado_civil'] == 'Soltero' ? 'selected' : ''); ?>>Soltero</option>
                        <option value="Viudo" <?php echo ($employee['estado_civil'] == 'Viudo' ? 'selected' : ''); ?>>Viudo</option>
                        <option value="Divorciado" <?php echo ($employee['estado_civil'] == 'Divorciado' ? 'selected' : ''); ?>>Divorciado</option>
                    </select>
                </div>  
                <div>
                    <label for="curp">CURP:</label>
                    <input type="text" id="curp" name="curp" value="<?php echo htmlspecialchars($employee['curp']); ?>" required>
                </div>  
                <div>
                    <label for="rfc">RFC:</label>
                    <input type="text" id="rfc" name="rfc" value="<?php echo htmlspecialchars($employee['rfc']); ?>" required>
                </div>  
                <div>
                    <label for="afiliacion">Afiliación:</label>
                    <input type="text" id="afiliacion" name="afiliacion" value="<?php echo htmlspecialchars($employee['afiliacion']); ?>" required>
                </div>  
                <div>
                    <label for="fecha_ingreso">Fecha de Ingreso:</label>
                    <input type="date" id="fecha_ingreso" name="fecha_ingreso" value="<?php echo $employee['fecha_ingreso']; ?>" required>
                </div>  
                <div>
                    <label for="fecha_baja">Fecha de Baja:</label>
                    <input type="date" id="fecha_baja" name="fecha_baja" value="<?php echo $employee['fecha_baja']; ?>">
                </div>  
                <div>
                    <label for="telefono">Teléfono:</label>
                    <input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($employee['telefono']); ?>" required>
                </div>  
                <div>
                    <label for="domicilio">Domicilio:</label>
                    <input type="text" id="domicilio" name="domicilio" value="<?php echo htmlspecialchars($employee['domicilio']); ?>" required>
                </div>  
                <div>
                    <label for="email_personal">Email Personal:</label>
                    <input type="email" id="email_personal" name="email_personal" value="<?php echo htmlspecialchars($employee['email_personal']); ?>" required>
                </div>  
                <div>
                    <label for="email_tecnm">Email TecNM:</label>
                    <input type="email" id="email_tecnm" name="email_tecnm" value="<?php echo htmlspecialchars($employee['email_tecnm']); ?>" required>
                </div>  
                <div class="upload-image-section">
                    <label for="imagen_empleado">Subir Imagen del Empleado (PNG o JPG, 5MB)</label>
                    <input 
                        type="file" 
                        id="imagen_empleado" 
                        name="imagen_empleado" 
                        accept=".png, .jpg" 
                        onchange="validateImage()" 
                        value="<?php echo htmlspecialchars($employee['img']); ?>"
                    >
                    <p id="image-error" style="color: red; font-size: 14px; display: none;">El archivo debe ser una imagen PNG o JPG menor a 5 MB.</p>
                </div>
            
            <button class="update" type="submit">Actualizar</button>
        </form>

    </div>

    <script src="../js/form_empleadosScript.js"></script>
    <script src="../js/mainScript.js"></script>
</body>
</html>
