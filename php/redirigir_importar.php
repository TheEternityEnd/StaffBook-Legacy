<?php
    session_start();

    // Verificar si el usuario está autenticado
    if (!isset($_SESSION['usuario'])) {
        header("location: ../index.php");
        session_destroy();
        die();
    }

    // Redirigir a la página form_empleados.php
    header("location: ../public/form_importar.php");
    exit;
?>
