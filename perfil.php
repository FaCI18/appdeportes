<?php
    include 'sesion.php';
    include 'usuario.php'; // Clase usuario simplemente para facilitar el recibir los datos


    // Comprueba que la sesion este iniciada
    if (!isset($_SESSION['iduser'])) {
        header("Location: login.php");
        exit();
    }

    $iduser = $_SESSION['iduser']; // Guardamos el usuario en una variable
    $usuario = new Usuario($iduser); // Creamos una instancia de usuario para recibir datos sin necesidad de hacer demasiadas consultas cada vez
    $datos_usuario = $usuario->getDatos(); // Guardamos los datos en una variable
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
                <center class="col-md-12">
                <h1>Perfil</h1>
                <div class="col-md-12">
                    <div style="margin-top: 25px;">
                        
                        <h3>Datos Básicos</h3>

                        <!-- Tabla donde vamos a ir imprimiendo los datos del usuario mediante el echo (Utilizamos la funcion htmlspecialchars que lo unico que hace es convertir los caracteres especiales para poder leerlos en html) -->
                        <table class="tabla_usuarios">
                            <tr>
                                <th>Campo</th>
                                <th>Dato</th>
                                <th>Campo</th>
                                <th>Dato</th>
                            </tr>
                            <tr>
                                <td><b>Nombre</b></td>
                                <td><?php echo htmlspecialchars($usuario->getNombre()); ?></td>
                                <td><b>Apellido Materno</b></td>
                                <td><?php echo htmlspecialchars($usuario->getApellido_materno()); ?></td>
                            </tr>
                            <tr>
                                <td><b>Apellido Paterno</b></td>
                                <td><?php echo htmlspecialchars($usuario->getApellido_paterno()); ?></td>
                                <td><b>Email</b></td>
                                <td><?php echo htmlspecialchars($usuario->getEmail()); ?></td>
                            </tr>
                            <tr>
                                <td><b>Contraseña</b></td>
                                <!-- Un pequeño formulario que permite modificar la contraseña por otra que el usuario elija -->
                                <td  <?php if ($_SESSION['roluser']!=3){echo('colspan="3" style="text-align: left"');}?>{ class="subearchivo">
                                    <form id="actPass" action="actualizapass.php" method="post">
                                        <input type="password" class="form-control-plaintext" id="pass" name="pass" required>
                                        <button type='submit' class='btn btn-primary'>
                                            Cambiar contraseña
                                        </button>
                                    </form>
                                </td>
                                <?php
                                if ($_SESSION['roluser']==3){ // Solo para los alumnos, un mini formulario con un enlace para ver el deslinde de responsabilidades en pdf y un boton para aceptarlo
                                    echo('
                                    <td><b>Deslinde de responsabilidad</b></td>
                                    <td class="subearchivo">
                                    <form action="verpdf.php" method="post" style="display: inline;" class="upload-form">
                                        <input type="hidden" name="archivo" value="files/responsabilidades/deslinde_responsabilidades.pdf">
                                        <button type="submit" class="icon-button" style="padding: 0; color: red;" >
                                            <i class="fa fa-file-pdf-o" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                    <form action="firmaResponsabilidades.php" method="post" style="display: inline;" class="upload-form">
                                ');
                                    
                                    
                                    // Comprueba si el deslinde esta firmado o no, para deshabilitar el boton de firmar
                                    if ($usuario->getResponsabilidades()==0) {
                                        echo('<button type="submit" class="btn btn-primary" style="margin-left: 10px;">Firmar responsabilidades</button>');
                                    }else{
                                        echo('<button type="submit" class="btn btn-success" style="margin-left: 10px;" disabled>Firmado</button>');
                                    }
                                    echo('</form>');
                                }
                                
                                ?>
                                        
                                    
                                </td>
                            </tr>
                        </table>

 
                    </div>
                    
                    <!-- DATOS ESPECIFICOS -->
                    <!-- Una tabla donde mostramos los datos de la tabla datos y datos_usuario -->
                    <!-- Solo se muestra si eres Alumno -->
                    <?php
                    if ($_SESSION['roluser']==3) {
                    ?>
                    <hr>
                    <h3>Datos Personales</h3>
                    <table class="tabla_usuarios">
                        <tr>
                            <th>Campo</th>
                            <th>Dato</th>
                            <th>Campo</th>
                            <th>Dato</th>
                        </tr>
                            <tr>
                                <td><b>Domicilio</b></td>
                                <td><?php echo htmlspecialchars($datos_usuario['domicilio'][1]); ?></td>
                                <td><b>Teléfono</b></td>
                                <td><?php echo htmlspecialchars($datos_usuario['telefono'][1]); ?></td>
                            </tr>
                            <tr>
                                <td><b>Fecha Nacimiento</b></td>
                                <td><?php echo htmlspecialchars($datos_usuario['fecha_nacimiento'][1]); ?></td>
                                <td><b>Lugar Nacimiento</b></td>
                                <td><?php echo htmlspecialchars($datos_usuario['lugar_nacimiento'][1]); ?></td>
                            </tr>
                            <tr>
                                <td><b>Peso</b></td>
                                <td><?php echo htmlspecialchars($datos_usuario['peso'][1]);?></td>
                                <td><b>Estatura</b></td>
                                <td><?php echo htmlspecialchars($datos_usuario['estatura'][1]); ?></td>
                            </tr>
                            <tr>
                                <td><b>Nivel Estudios</b></td>
                                <td><?php echo htmlspecialchars($datos_usuario['nivel_estudios'][1]); ?></td>
                                <td><b>Ocupación</b></td>
                                <td><?php echo htmlspecialchars($datos_usuario['ocupacion'][1]); ?></td>
                            </tr>
                            <tr>
                                <td><b>Qué estudia</b></td>
                                <td><?php echo htmlspecialchars($datos_usuario['que_estudia'][1]); ?></td>
                                <td><b>Profesión</b></td>
                                <td><?php echo htmlspecialchars($datos_usuario['profesion'][1]); ?></td>
                            </tr>
                            <tr>
                                <td><b>Puesto</b></td>
                                <td><?php echo htmlspecialchars($datos_usuario['puesto'][1]); ?></td>
                                <td><b>Interés</b></td>
                                <td><?php echo htmlspecialchars($datos_usuario['interes'][1]); ?></td>
                            </tr>
                            <tr>
                                <td><b>Disciplinas</b></td>
                                <td colspan="3" style="text-align: center;"><?php echo htmlspecialchars($datos_usuario['disciplinas'][1]); ?></td>
                                
                            </tr>
                        </table>

                    <!-- PROBLEMAS DE SALUD -->
                    <hr>
                    <h3>Problemas de salud</h3>
                    <!-- Una tabla donde mostramos uno a uno los problemas de salud dados de alta por el usuario -->
                    <table class="tabla_usuarios">
                        <tr>
                            <th>Nombre problema</th>
                            <th style="text-align: center;">Adjunto</th>
                            <th></th>
                        </tr>

                        <!-- Este formulario permite registrar problemas de salud, incluyendo un nombre de la lesion o enfermedad y un archivo adjunto NO obligatorio-->
                        <form action='registraPsalud.php' method='POST' enctype='multipart/form-data' class='upload-form'>
                            <tr><label>Agregar problema</label></tr>
                            <tr>
                                <td><input type='text' class="form-control" name='nombre_psalud' placeholder='Introduzca el nombre del problema' required></td>
                                <td><input type='file' name='archivo' accept='application/pdf' class='file-input'></td>
                                <td><button type='submit' class='btn btn-primary'>Registrar</button></td>
                            </tr>
                        </form>
                        <?php
                            // Recorremos la lista de problemas de salud
                            foreach ($usuario->getPsalud() as $key => $value) {

                                // Guardamos en variables los datos para facilitar trabajar con ellos
                                $enfermedad=$value['enfermedad'];
                                $adjunto=$value['adjunto'];

                                // Imprimimos la primera parte de la fila con el nombre de la enfermedad o lesion
                                echo("
                                    <tr>
                                        <td colspan='2'>$enfermedad</td>
                                        
                                ");

                                // Comprobamos si existe archivo adjunto, si existe añade un mini formulario con unicamente un boton que lleva a verpdf.php
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
                                    // Si NO existe archivo adjunto, añáde un texto que dice que no hay adjunto
                                    echo("
                                    <td style='text-align: left'>
                                        Sin documento adjunto
                                    </td>
                                    ");
                                }
                                echo("</tr>");
                            }
                        ?>
                    </table>

                    <?php
                        } // Fin de la condición if para mostrar las secciones
                        ?>

                    <style>

                        #botonact{
                            margin-left: 25px;
                            margin-bottom: 25px;
                        }

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

                        .upload-form {
                            display: flex;
                            align-items: center;
                            gap: 10px;
                        }

                        .file-input {
                            display: none; /* Esconde el input de archivo */
                        }

                        .subearchivo {
                            text-align: center;
                        }

                        .upload-btn {
                            background: none;
                            border: none;
                            cursor: pointer;
                            font-size: 1.2em;
                        }
                        .upload-btn:disabled {
                            cursor: not-allowed;
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

                        .subearchivo {
                            text-align: center;
                        }
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

