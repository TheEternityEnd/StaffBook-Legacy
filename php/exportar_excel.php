<?php
require_once './conexion_be.php';

header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=empleados.xls");
header("Pragma: no-cache");
header("Expires: 0");

$query = "SELECT * FROM empleados";
$resultado = $conexion->query($query);

if ($resultado->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr>";
    echo "<th>id</th><th>nombre</th><th>clave</th><th>funcion_empleado</th><th>tipo_empleado</th><th>area</th><th>puesto</th><th>escolaridad</th><th>sexo</th><th>tipo_sangre</th><th>fecha_nacimiento</th><th>estado_civil</th><th>curp</th><th>rfc</th><th>afiliacion</th><th>fecha_ingreso</th><th>fecha_baja</th><th>telefono</th><th>domicilio</th><th>email_personal</th><th>email_tecmn</th><th>img</th>";
    echo "</tr>";

    while ($row = $resultado->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['nombre'] . "</td>";
        echo "<td>" . $row['clave'] . "</td>";
        echo "<td>" . $row['funcion_empleado'] . "</td>";
        echo "<td>" . $row['tipo_empleado'] . "</td>";
        echo "<td>" . $row['area'] . "</td>";
        echo "<td>" . $row['puesto'] . "</td>";
        echo "<td>" . $row['escolaridad'] . "</td>";
        echo "<td>" . $row['sexo'] . "</td>";
        echo "<td>" . $row['tipo_sangre'] . "</td>";
        echo "<td>" . $row['fecha_nacimiento'] . "</td>";
        echo "<td>" . $row['estado_civil'] . "</td>";
        echo "<td>" . $row['curp'] . "</td>";
        echo "<td>" . $row['rfc'] . "</td>";
        echo "<td>" . $row['afiliacion'] . "</td>";
        echo "<td>" . $row['fecha_ingreso'] . "</td>";
        echo "<td>" . $row['fecha_baja'] . "</td>";
        echo "<td>" . $row['telefono'] . "</td>";
        echo "<td>" . $row['domicilio'] . "</td>";
        echo "<td>" . $row['email_personal'] . "</td>";
        echo "<td>" . (isset($row['email_tecnm']) ? $row['email_tecnm'] : "") . "</td>";
        echo "<td>" . $row['img'] . "</td>";
        echo "</tr>";
    }

    echo "</table>";
} else {
    echo "<table><tr><td>No se encontraron registros.</td></tr></table>";
}

$resultado->close();
?>