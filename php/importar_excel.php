<?php
require_once './conexion_be.php';
require '../vendor/autoload.php'; // Asegúrate de tener PhpSpreadsheet instalado

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo_excel'])) {
    $archivo = $_FILES['archivo_excel']['tmp_name'];
    $tipo = $_FILES['archivo_excel']['type'];

    try {
        $spreadsheet = IOFactory::load($archivo);
        $hoja = $spreadsheet->getActiveSheet();
        $datos = $hoja->toArray();

        $errores = [];
        $insertados = 0;

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

        $conexion->close();

        echo "<h3>Importación completada</h3>";
        echo "<p>$insertados filas insertadas correctamente.</p>";
        if ($errores) {
            echo "<h4>Errores:</h4><ul>";
            foreach ($errores as $e) echo "<li>$e</li>";
            echo "</ul>";
        }

    } catch (Exception $e) {
        echo "<p>Error al leer el archivo: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<form method='POST' enctype='multipart/form-data'>
        <label>Selecciona archivo Excel (.xls o .xlsx):</label>
        <input type='file' name='archivo_excel' accept='.xls,.xlsx'>
        <button type='submit'>Subir</button>
    </form>";
}
?>
