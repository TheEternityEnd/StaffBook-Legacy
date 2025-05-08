<?php
session_start();
include './conexion_be.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? $_POST['id'] : null;
    $nombre = $_POST['nombre'];
    $clave = $_POST['clave'];
    $funcion_empleado = $_POST['funcion_empleado'];
    $tipo_empleado = $_POST['tipo_empleado'];
    $area = $_POST['area'];
    $puesto = $_POST['puesto'];
    $escolaridad = $_POST['escolaridad'];
    $sexo = $_POST['sexo'];
    $tipo_sangre = $_POST['tipo_sangre'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $estado_civil = $_POST['estado_civil'];
    $curp = $_POST['curp'];
    $rfc = $_POST['rfc'];
    $afiliacion = $_POST['afiliacion'];
    $fecha_ingreso = $_POST['fecha_ingreso'];
    $fecha_baja = !empty($_POST['fecha_baja']) ? $_POST['fecha_baja'] : NULL;
    $telefono = $_POST['telefono'];
    $domicilio = $_POST['domicilio'];
    $email_personal = $_POST['email_personal'];
    $email_tecnm = $_POST['email_tecnm'];

    // Procesar la imagen
    $imagen_empleado = $_FILES['imagen_empleado'];
    $img_path = NULL; // Ruta por defecto para la imagen

    // Verificar si la clave ya existe en otro registro
    $checkClaveQuery = "SELECT COUNT(*) FROM empleados WHERE clave = ? AND id != ?";
    $checkClaveStmt = $conexion->prepare($checkClaveQuery);
    $checkClaveStmt->bind_param("si", $clave, $id);
    $checkClaveStmt->execute();
    $checkClaveStmt->bind_result($claveCount);
    $checkClaveStmt->fetch();
    $checkClaveStmt->close();

    if ($claveCount > 0) {
        echo '
            <script>
                alert("Error: La clave ya está asignada a otro empleado. Por favor, use una clave diferente.");
                window.history.back();
            </script>
        ';
        exit; // Detener la ejecución si la clave ya existe
    }


    if ($imagen_empleado['size'] > 0) {
        $img_name = uniqid() . '_' . basename($imagen_empleado['name']);
        $img_path = 'uploads/' . $img_name;

        // Mover la imagen al directorio de destino
        $full_path = '../' . $img_path;
        if (!move_uploaded_file($imagen_empleado['tmp_name'], $full_path)) {
            die("Error al subir la imagen.");
        }
    }

    // Construcción dinámica de la consulta SQL
    $columns = [
        "nombre = ?", "clave = ?", "funcion_empleado = ?", "tipo_empleado = ?", 
        "area = ?", "puesto = ?", "escolaridad = ?", "sexo = ?", 
        "tipo_sangre = ?", "fecha_nacimiento = ?", "estado_civil = ?", 
        "curp = ?", "rfc = ?", "afiliacion = ?", "fecha_ingreso = ?", 
        "fecha_baja = ?", "telefono = ?", "domicilio = ?", 
        "email_personal = ?", "email_tecnm = ?"
    ];

    $params = [
        $nombre, $clave, $funcion_empleado, $tipo_empleado,
        $area, $puesto, $escolaridad, $sexo,
        $tipo_sangre, $fecha_nacimiento, $estado_civil, $curp,
        $rfc, $afiliacion, $fecha_ingreso, $fecha_baja,
        $telefono, $domicilio, $email_personal, $email_tecnm
    ];

    // Si $img_path no es nulo, agregar la columna img
    if ($img_path !== null) {
        $columns[] = "img = ?";
        $params[] = $img_path;
    }

    // Agregar el ID al final de los parámetros
    $params[] = $id;

    // Construir la consulta SQL
    $query = "UPDATE empleados SET " . implode(", ", $columns) . " WHERE id = ?";

    // Preparar la consulta
    $stmt = $conexion->prepare($query);

    // Tipos de parámetros dinámicos
    $param_types = str_repeat("s", count($params) - 1) . "i"; // Todas cadenas menos el ID (entero)

    // Vincular parámetros
    $stmt->bind_param($param_types, ...$params);

    // Ejecutar la consulta
    if ($stmt->execute()) {
        $usuario = $_SESSION['usuario']; // Usuario actual desde la sesión
        $accion = "Actualización de empleado";
        $detalle = "Se modificó el empleado con ID: $id";
    
        $logQuery = "INSERT INTO movimientos_log (fecha_hora, usuario, accion, detalle) VALUES (NOW(), ?, ?, ?)";
        $logStmt = $conexion->prepare($logQuery);
        $logStmt->bind_param("sss", $usuario, $accion, $detalle);
    
        if ($logStmt->execute()) {
            $logStmt->close(); // Cerrar la sentencia
        } else {
            echo '
                <script>
                    alert("Error al registrar en el log de movimientos.");
                    window.history.back();
                </script>
            ';
            exit;
        }
    
        // Mensaje de éxito
        echo '
            <script>
                alert("Empleado actualizado exitosamente.");
                window.location = "../main.php";
            </script>
        ';
    } else {
        echo "Error al actualizar el empleado: " . $stmt->error;
    }

    $stmt->close();
    $conexion->close();
}

?>
