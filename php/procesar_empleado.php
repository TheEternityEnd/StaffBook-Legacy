Canvas <?php
// Iniciar la sesión
session_start();
include '../php/conexion_be.php'; // Conexión a la base de datos
// Verificar si el formulario se envió correctamente
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger los datos del formulario
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


      // Verificar si la clave ya existe
    $checkClaveQuery = "SELECT COUNT(*) FROM empleados WHERE clave = ?";
    $checkClaveStmt = $conexion->prepare($checkClaveQuery);
    $checkClaveStmt->bind_param("s", $clave);
    $checkClaveStmt->execute();
    $checkClaveStmt->bind_result($claveCount);
    $checkClaveStmt->fetch();
    $checkClaveStmt->close();

    if ($claveCount > 0) {
        echo '
            <script>
                alert("Error: La clave ya existe. Por favor, use una clave diferente.");
                window.history.back();
            </script>
        ';
        exit; // Detener la ejecución si la clave ya existe
    }
  
    if ($imagen_empleado['size'] > 0) {
        $img_name = uniqid() . '_' . basename($imagen_empleado['name']);
        $img_path = 'uploads/' . $img_name; // Cambiar la ruta aquí para que solo guarde "uploads/"
    
        // Mover la imagen al directorio de destino
        $full_path = '../' . $img_path; // Usar esta variable para mover el archivo físicamente
        if (!move_uploaded_file($imagen_empleado['tmp_name'], $full_path)) {
            die("Error al subir la imagen.");
        }
    }
    
    if ($id) {
        // Actualizar los datos existentes
        $query = "UPDATE empleados SET nombre = ?, clave = ?, funcion_empleado = ?, tipo_empleado = ?, area = ?, puesto = ?, escolaridad = ?, sexo = ?, tipo_sangre = ?, fecha_nacimiento = ?, estado_civil = ?, curp = ?, rfc = ?, afiliacion = ?, fecha_ingreso = ?, fecha_baja = ?, telefono = ?, domicilio = ?, email_personal = ?, email_tecnm = ?, img = ? WHERE id = ?";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("ssssssssssssssssssssi",
            $nombre,
            $clave,
            $funcion_empleado,
            $_POST['tipo_empleado'],
            $_POST['area'],
            $_POST['puesto'],
            $_POST['escolaridad'],
            $_POST['sexo'],
            $_POST['tipo_sangre'],
            $_POST['fecha_nacimiento'],
            $_POST['estado_civil'],
            $_POST['curp'],
            $_POST['rfc'],
            $_POST['afiliacion'],
            $_POST['fecha_ingreso'],
            $_POST['fecha_baja'],
            $_POST['telefono'],
            $_POST['domicilio'],
            $_POST['email_personal'],
            $_POST['email_tecnm'],
            $_POST['imagen_empleado'],
            $id
        );
        $stmt->execute();
        $stmt->close();
    } else {
        // Insertar los datos en la base de datos
        $sql = "INSERT INTO empleados (nombre, clave, funcion_empleado, tipo_empleado, area, puesto, escolaridad, sexo, tipo_sangre, fecha_nacimiento, estado_civil, curp, rfc, afiliacion, fecha_ingreso, fecha_baja, telefono, domicilio, email_personal, email_tecnm, img) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param(
        'sssssssssssssssssssss',
        $nombre,
        $clave,
        $funcion_empleado,
        $tipo_empleado,
        $area,
        $puesto,
        $escolaridad,
        $sexo,
        $tipo_sangre,
        $fecha_nacimiento,
        $estado_civil,
        $curp,
        $rfc,
        $afiliacion,
        $fecha_ingreso,
        $fecha_baja,
        $telefono,
        $domicilio,
        $email_personal,
        $email_tecnm,
        $img_path
        );

        // Ejecutar la consulta y verificar errores
        if ($stmt->execute()) {

          
            // Registrar la acción en la tabla movimientos_log
            $usuario = $_SESSION['usuario']; // Obtener el usuario actual desde la sesión
            $accion = $id ? "Actualización de empleado" : "Registro de empleado";
            $detalle = "Empleado: $nombre, Clave: $clave, Área: $area, Puesto: $puesto";
        
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
                exit; // Salir si ocurre un error
            }
        
            echo '
                <script>
                    alert("Empleado registrado exitosamente.");
                    window.location = "../main.php";
                </script>
            ';
        }
        
    }
    

    // Cerrar la sentencia y la conexión
    $stmt->close();
    $conexion->close();
} else {
    echo '
        <script>
            alert("Método de solicitud no válido.");
            window.location = "../main.php";
        </script>
    ';
}
?>
