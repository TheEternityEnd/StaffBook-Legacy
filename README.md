=================================================================
=============== VER EN RAW PARA MEJOR COMPRENSION ===============
=================================================================
Version descontinuada

Este es un proyecto integrador diseñado para el Intituto Tecnologico Superior de Puerto Peñasco.

===GUIA DE USO===
Nesesitas un servidor local relacional SQL.
Cuando tengas tu servidor funcionando crea un nuevo schema llamado: "railway".
En Railway crea 3 tablas: "empleados", "usuarios" y "movimientos_log".

Ejecuta estos codigos SQL para que se generen las tablas correctamente:
Codigo 1:
USE nombre_del_esquema;

CREATE TABLE empleados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    clave VARCHAR(50) NOT NULL,
    funcion_empleado ENUM('Administrativo', 'Analista', 'Apoyo', 'Docencia', 'Incapacidad', 'Incapacidad Permanente', 'Servicio') NOT NULL,
    tipo_empleado ENUM('Confianza', 'Determinado', 'Indeterminado', 'Eventual') NOT NULL,
    area ENUM('Sub. de Serv. Administrativos', 'Direccion General', 'Sub. Academica', 'Sub. de Admon. y Finanzas', 'Sub. de Plan. y Vinculacion') NOT NULL,
    puesto VARCHAR(100) NOT NULL,
    escolaridad ENUM('Ing. en Sistemas Computacionales', 'Ing. Civil', 'Ing. Industrial', 'Lic. en Administracion', 'No es Docente') NOT NULL,
    sexo ENUM('Otro', 'Hombre', 'Mujer') NOT NULL,
    tipo_sangre ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    estado_civil ENUM('Casado', 'Soltero', 'Viudo', 'Divorciado') NOT NULL,
    curp VARCHAR(18) NOT NULL,
    rfc VARCHAR(13) NOT NULL,
    afiliacion VARCHAR(100) NOT NULL,
    fecha_ingreso DATE NOT NULL,
    fecha_baja DATE,
    telefono VARCHAR(20) NOT NULL,
    domicilio VARCHAR(255) NOT NULL,
    email_personal VARCHAR(100) NOT NULL,
    email_tecnm VARCHAR(100) NOT NULL,
    img VARCHAR(255)
);

Codigo 2:
USE nombre_del_esquema;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    contrasena VARCHAR(255) NOT NULL,
    ultima_sesion DATETIME DEFAULT NULL,
    fecha_de_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    img VARCHAR(255) DEFAULT NULL,
    admin TINYINT DEFAULT 0
);

Codigo 3:
USE nombre_del_esquema;

CREATE TABLE movimientos_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    usuario VARCHAR(100) NOT NULL,
    accion TEXT NOT NULL,
    detalle TEXT DEFAULT NULL
);

El programa ya tiene inculida las llaves de acceso a la base de datos local, en caso de que quieras modificar o hacerla con una base de datos en linea, modifica conexion_be.php en la carpeta php.

Supongo que ya sabes que es PHP y que necesitas para hacerlo funcionar, pero en caso de que no, descarga xampp del navegador y mueve la carpeta StaffBook dentro del directorio C:\xampp\htdocs\ ejecuta apache y el login de StaffBook deberia de aparecer en la url http://localhost/StaffBook/index.php






























