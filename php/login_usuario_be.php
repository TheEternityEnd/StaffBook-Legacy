<?php
session_start();
include 'conexion_be.php';  // Conexión a la base de datos

$usuario = $_POST['userLogin'];
$contrasena = $_POST['passLogin'];

// Encriptar la contraseña usando hash
$contrasena = hash('sha512', $contrasena);

// Preparar la consulta para evitar inyecciones SQL
$stmt = $conexion->prepare("SELECT * FROM usuarios WHERE usuario = ? AND contrasena = ?");
$stmt->bind_param('ss', $usuario, $contrasena);  // 'ss' significa que ambos parámetros son cadenas (strings)

// Ejecutar la consulta
$stmt->execute();
$resultado = $stmt->get_result();

// Verificar si hay resultados (usuario encontrado)
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
    // Mensaje de error si los datos no coinciden
    echo '
        <script>
            alert("Usuario y/o contraseña incorrectos.");
            window.location = "../index.php";
        </script>
    ';
    exit();
}

// Cerrar las sentencias preparadas y la conexión
$stmt->close();
$stmt_update->close();
$conexion->close();
?>
