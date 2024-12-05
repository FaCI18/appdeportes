<?php 
if (isset($_SESSION['iduser'])) {
    // Incluir la conexión a la base de datos
    include 'conexion.php';

    if ($_SESSION['roluser']==3) { // Comprueba si es alumno
        // Comprobar si firmo el deslinde de responsabilidad
        $sqlMenu = "SELECT * from usuarios WHERE id_usuario=".$_SESSION['iduser'];


        $resultMenu = $conn->query($sqlMenu); // Ejecutar la consulta

        
        if ($resultMenu->num_rows > 0) { // Comprobamos los resultados y guardamos si firmo el deslinde de responsabilidades
            $row = $resultMenu->fetch_assoc();
            $deslinde = $row['responsabilidades'];
        }else{
            $deslinde = 0;
        }

        // Comprobamos el total de notificaciones
        $sqlTotal = "SELECT COUNT(id_notificacion) AS total from notificaciones WHERE id_receptor=".$_SESSION['iduser'];
        $resultTotal = $conn->query($sqlTotal); // Ejecutamos consulta

        if ($resultTotal->num_rows > 0) { // Comprobamos resultados y guardamos el total
            $row = $resultTotal->fetch_assoc();
            $contMensajes = $row['total'];
        }else{
            $contMensajes = '';
        }
    }
}
?>


<nav id="mainNav" class="navbar navbar-default navbar-fixed-top" style="background-color: black; opacity: 1">
        <div class="container" id="navbarSupportedContent">
            <div class="container-fluid">
                <!-- Brand and toggle get grouped for better mobile display -->
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="" href="index.php"><img src="assets/img/logo.png" style="width: 30%;padding: 0px; padding-top: 10px;margin-top: 0px;" alt="Logo">
                    </a>
                </div>

                <!-- Collect the nav links, forms, and other content for toggling -->

                <div class=" navbar-collapse" id="bs-example-navbar-collapse-1">
                    <ul class="nav navbar-nav navbar-right">
                        <li>
                            <a class="page-scroll" href="informacion.php">Sobre Nosotros</a>
                        </li>
						<li>
                            <a class="page-scroll" href="ayuda.php">Ayuda</a>
                        </li>
<?php
    // Comprobamos los roles
    if (isset($_SESSION['iduser'])) {
        if ($_SESSION['roluser'] == 1){ // Admin

            // El admin tiene los siguientes menus en el desplegable de gestión:
            // - Gestion de personal
            // - Gestion de clases
            // - Gestion de paquetes
            // - Estadisticas
            // Separador
            // Envio de notificaciones de cumpleaños
            // Menu de modificacion de contenido del index
            echo('
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Gestión<span class="caret"></span></a>
                    <ul class="dropdown-menu" aria-labelledby="about-us">
                        <li><a class ="desplegable" href="gestionUsuarios.php">Gestión de personal</a></li>
                        <li><a class ="desplegable" href="gestionClases.php">Gestión de clases</a></li>
                        <li><a class ="desplegable" href="gestionPaquetes.php">Gestión de paquetes</a></li>
                        <li><a class ="desplegable" href="gestionPagos.php">Gestión de pagos</a></li>
                        <li><a class ="desplegable" href="estadisticas.php">Estadísticas</a></li>
                        <hr>
                        <li><a class ="desplegable" href="avisoCumples.php">Enviar Notificaciones de cumpleaños</a></li>
                        <li><a class ="desplegable" href="modIndex.php">Modifica Inicio</a></li>
                    </ul>
                </li>
                
            ');
        }elseif($_SESSION['roluser'] == 3){ // Alumnos

            // Solo muestra este menu si el alumno firmo el deslinde desde el perfil
            if ($deslinde) {

                // Menú de gestión del alumno
                // - Gestion de clases
                // - Gestion de paquetes
                // - Gestión de pagos
                echo('
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Gestión<span class="caret"></span></a>
                    <ul class="dropdown-menu" aria-labelledby="about-us">
                        <li><a class ="desplegable" href="gestionClases.php">Ver clases</a></li>
                        <li><a class ="desplegable" href="gestionPaquetes.php">Ver paquetes</a></li>
                        <li><a class ="desplegable" href="mispagos.php">Mis pagos</a></li>
                    </ul>
                </li>
            ');
            }
            
        }elseif($_SESSION['roluser'] == 2){ // Profesor
            // Menú de gestión del profesor
            // - Gestion de sus clases
            // - Gestion de sus paquetes (Se va a eliminar)
            echo('
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Gestión<span class="caret"></span></a>
                    <ul class="dropdown-menu" aria-labelledby="about-us">
                        <li><a class ="desplegable" href="profesorClases.php">Mis clases</a></li>
                    </ul>
                </li>
            ');
        }

        // Menú desplegable
        // Admin y Profesor solo tendrán cerrar sesión
        // Alumno tendrá la opcion de ver su perfil y sus notificaciones
        echo('
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><b>' . $_SESSION['nombreuser'] . '</b><span class="caret"></span></a>
                    <ul class="dropdown-menu" aria-labelledby="about-us">
            ');
            echo('
                        <li><a class ="desplegable" href="perfil.php"><i class="fa fa-user" aria-hidden="true"></i> Perfil</a></li>
            ');
            if ($_SESSION['roluser'] == 3) {
                if ($deslinde) {
                    if ($contMensajes==0) {
                        echo("<li><a class='desplegable' href='listaNotificaciones.php'><i class='fa fa-envelope-o' aria-hidden='true'></i> Notificaciones</a></li>");
                    }else{
                        echo("<li><a class='desplegable' href='listaNotificaciones.php'><i class='fa fa-envelope-o' aria-hidden='true'></i> <b style='color: red; font-weight: bold;'>($contMensajes)</b> Notificaciones</a></li>");
                    }
                }
            }
        echo('
                        
                        <li><a style="color: red;" class ="desplegable" href="logout.php"><i class="fa fa-sign-out" aria-hidden="true"></i> Cerrar Sesión</a></li>
                    </ul>
                </li>
                
            ');
    }
?>
                        
                        
                        

                    </ul>
                </div>
                <!-- =============== navbar-collapse =============== -->

            </div>
        </div>
        <!-- =============== container-fluid =============== -->
    </nav>

    <style>
        .dropdown:hover .dropdown-menu {
            display: block;
        }

        .desplegable{
            font-size: 20px;
        }
    </style>