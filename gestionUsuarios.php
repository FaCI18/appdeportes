<?php
    include 'sesion.php';

    // Comprobamos que la sesion este iniciada y que el usuario sea admin
    if (!isset($_SESSION['iduser'])) {
        header("Location: index.php");
    }

    if (isset($_SESSION['iduser'])) {
        if ($_SESSION['roluser'] != 1){
            header("Location: index.php");
        }
    }


    // Variables para la lista de cada tipo de usuarios
    $lista_alumnos = [];
    $lista_profesores = [];
    $lista_admins = [];

    // Incluir la conexión a la base de datos
    include 'conexion.php';


    // Como siempre se comprueba que el usuario haya utilizado o no el formulario de buscar, para modificar la consulta
    // En este caso, el LIKE %% se utiliza tanto en nombre como apellidos de forma que el usuario puede buscar por cualquiera de ellos
    if (isset($_POST['buscaUsuario'])){
        $busqueda = $_POST['buscaUsuario'];
        $sql = "SELECT * FROM usuarios WHERE email LIKE '%$busqueda%' OR nombre LIKE '%$busqueda%' OR apellido_materno LIKE '%$busqueda%' OR apellido_paterno LIKE '%$busqueda%' LIMIT 10";
    }else{
        $sql = "SELECT * FROM usuarios LIMIT 10";
    }
    
    $result = $conn->query($sql); // Ejecutamos la consulta
    
    if ($result->num_rows > 0) { // Comprobamos que haya resultados en la consulta


            // Hay resultados
            while($row = $result->fetch_assoc()) { // Recorremos los resultados

                // Dependiendo del ROL del usuario lo añádimos a su lista correspondiente
                switch ($row['rol']) {
                    case 1:
                        $lista_admins[$row['id_usuario']] = [
                            'id_usuario' => $row['id_usuario'],
                            'email' => $row['email'],
                            'nombre' => $row['nombre'],
                            'apellido_materno' => $row['apellido_materno'],
                            'apellido_paterno' => $row['apellido_paterno'],
                            'activado' => $row['activado'],
                        ];
                        break;
                    case 2:
                        $lista_profesores[$row['id_usuario']] = [
                            'id_usuario' => $row['id_usuario'],
                            'email' => $row['email'],
                            'nombre' => $row['nombre'],
                            'apellido_materno' => $row['apellido_materno'],
                            'apellido_paterno' => $row['apellido_paterno'],
                            'activado' => $row['activado'],
                        ];
                        break;
                    case 3:
                        $lista_alumnos[$row['id_usuario']] = [
                            'id_usuario' => $row['id_usuario'],
                            'email' => $row['email'],
                            'nombre' => $row['nombre'],
                            'apellido_materno' => $row['apellido_materno'],
                            'apellido_paterno' => $row['apellido_paterno'],
                            'activado' => $row['activado'],
                        ];
                        break;
                }
        }
    }


?>
<!DOCTYPE html>
<!--
	Be by TEMPLATE STOCK
	templatestock.co @templatestock
	Released for free under the Creative Commons Attribution 3.0 license (templated.co/license)
-->
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Mar, Inc.</title>

    <!-- =============== Bootstrap Core CSS =============== -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" type="text/css">
    <!-- =============== fonts awesome =============== -->
    <link rel="stylesheet" href="assets/font/css/font-awesome.min.css" type="text/css">
    <!-- =============== Plugin CSS =============== -->
    <link rel="stylesheet" href="assets/css/animate.min.css" type="text/css">
    <!-- =============== Custom CSS =============== -->
    <link rel="stylesheet" href="assets/css/style.css" type="text/css">
    <!-- =============== Owl Carousel Assets =============== -->
    <link href="assets/owl-carousel/owl.carousel.css" rel="stylesheet">
    <link href="assets/owl-carousel/owl.theme.css" rel="stylesheet">
	
	 <link rel="stylesheet" href="assets/css/isotope-docs.css" media="screen">
	  <link rel="stylesheet" href="assets/css/baguetteBox.css">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
	
</head>

<body>
    <!-- =============== Preloader =============== -->
    <div id="preloader">
        <div id="loading">
		<img width="256" height="32" src="assets/img/loading-cylon-red.svg">	
        </div>
    </div>
    <!-- =============== nav =============== -->
<?php include 'menu.php';
    
