<?php
    session_start();

    if(isset($_SESSION['usuario'])){
        header("location: main.php");
    }
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StaffBook − Iniciar Sesion</title>
    <link rel="stylesheet" href="css/loginStyles.css">
</head>

<body>
    <div class=" cuadro-blanco ">
        <img src="images/TecNM.png " alt="Imagen centrada " class="imagen-centrada ">
        <img src="images/mainLogo1_Blue_Title.png " alt="Imagen centrada " class="imagen-centrada ">
        <img src="images/ITSPP-logotipo-colores.png " alt="Imagen centrada " class="imagen-centrada ">
    </div>

    <!--Formulario Login-->
    <form id="form1" class="form visible" action="php/login_usuario_be.php" method="POST">
        <p id="heading ">Iniciar Sesión</p>

        <!--Usuario-->
        <div class="field ">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-at-sign">
                <circle cx="12" cy="12" r="4"></circle>
                <path d="M16 8v5a3 3 0 0 0 6 0v-1a10 10 0 1 0-3.92 7.94"></path>
            </svg>
            <input autocomplete="off " placeholder="Nombre de Usuario " class="input-field " type="text " name="userLogin" aria-label="Username " required>
        </div>

        <!--Pass-->
        <div class="field ">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-lock">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
            <input placeholder="Contraseña " class="input-field " type="password" name="passLogin" aria-label="Password " required>
        </div>

        <!--Botones-->
        <div class="btn ">
            <button class="button1 " type="submit ">Iniciar Sesión</button>
            <button class="button2 " type="button" id="toForm2">Registrarse</button>
        </div>
        <button class="button3 " type="button"></button>
    </form>


    <!--Formulario Register-->
    <form id="form2" class="form margin" action="php/registro_usuario_be.php" method="POST">
        <p id="heading ">Registrarse</p>

        <!--Usuario-->
        <div class="field ">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-at-sign">
                <circle cx="12" cy="12" r="4"></circle>
                <path d="M16 8v5a3 3 0 0 0 6 0v-1a10 10 0 1 0-3.92 7.94"></path>
            </svg>
            <input autocomplete="off " placeholder="Nombre de Usuario " class="input-field " type="text " name="userRegister" aria-label="Username " required>
        </div>

        <!--Correo-->
        <div class="field ">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-mail">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                <polyline points="22,6 12,13 2,6"></polyline>
            </svg>
            <input autocomplete="off " placeholder="Correo Electronico" class="input-field " type="text " name="email" aria-label="Username " required>
        </div>

        <!--Nombre-->
        <div class="field ">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
            <input autocomplete="off " placeholder="Nombre" class="input-field " type="text " name="name" aria-label="Username " required>
        </div>

        <!--Apellido-->
        <div class="field ">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
            <input autocomplete="off " placeholder="Apellido" class="input-field " type="text " name="lastname" aria-label="Username " required>
        </div>

        <!--Pass-->
        <div class="field ">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-lock">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
            <input placeholder="Contraseña " class="input-field " type="password" name="passRegister" aria-label="Password " required>
        </div>

        <!--Confirmar pass-->
        <div class="field ">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-lock">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
            <input placeholder="Confirmar Contraseña" class="input-field " type="password" name="passRegisterCon" aria-label="Password " required>
        </div>

        <!--Botones-->
        <div class="btn ">

        </div>

        <button class="button4 " type="button ">Registrarse</button>
        <button class="button5 " type="button" id="toForm1">¿Ya tienes una cuenta?</button>
    </form>
    <script src="js/script.js "></script>


</body>

</html>