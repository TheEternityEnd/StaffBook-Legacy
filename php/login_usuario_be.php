<?php
session_start();
include 'conexion_be.php';  // Conexión a la base de datos

$usuario = $_POST['userLogin'];
$contrasena = $_POST['passLogin'];

// Encriptar la contraseña usando hash
$contrasena = hash('sha512', $contrasena);

// Preparar la consulta para verificar usuario, contraseña Y estado de verificación
$stmt = $conexion->prepare("SELECT * FROM usuarios WHERE usuario = ? AND contrasena = ? AND verificado = 1");
$stmt->bind_param('ss', $usuario, $contrasena);  // 'ss' significa que ambos parámetros son cadenas (strings)

// Ejecutar la consulta
$stmt->execute();
$resultado = $stmt->get_result();

// Verificar si hay resultados (usuario encontrado Y verificado)
if ($resultado->num_rows > 0) {
    $fila = $resultado->fetch_assoc();
    $_SESSION['usuario'] = $fila['usuario'];
    $_SESSION['es_admin'] = $fila['admin'];

    // Llamar a log.php para registrar la acción
    $accion = "Inicio de Sesion";
    $detalle = "Inicio de sesión exitoso desde IP: " . $_SERVER['REMOTE_ADDR'];

    // Preparar la consulta para insertar en movimientos_log
    $stmt_log = $conexion->prepare("INSERT INTO movimientos_log (usuario, accion, detalle) VALUES (?, ?, ?)");
    $stmt_log->bind_param('sss', $usuario, $accion, $detalle);
    $stmt_log->execute();
    $stmt_log->close();

    // Redirigir a la página principal
    header("location: ../main.php");

    // Actualizar la fecha de la última sesión de manera segura
    $stmt_update = $conexion->prepare("UPDATE usuarios SET ultima_sesion = CURRENT_TIMESTAMP WHERE usuario = ?");
    $stmt_update->bind_param('s', $usuario);  // 's' para string
    $stmt_update->execute();

    exit();
} else {
    // Verificar si el usuario existe pero no está verificado
    $stmt_unverified = $conexion->prepare("SELECT * FROM usuarios WHERE usuario = ? AND contrasena = ? AND verificado = 0");
    $stmt_unverified->bind_param('ss', $usuario, $contrasena);
    $stmt_unverified->execute();
    $unverified_result = $stmt_unverified->get_result();
    
    if ($unverified_result->num_rows > 0) {
        // Mensaje específico para usuario no verificado
        echo '
            <script>
                alert("Tu cuenta no ha sido verificada. Por favor, verifica tu cuenta antes de iniciar sesión.");
                window.location = "../index.php";
            </script>
        ';
    } else {
        // Mensaje genérico para credenciales incorrectas
        echo '
            <script>
                alert("Usuario y/o contraseña incorrectos o cuenta no verificada.");
                window.location = "../index.php";
            </script>
        ';
    }
    exit();
}

// Cerrar las sentencias preparadas y la conexión
$stmt->close();
if (isset($stmt_update)) $stmt_update->close();
if (isset($stmt_unverified)) $stmt_unverified->close();
$conexion->close();
?>