?>
    
    <!-- =============== About =============== -->
    <section id="about" class="">
		<!-- =============== container =============== -->
    <!-- style="margin-left: 0px; margin-right: 0px" -->
        <div class="container" >   
                     
            <div class="row justify-content-center" style="padding: 0px">

                
            
                <center class="col-md-12 cuadrosombra">

                <div style="margin-top: 25px;">
                    <h2>Gestion de personal</h2>
                    <form class="form-inline" id="buscaEdita" action="gestionUsuarios.php" method="post" style="margin-bottom: 10px">
                        <div class="form-group mx-sm-3 mb-2">
                            <input type="text" minlength="3" class="form-control" name="buscaUsuario" id="buscaUsuario">
                        </div>
                        <button type="submit" class="btn btn-warning mb-2">Buscar</button>
                        <h5>Puede realizar una búsqueda por nombre o apellidos, presione buscar sin una busqueda para reiniciar la busqueda</h5>
                    </form>

                    <form id="regProfesor" action="registro.php" method="post">
                        <input type="hidden" name="registroRol" value="2">
                    </form>
                    <form id="regAlumno" action="registro.php" method="post">
                        <input type="hidden" name="registroRol" value="3">
                    </form>
                    <form id="regAdmin" action="registro.php" method="post">
                        <input type="hidden" name="registroRol" value="1">
                    </form>
                    <button type="button" class="btn btn-primary" onclick="document.getElementById('regProfesor').submit();">Registrar profesor</button>
                    <button type="button" class="btn btn-success" onclick="document.getElementById('regAlumno').submit();">Registrar alumno</button>
                    <button type="button" class="btn btn-danger" onclick="document.getElementById('regAdmin').submit();">Registrar administrador</button>
                </div>

                <!-- TABLA PROFESORES -->
                <h3>Lista de Profesores:</h3>
                <table class="tabla_usuarios">
                <tr>
                    <th>ID</th>
                    <th>Email</th>
                    <th>Nombre</th>
                    <th>Apellido Paterno</th>
                    <th>Apellido Materno</th>
                    <th>Accion</th>
                </tr>
                <?php
                    if (count($lista_profesores)>0) { 
                        foreach ($lista_profesores as $key => $value) {// Recorremos lista profesores

                            // SI el usuario esta activado (activado=1) entonces no hacemos nada
                            // Si esta desactivado (activado=0) entonces le bajamos la opacidad y le ponemos un color rojizo para mostrar que esta desactivado
                            $act = $value['activado'];
                            if ($act==1) {
                                $actEstilo = '';
                            }else{
                                $actEstilo='style="opacity: 0.5; color: coral"';
                            }

                            // Creamos los datos de la fila de la tabla y un enlace a verperfil donde le pasamos la id para ver o modificar el perfil de ese usuario
                            echo("
                                    <tr $actEstilo>
                                        <td>" . $key . "</td>
                                        <td>" . $value['email'] . "</td>
                                        <td>" . $value['nombre'] . "</td>
                                        <td>" . $value['apellido_paterno'] . "</td>
                                        <td>" . $value['apellido_materno'] . "</td>
                                        <td><a href='verperfil.php?usrid=$key'><b>Editar</b></a></td>
                                    </tr>
                                    ");
                        }
                    } else {
                            echo "No se encontraron resultados.";
                        }


                ?>
                </table>
                <h5>Mostrando máximo <b>10</b> resultados. Recomendado utilizar la búsqueda.</h5>
                <hr>


                <!-- TABLA ALUMNOS -->
                <h3 style="margin-top: 80px;">Lista de Alumnos:</h3>
                <table class="tabla_usuarios">
                <tr>
                    <th>ID</th>
                    <th>Email</th>
                    <th>Nombre</th>
                    <th>Apellido Paterno</th>
                    <th>Apellido Materno</th>
                    <th>Accion</th>
                </tr>
                <?php

                    if (count($lista_alumnos)>0) {
                        foreach ($lista_alumnos as $key => $value) { // Recorremos lista alumnos

                            // SI el usuario esta activado (activado=1) entonces no hacemos nada
                            // Si esta desactivado (activado=0) entonces le bajamos la opacidad y le ponemos un color rojizo para mostrar que esta desactivado
                            $act = $value['activado'];
                            if ($act==1) {
                                $actEstilo = '';
                            }else{
                                $actEstilo='style="opacity: 0.5; color: coral"';
                            }

                            // Creamos los datos de la fila de la tabla y un enlace a verperfil donde le pasamos la id para ver o modificar el perfil de ese usuario
                            echo("
                                    <tr $actEstilo>
                                        <td>" . $key . "</td>
                                        <td>" . $value['email'] . "</td>
                                        <td>" . $value['nombre'] . "</td>
                                        <td>" . $value['apellido_paterno'] . "</td>
                                        <td>" . $value['apellido_materno'] . "</td>
                                        <td><a href='verperfil.php?usrid=$key'><b>Editar</b></a></td>
                                    </tr>
                                    ");
                        }
                    } else {
                            echo "No se encontraron resultados.";
                        }


                ?>
                </table>
                <h5>Mostrando máximo <b>10</b> resultados. Recomendado utilizar la búsqueda.</h5>
                <hr>

                <!-- Tabla Admins -->
                <h3 style="margin-top: 80px;">Lista de Administradores:</h3>
                <table class="tabla_usuarios">
                <tr>
                    <th>ID</th>
                    <th>Email</th>
                    <th>Nombre</th>
                    <th>Apellido Paterno</th>
                    <th>Apellido Materno</th>
                    <th>Accion</th>
                </tr>
                <?php
                    if (count($lista_admins)>0) {
                        foreach ($lista_admins as $key => $value) { // Recorremos la lista de admins

                            // SI el usuario esta activado (activado=1) entonces no hacemos nada
                            // Si esta desactivado (activado=0) entonces le bajamos la opacidad y le ponemos un color rojizo para mostrar que esta desactivado
                            $act = $value['activado'];
                            if ($act==1) {
                                $actEstilo = '';
                            }else{
                                $actEstilo='style="opacity: 0.5; color: coral"';
                            }

                            // Creamos los datos de la fila de la tabla y un enlace a verperfil donde le pasamos la id para ver o modificar el perfil de ese usuario
                            echo("
                                    <tr>
                                        <td>" . $key . "</td>
                                        <td>" . $value['email'] . "</td>
                                        <td>" . $value['nombre'] . "</td>
                                        <td>" . $value['apellido_paterno'] . "</td>
                                        <td>" . $value['apellido_materno'] . "</td>
                                        <td><a href='verperfil.php?usrid=$key'><b>Editar</b></a></td>");
                            echo("</tr>");
                        }
                    } else {
                            echo "No se encontraron resultados.";
                        }


                ?>
                </table>
                <h5>Mostrando máximo <b>10</b> resultados. Recomendado utilizar la búsqueda.</h5>

                    <style>
                        /*
                        .tabla_usuarios, td, tr, th{
                            border: 2px solid black;
                            padding: 10px;
                        }

                        .tabla_usuarios{
                            margin: 25px;
                        }*/

                        .cuadrosombra{
                            border-radius: 15px;
                            background-color: #f5f4f2;
                            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15),
                                0 6px 6px rgba(0, 0, 0, 0.1);
                            transition: transform 0.3s ease, box-shadow 0.3s ease;
                            margin: 20px;
                        }

                        /* Estilo general para la tabla */
                        .tabla_usuarios {
                            width: 100%;
                            border-collapse: collapse; /* Elimina los espacios entre bordes */
                            font-family: Arial, sans-serif;
                            font-size: 14px;
                            text-align: left;
                            margin: 20px 0;
                            background-color: #ffffff; /* Fondo blanco */
                            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Sombra ligera */
                            border-radius: 8px; /* Bordes redondeados */
                            overflow: hidden;
                        }

                        /* Estilo para las cabeceras */
                        .tabla_usuarios th {
                            background-color: #f5bc42; /* Verde elegante */
                            color: black; /* Texto blanco */
                            padding: 12px; /* Espaciado interno */
                            text-transform: uppercase; /* Texto en mayúsculas */
                            letter-spacing: 1px; /* Separación de letras */
                        }

                        /* Estilo para las filas */
                        .tabla_usuarios td {
                            padding: 12px; /* Espaciado interno */
                            border-bottom: 1px solid #dddddd; /* Línea divisoria */
                            color: #333; /* Texto oscuro */
                        }

                        /* Cambiar el color de fondo en las filas pares */
                        .tabla_usuarios tr:nth-child(even) {
                            background-color: #f2f2f2; /* Fondo gris claro */
                        }

                        /* Resaltar fila al pasar el mouse */
                        .tabla_usuarios tr:hover {
                            background-color: #ffedc7; /* Fondo verde claro */
                        }

                        /* Estilo para celdas de acción */
                        .tabla_usuarios td:last-child {
                            text-align: center; /* Centrar las acciones */
                            font-weight: bold; /* Resaltar */
                        }

                        /* Botones o enlaces dentro de la tabla */
                        .tabla_usuarios td a {
                            text-decoration: none; /* Eliminar subrayado */
                            color: #f5bc42; /* Color verde */
                            padding: 6px 12px; /* Espaciado interno */
                            border: 1px solid #1a1a1a; /* Borde verde */
                            border-radius: 4px; /* Bordes redondeados */
                            transition: all 0.3s ease; /* Transición suave */
                        }

                        .tabla_usuarios td a:hover {
                            background-color: #f5bc42; /* Fondo verde al pasar el mouse */
                            color: black; /* Texto blanco */
                        }

                    </style>
                </center>
            </div>
        </div>   
		<!-- =============== container end =============== -->		
    </section>	
    <!-- Footer -->
    <footer id="footer">
	<!-- =============== container =============== -->
    <div class="container">
			    <div class="row">
                <div class="col-xs-12 col-sm-6 col-md-6">

					<ul class="social-links">
						<li><a class="wow fadeInUp animated" href="index.html#" style="visibility: visible; animation-name: fadeInUp;"><i class="fa fa-facebook"></i></a></li>
						<li><a data-wow-delay=".1s" class="wow fadeInUp animated" href="index.html#" style="visibility: visible; animation-delay: 0.1s; animation-name: fadeInUp;"><i class="fa fa-twitter"></i></a></li>
						<li><a data-wow-delay=".2s" class="wow fadeInUp animated" href="index.html#" style="visibility: visible; animation-delay: 0.2s; animation-name: fadeInUp;"><i class="fa fa-google-plus"></i></a></li>
						<li><a data-wow-delay=".4s" class="wow fadeInUp animated" href="index.html#" style="visibility: visible; animation-delay: 0.4s; animation-name: fadeInUp;"><i class="fa fa-pinterest"></i></a></li>
						<li><a data-wow-delay=".5s" class="wow fadeInUp animated" href="index.html#" style="visibility: visible; animation-delay: 0.5s; animation-name: fadeInUp;"><i class="fa fa-envelope"></i></a></li>
					</ul>

                    <p class="copyright">
                        &copy; 2024 Mar,Inc. Created By <a href="">Equipo1</a>
					</p>

				</div>
				<div data-wow-delay=".6s" class="col-xs-12 col-sm-6 col-md-6 wow bounceIn  animated" style="visibility: visible; animation-delay: 0.6s; animation-name: bounceIn;">

					  <section class="widget widget_text" id="text-15">
                         <h3 class="widget-title">Puebla,Mexico</h3> <div class="textwidget">786, Calle inventada, Puebla,<br>
                        <p>Tel: 01 234-56786<br>
                        Mobile: 01 234-56786<br>
                        E-mail: <a href="#">info@MarInc.com</a></p>
                        <a href="#">Get directions on the map</a> →</div>
                    </section>

				</div>
			</div>
    </div><!-- =============== container end =============== -->
	</footer>    
	<!-- =============== jQuery =============== -->
    <script src="assets/js/jquery.js"></script>
	 <script src="assets/js/isotope-docs.min.js"></script>
    <!-- =============== Bootstrap Core JavaScript =============== -->
    <script src="assets/js/bootstrap.min.js"></script>
    <!-- =============== Plugin JavaScript =============== -->
    <script src="assets/js/jquery.easing.min.js"></script>
    <script src="assets/js/jquery.fittext.js"></script>
    <script src="assets/js/wow.min.js"></script> 
	<!-- =============== owl carousel =============== -->
    <script src="assets/owl-carousel/owl.carousel.js"></script>  
	<!-- Isotope does NOT require jQuery. But it does make things easier -->

<script src="assets/js/baguetteBox.js" async></script>
<script src="assets/js/plugins.js" async></script>
 
    <!-- =============== Custom Theme JavaScript =============== -->
    <script src="assets/js/creative.js">	</script> 
<script src="assets/js/jquery.nicescroll.min.js"></script>

<script>
  $(document).ready(function() {
  
	var nice = $("html").niceScroll();  // The document page (body)
	
	$("#div1").html($("#div1").html()+' '+nice.version);
    
    $("#boxscroll").niceScroll({cursorborder:"",cursorcolor:"#00F",boxzoom:true}); // First scrollable DIV

    $("#boxscroll2").niceScroll("#contentscroll2",{cursorcolor:"#F00",cursoropacitymax:0.7,boxzoom:true,touchbehavior:true});  // Second scrollable DIV
    $("#boxframe").niceScroll("#boxscroll3",{cursorcolor:"#0F0",cursoropacitymax:0.7,boxzoom:true,touchbehavior:true});  // This is an IFrame (iPad compatible)
	
    $("#boxscroll4").niceScroll("#boxscroll4 .wrapper",{boxzoom:true});  // hw acceleration enabled when using wrapper
    
  });
</script>
<script>
window.onload = function() {
    if(typeof oldIE === 'undefined' && Object.keys)
        hljs.initHighlighting();

    baguetteBox.run('.baguetteBoxOne');
    baguetteBox.run('.baguetteBoxTwo');
    baguetteBox.run('.baguetteBoxThree', {
        animation: 'fadeIn'
    });
    baguetteBox.run('.baguetteBoxFour', {
        buttons: false
    });
    baguetteBox.run('.baguetteBoxFive', {
        captions: function(element) {
            return element.getElementsByTagName('img')[0].alt;
        }
    });
};
</script>
</body>
</html>

