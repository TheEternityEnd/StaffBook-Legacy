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

    // Lógica de importación de Excel
    require_once '../vendor/autoload.php';
    use PhpOffice\PhpSpreadsheet\IOFactory;
    use PhpOffice\PhpSpreadsheet\Shared\Date;

    $mensaje = '';
    $errores = [];
    $insertados = 0;

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo_excel'])) {
        $archivo = $_FILES['archivo_excel']['tmp_name'];

        try {
            $spreadsheet = IOFactory::load($archivo);
            $hoja = $spreadsheet->getActiveSheet();
            $datos = $hoja->toArray();

            foreach ($datos as $index => $fila) {
                if ($index === 0) continue; // Saltar encabezado

                if (count($fila) !== 21) {
                    $errores[] = "Fila $index con columnas incorrectas (" . count($fila) . ")";
                    continue;
                }

                list($nombre, $clave, $funcion_empleado, $tipo_empleado, $area,
                     $puesto, $escolaridad, $sexo, $tipo_sangre, $fecha_nacimiento,
                     $estado_civil, $curp, $rfc, $afiliacion, $fecha_ingreso, $fecha_baja,
                     $telefono, $domicilio, $email_personal, $email_tecmn, $img) = $fila;

                // Validaciones básicas
                if (!$nombre || !$clave || !$email_tecmn) {
                    $errores[] = "Fila $index con campos obligatorios vacíos.";
                    continue;
                }

                // Convertir fechas si vienen en formato numérico o texto tipo d/m/Y
                foreach (['fecha_nacimiento', 'fecha_ingreso', 'fecha_baja'] as $campo) {
                    if (isset($$campo)) {
                        if (is_numeric($$campo)) {
                            $$campo = Date::excelToDateTimeObject($$campo)->format('Y-m-d');
                        } elseif (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $$campo, $m)) {
                            $$campo = sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
                        }
                    }
                }

                // Validación de fechas
                foreach ([$fecha_nacimiento, $fecha_ingreso, $fecha_baja] as $fecha) {
                    if ($fecha && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
                        $errores[] = "Fila $index con formato de fecha inválido: $fecha";
                        continue 2;
                    }
                }

                $stmt = $conexion->prepare("INSERT INTO empleados (
                    nombre, clave, funcion_empleado, tipo_empleado, area,
                    puesto, escolaridad, sexo, tipo_sangre, fecha_nacimiento,
                    estado_civil, curp, rfc, afiliacion, fecha_ingreso, fecha_baja,
                    telefono, domicilio, email_personal, email_tecnm, img
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                $stmt->bind_param("sssssssssssssssssssss",
                    $nombre, $clave, $funcion_empleado, $tipo_empleado, $area,
                    $puesto, $escolaridad, $sexo, $tipo_sangre, $fecha_nacimiento,
                    $estado_civil, $curp, $rfc, $afiliacion, $fecha_ingreso, $fecha_baja,
                    $telefono, $domicilio, $email_personal, $email_tecmn, $img);

                if ($stmt->execute()) {
                    $insertados++;
                } else {
                    $errores[] = "Fila $index error al insertar: " . $stmt->error;
                }

                $stmt->close();
            }

            if ($insertados > 0) {
                $mensaje = "<div class='success-message'>Importación completada: $insertados empleados importados correctamente.</div>";
            }

            if (!empty($errores)) {
                $mensaje .= "<div class='error-message'><h4>Errores encontrados:</h4><ul>";
                foreach ($errores as $error) {
                    $mensaje .= "<li>$error</li>";
                }
                $mensaje .= "</ul></div>";
            }

        } catch (Exception $e) {
            $mensaje = "<div class='error-message'>Error al leer el archivo: " . $e->getMessage() . "</div>";
        }
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importar Empleados desde Excel</title>
    <link rel="stylesheet" href="../css/form-empleadosStyles.css">
    <style>
        .import-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .import-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .import-form label {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .import-form input[type="file"] {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .import-form button {
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        
        .import-form button:hover {
            background-color: #45a049;
        }
        
        .success-message {
            background-color: #dff0d8;
            color: #3c763d;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .error-message {
            background-color: #f2dede;
            color: #a94442;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .error-message ul {
            margin: 10px 0 0 20px;
        }
    </style>
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

    <div class="container">
        <div class="import-container">
            <h2>Importar Empleados desde Excel</h2>
            <?php if (!empty($mensaje)) echo $mensaje; ?>
            
            <form method="POST" enctype="multipart/form-data" class="import-form">
                <label for="archivo_excel">Selecciona un archivo Excel (.xls, .xlsx):</label>
                <input type="file" name="archivo_excel" id="archivo_excel" accept=".xls,.xlsx" required>
                <button type="submit">Importar Datos</button>
            </form>
            
            <div class="instructions">
                <h3>Instrucciones:</h3>
                <ul>
                    <li>Descargar la plantilla haciendo click <a href="./plantilla.xlsx">aqui</a></li>
                    <li>El archivo debe tener exactamente 21 columnas en el orden correcto.</li>
                    <li>Las columnas obligatorias son: Nombre, Clave y Email institucional.</li>
                    <li>Las fechas deben estar en formato dd/mm/aaaa o en formato de Excel.</li>
                    <li>La primera fila debe ser la cabecera con los nombres de las columnas.</li>
                    <li>Para mas informacion, Consultar la guia de usuario</li>
                </ul>
            </div>
        </div>
    </div>

    <script src="../js/form_empleadosScript.js"></script>
    <script src="../js/mainScript.js"></script>
</body>
</html>