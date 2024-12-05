<?php
    include 'sesion.php';
    include 'usuario.php';

    // Comprueba que la sesion este iniciada
    if (!isset($_SESSION['iduser'])) {
        header("Location: login.php");
        exit();
    }

    // Comprueba que recibamos el id del usuario para ver su perfil
    if (!isset($_GET['usrid'])) {
        header("Location: index.php");
        exit();
    }

    // Comprueba que el usuario activo sea admin o profesor
    if ($_SESSION['roluser']>2) {
        header("Location: index.php");
        exit();
    }

    // Guardamos los datos en variables
    $iduser = $_GET['usrid'];
    $usuario = new Usuario($_GET['usrid']);
    $datos_usuario = $usuario->getDatos();
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

    <script type="text/javascript">
        $(".datepicker").datepicker({
            format: "yyyy-mm-dd",
            startDate: '-3d'
        });
    </script>
	
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
                <center class="col-md-12">
                <h1>Perfil</h1>
                <div class="col-md-12">
                    <div style="margin-top: 25px;">
                        
                       
                        <form action="actualizausuario.php" method="POST" >
                            <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($_GET['usrid']); ?>">
                             <!-- Boton para actualizar el usuario SOLO si eres admin -->
                            <?php
                                if ($_SESSION['roluser']==1) {
                                    echo('<button type="submit" class="btn btn-primary">Actualizar</button>');
                                    $editar='';
                                }else{
                                    $editar='disabled';
                                }
                            ?>
                           
                            <h3>Datos personales</h3>

                             <div>
                                <label for="activado">Estado: </label>
                            <select name="activado" class="form-control" style="width: 200px">

                                
                            <?php
                                // Esto es un desplegable con la opcion de activado y desactivado
                                // Establecemos por defecto la opcion en la que este ahora mismo el usuario

                                if ($usuario->getActivado()==1) {
                                    echo("
                                        <option selected='selected' value='1'>Activado</option>
                                        <option value='0'>Desactivado</option>
                                    ");
                                }else{
                                    echo("
                                        <option value='1'>Activado</option>
                                        <option selected='selected' value='0'>Desactivado</option>
                                    ");
                                }
                             ?>
                             
                            </select>
                             
                             </div>

                            <!-- Imprimimos en una tabla dentro de un formulario TODOS los datos del usuario -->
                            <!-- Mediante CSS desactivamos todos los inputs en caso de que el usuario que ve el perfil NO sea admin sino profesor-->
                            <!-- htmlspecialchars lo unico que hace es sustituir los carcateres especiales por un codigo para poder verlos en el navegador sin causar problemas  -->
                            <table class="tabla_usuarios">
                            <tr>
                                <td><b>Nombre</b></td>
                                <td><input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($usuario->getNombre()); ?>" pattern="[\p{L} ]*" required></td>
                                <td><b>Email</b</td>
                                <td><input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($usuario->getEmail()); ?>" required></td>
                            </tr>
                            <tr>
                                <td><b>Apellido Materno</b></td>
                                <td><input type="text" class="form-control" id="apellido_materno" name="apellido_materno" value="<?php echo htmlspecialchars($usuario->getApellido_materno()); ?>" pattern="[\p{L} ]*" required></td>
                                <td><b>Apellido Paterno</b</td>
                                <td><input type="text" class="form-control" id="apellido_paterno" name="apellido_paterno" value="<?php echo htmlspecialchars($usuario->getApellido_paterno()); ?>" pattern="[\p{L} ]*" required></td>
                            </tr>
                            <tr>
                                <td><b>Edad</b></td>
                                <td><input type="number" class="form-control" id="edad" name="edad" min="1" max="100" value="<?php echo htmlspecialchars($datos_usuario['edad'][1]); ?>"></td>
                                <td><b>Domicilio</b></td>
                                <td><input type="text" class="form-control" id="domicilio" name="domicilio" value="<?php echo htmlspecialchars($datos_usuario['domicilio'][1]); ?>"></td>
                            </tr>
                            <tr>
                                <td><b>Teléfono</b></td>
                                <td><input type="text" class="form-control" id="telefono" name="telefono" value="<?php echo htmlspecialchars($datos_usuario['telefono'][1]); ?>"></td>
                                <td><b>Fecha Nacimiento</b></td>
                                <td><input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" value="<?php echo htmlspecialchars($datos_usuario['fecha_nacimiento'][1]); ?>"></td>
                            </tr>
                            <tr>
                                <td><b>Lugar Nacimiento</b></td>
                                <td><input type="text" class="form-control" id="lugar_nacimiento" name="lugar_nacimiento" value="<?php echo htmlspecialchars($datos_usuario['lugar_nacimiento'][1]); ?>"></td>
                                <td><b>Peso</b></td>
                                <td><input type="number" step="0.01"  class="form-control" id="peso" name="peso" value="<?php echo htmlspecialchars($datos_usuario['peso'][1]); ?>"></td>
                            </tr>
                            <tr>
                                <td><b>Estatura</b></td>
                                <td><input type="number" step="0.01" class="form-control" id="estatura" name="estatura" value="<?php echo htmlspecialchars($datos_usuario['estatura'][1]); ?>"></td>
                                <td><b>Nivel Estudios</b></td>
                                <td><input type="text" class="form-control" id="nivel_estudios" name="nivel_estudios" value="<?php echo htmlspecialchars($datos_usuario['nivel_estudios'][1]); ?>"></td>
                            </tr>
                            <tr>
                                <td><b>Ocupación</b></td>
                                <td><input type="text" class="form-control" id="ocupacion" name="ocupacion" value="<?php echo htmlspecialchars($datos_usuario['ocupacion'][1]); ?>"></td>
                                <td><b>Qué estudia</b></td>
                                <td><input type="text" class="form-control" id="que_estudia" name="que_estudia" value="<?php echo htmlspecialchars($datos_usuario['que_estudia'][1]); ?>"></td>
                            </tr>
                            <tr>
                                <td><b>Profesión</b></td>
                                <td><input type="text" class="form-control" id="profesion" name="profesion" value="<?php echo htmlspecialchars($datos_usuario['profesion'][1]); ?>"></td>
                                <td><b>Puesto</b></td>
                                <td><input type="text" class="form-control" id="puesto" name="puesto" value="<?php echo htmlspecialchars($datos_usuario['puesto'][1]); ?>"></td>
                            </tr>
                            <tr>
                                <td><b>Interés</b></td>
                                <td><input type="text" class="form-control" id="interes" name="interes" value="<?php echo htmlspecialchars($datos_usuario['interes'][1]); ?>"></td>
                                <td><b>Disciplinas</b></td>
                                <td><input type="text" class="form-control" id="disciplinas" name="disciplinas" value="<?php echo htmlspecialchars($datos_usuario['disciplinas'][1]); ?>"></td>
                            </tr>
                        </table>
                        </form>

                        <!-- TABLA CON LOS PROBLEMAS DE SALUD -->
                    <hr>
                    <h3>Problemas de salud</h3>
                    <table class="tabla_usuarios">
                        <tr>
                            <th>Nombre problema</th>
                            <th style="text-align: center;">Adjunto</th>
                        </tr>
                        <?php
                            if (count($usuario->getPsalud())==0) { // Comprueba que haya problemas de salud en este usuario
                                echo('<tr><td colspan="2">Sin problemas de salud</td></tr>');
                            }else{
                                foreach ($usuario->getPsalud() as $key => $value) { // Si tiene problemas en la lista, recorremos la lista entera

                                    // Guardamos los datos en variables para trabajar facilmente con ellos
                                    $enfermedad=$value['enfermedad'];
                                    $adjunto=$value['adjunto'];

                                    // Imprimimos en la fila de la tabla el nombre de la enfermedad
                                    echo("
                                        <tr>
                                            <td>$enfermedad</td>
                                            
                                    ");
                                    
                                    // COmprobamos que el adjunto no sea null, si no lo es, añadimos un pequeño formulario que nos lleva a verpdf.php para ver el adjunto con el comprobante de la lesion o enfermedad
                                    // Si es null, solo añadimos un texto diciendo que no tiene adjunto
                                    if (!is_null($adjunto)) {
                                        echo("
                                        <td>
                                            <form action='verpdf.php' method='POST' enctype='multipart/form-data' class='upload-form'>
                                                <input type='hidden' name='archivo' value='".$value['adjunto']."'>
                                                <button type='submit' class='icon-button'>
                                                    <i class='fa fa-file-pdf-o' aria-hidden='true'></i>
                                                </button>
                                            </form>
                                        </td>
                                        ");
                                    }else{
                                        echo("
                                        <td>
                                            Sin documento adjunto
                                        </td>
                                        ");
                                    }
                                    echo("</tr>");
                                }
                            }
                            
                        ?>
                    </table>
                        
                    </div>

                    



                    <style>
                        #botonact{
                            margin-left: 25px;
                            margin-bottom: 25px;
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
                            border: 1px solid #dddddd; /* Línea divisoria */
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
                             /* Resaltar */
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

                        .icon-button {
                            background: none; /* Sin fondo */
                            border: none; /* Sin borde */
                            padding: 0; /* Sin padding */
                            cursor: pointer; /* Cambia el cursor al estilo de clic */
                        }

                        .icon-button i {
                            font-size: 1.5em; /* Aumenta el tamaño del ícono */
                            color: red;/* Cambia el color si lo deseas */
                        }

                        .icon-button:hover i {
                            scale: 1.5;
                        }
                        
                        <?php
                            // Si el usuario es profesor y no admin, deshabilitamos todos los inputs y selects para que no pueda modificar nada
                            if ($_SESSION['roluser']==2) {
                                echo("input{
                                    pointer-events: none;
                                    opacity: 0.8;
                                }
                                select{
                                    pointer-events: none;
                                    opacity: 0.8;
                                }    
                                ");
                            }
                        ?>
                        

                        

                    </style>
                </div>
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

