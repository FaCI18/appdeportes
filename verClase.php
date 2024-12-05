<?php
    include 'sesion.php';


    // COmprueba que la sesion este inciada
    if (!isset($_SESSION['iduser'])) {
        header("Location: login.php");
        exit();
    }

    // Comprobacion que recibamos el id de clase
    if (!isset($_GET['idclase'])) {
        header("Location: index.php");
        exit();
    }

     // Incluir la conexión a la base de datos
    include 'conexion.php';

     // Extraer datos de la clase mediante consulta
    $sql = "SELECT 
        c.id_clase,
        c.nombre,
        c.descripcion,
        c.fecha_clase,
        h.hora AS hora,
        c.cupos,
        c.precio
    FROM 
        clases c
    INNER JOIN 
        horario h ON c.id_horario = h.id_horario
    WHERE 
        id_clase = ".$_GET['idclase'];


    // Ejecutar consulta
    $result = $conn->query($sql);

    
    // Comprobamos que haya resultados, y los guardamos en variables
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        $id_clase = $row['id_clase'];
        $nombre = $row['nombre'];
        $descripcion = $row['descripcion'];
        $fecha_clase = $row['fecha_clase'];
        $hora = $row['hora'];
        $cupos = $row['cupos'];
        $precio = $row['precio'];

    }else{
        header("Location: gestionClases.php");
        exit();
    }

    // Comprobar cuantos inscritos hay a esta clase
    $sql_cupos = "SELECT COUNT(id_solicitud) AS total FROM solicitudes WHERE id_clase=".$id_clase; // Consulta con el COUNT
    $result_clases = $conn->query($sql_cupos); // Ejecutamos consulta

    if ($result_clases->num_rows > 0) { // Comprobamos resultados y lo guardamos en una variable
        $row = $result_clases->fetch_assoc();
        $inscritos = $row['total'];
    } else {
        $inscritos = 0;
    }

    // Comprobar si el usuario que revisa esta inscrito en la clase
    $sql_estado = "SELECT id_solicitud FROM solicitudes WHERE id_clase=".$id_clase." AND id_usuario=".$_SESSION['iduser']; // Consulta
    $result_estado = $conn->query($sql_estado); // Ejecutamos consulta

    // Comprobamos resultados y lo guardamos en una variable
    if ($result_estado->num_rows > 0) {
        $row = $result_estado->fetch_assoc();
        $estado = 'inscrito';
    } else {
        $estado = 'noinscrito';
    }

    // Lista de usuarios inscritos junto a sus datos
    $lista_usuarios = [];
    $sql_usuarios = "SELECT 
        solicitudes.id_solicitud,
        solicitudes.estado_pago,
        solicitudes.adjunto,
        solicitudes.id_solicitud_paquete,
        usuarios.id_usuario,
        usuarios.nombre,
        usuarios.apellido_materno,
        usuarios.apellido_paterno,
        usuarios.activado
    FROM 
        solicitudes
    JOIN 
        usuarios ON solicitudes.id_usuario = usuarios.id_usuario
    WHERE 
        solicitudes.id_clase = $id_clase;
    ";
    $result_usuarios = $conn->query($sql_usuarios); // Ejecutamos consulta

    if ($result_usuarios->num_rows > 0) { // COmprobamos los resultados
        while($row = $result_usuarios->fetch_assoc()) { // Recorremos los resultados y los guardamos en una lista
           $lista_usuarios[$row['id_usuario']] = [
            "nombre" => $row['nombre'],
            "apellido_materno" => $row['apellido_materno'],
            "apellido_paterno" => $row['apellido_paterno'],
            "estado_pago" => $row['estado_pago'],
            "adjunto" => $row['adjunto'],
            "activado" => $row['activado'],
            "id_solicitud_paquete" => $row['id_solicitud_paquete']
           ];
        }
    }


    // Lista de profesores
    $lista_profesores = [];
    $sql_profesores = "SELECT 
        u.id_usuario,
        u.nombre,
        u.apellido_paterno,
        u.apellido_materno,
        u.email,
        u.activado
    FROM 
        clases_profesores cp
    INNER JOIN 
        usuarios u ON cp.id_profesor = u.id_usuario
    WHERE 
        cp.id_clase = ".$_GET['idclase'].";
    ";
    $result_profesores = $conn->query($sql_profesores); // Ejecutamos la consulta

    if ($result_profesores->num_rows > 0) { // Comprobamos que haya resultados
        while($row = $result_profesores->fetch_assoc()) { // Recorremos los resultados y los guardamos en una lista
           $lista_profesores[$row['id_usuario']] = [
            "nombre" => $row['nombre'],
            "apellido_materno" => $row['apellido_materno'],
            "apellido_paterno" => $row['apellido_paterno'],
            "email" => $row['email'],
            "activado" => $row['activado']
           ];
        }
    }

    // Cerrar la conexión
    $conn->close();
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
                
                <div class="col-md-12">
                    <div style="margin-top: 25px;">
                        <h3><?php echo($nombre) ?></h3> <!-- Imprimimos el nombre de la clase -->

                        <!-- Mostramos al alumno el estado actual, si esta suscrito o no -->
                         <?php

                            // Mostramos un formulario con un boton de inscribirse si no esta suscrito, si lo esta, el boton se vuelve desactivado y en color verde mostrando que ya esta inscrito
                            if($_SESSION['roluser']==3){
                                if($estado=='inscrito'){
                                    echo("
                                    <form id='inscribirse' action='inscripcion.php' method='post'>
                                        <input type='hidden' name='tipo' value='clase'>
                                        <button type='submit' class='btn btn-success mb-2' disabled>Ya inscrito</button>
                                    </form>
                                ");
                                }else{

                                    // Comprobamos las fechas y si la clase es de hoy o anterior, no permitimos tampoco inscribirse debido a que debe inscribirse con un dia al menos de antelacion
                                    $hoy = new DateTime();
                                    $fclase = new DateTime($fecha_clase);

                                    if ($fclase<=$hoy) { // Comparacion de las fechas 
                                        echo("
                                            <form id='inscribirse' action='inscripcion.php' method='post'>
                                                <input type='hidden' name='tipo' value='clase'>
                                                <input type='hidden' name='idclase' value='".$_GET['idclase']."'>
                                                <input type='hidden' name='cupos' value='".$cupos."'>
                                                <button type='submit' class='btn btn-danger mb-2' disabled>Clase finalizada</button>
                                            </form>
                                        ");
                                    }else{
                                        echo("
                                        <form id='inscribirse' action='inscripcion.php' method='post'>
                                            <input type='hidden' name='tipo' value='clase'>
                                            <input type='hidden' name='idclase' value='".$_GET['idclase']."'>
                                            <input type='hidden' name='regresar' value='verclase.php?idclase=".$id_clase."'>
                                            <input type='hidden' name='iduserinscripcion' value='".$_SESSION['iduser']."'>
                                            <input type='hidden' name='cupos' value='".$cupos."'>
                                            <button type='submit' class='btn btn-primary mb-2'>Inscribirse a la clase</button>
                                        </form>
                                    ");
                                    }
                                    
                                }
                                
                            } 
                         ?>
                        <table class="tabla_clases">
                        <!-- Tabla con los datos de la clase, así como los cupos que quedan -->
                            <tr>
                                <td><b>Fecha de clase</b></td>
                                <td><?php echo($fecha_clase) ?></td>
                                <td><b>Hora de inicio</b></td>
                                <td><?php echo($hora) ?></td>
                            </tr>
                            <hr>
                            <tr>
                                <td><b>Alumnos inscritos:</b></td>
                                <td><?php echo($inscritos." / <b>".$cupos."</b>") ?></td>
                                <td><b>Precio</b></td>
                                <td><b>$</b><?php echo($precio) ?></td>
                            </tr>
                        </table>
                        <table class="tabla_clases">
                            <tr>
                                <td colspan="4" style="text-align: center;"><b>Descripcion</b></td>
                            </tr>
                            <tr>
                                <td colspan="4" style="text-align: left;"><?php echo($descripcion) ?></td>
                            </tr>

                        </table>
                        <hr>
                        
                        <h3 style='margin-top: 50px'>Profesores de esta clase</h3>
                        <table class="tabla_clases" style="width: 900px">
                            <tr>
                                <th>Nombre</th>
                                <th>Email</th>
                            </tr>
                            <?php
                            // Aqui añadimos los profesores de la clase a una tabla
                            if (count($lista_profesores)>0) { // Comprobamos que haya profesores en la lista
                                foreach ($lista_profesores as $key => $value) { // Recorremos la lista
                                    $pnombre = $value['nombre'];
                                    $ppaterno = $value['apellido_paterno'];
                                    $pmaterno = $value['apellido_materno'];
                                    $pmail = $value['email'];

                                    // Establecemos la opacidad al 60% si el usuario esta desactivado
                                    if ($value['activado']==1) {
                                        $profeEstado = '';
                                    }else{
                                        $profeEstado = 'style="opacity: 0.6"';
                                    }
                                    echo("
                                        <tr $profeEstado>
                                            <td>$pnombre $ppaterno $pmaterno</td>
                                            <td>$pmail</td>
                                        </tr>
                                    ");
                                } 
                            }
                                
                            ?>
                        </table>
                        <hr>

                        <?php

                            
                            
                            // Si el usuario es Admin o Profesor, mostramos una lista de los usuarios inscritos
                            if ($_SESSION['roluser']<3){
                                echo("<h3 style='margin-top: 50px'>Lista de usuarios</h3>");

                                // Comprueba que haya usuarios en la lista
                                if(count($lista_usuarios)>0){
                                    echo("<table class='tabla_clases' style='width: 1200px'>");
                                    echo("<tr>
                                        <th>Ver perfil</th>
                                    ");
                                    echo("<th>Nombre</th>
                                        <th>Estado inscripcion</th>");
                                    echo("<th>Mensaje</th>");
                                    echo("</tr>");

                                    // Recorremos la lista de usuarios
                                    foreach ($lista_usuarios as $key => $value) {

                                        // Establecemos la opacidad al 60% si el usuario esta desactivado
                                        if ($value['activado']==1) {
                                            $alumnoEstado = '';
                                        }else{
                                            $alumnoEstado = 'style="opacity: 0.6"';
                                        }

                                        // Añadimos el enlace para ver el perfil del usuario
                                        echo("
                                            <tr $alumnoEstado>
                                                <td ><a href='"."verperfil.php?usrid=$key'><i class='fa fa-eye' aria-hidden='true'></i></a></td>
                                                <td>".$value['nombre']." ".$value['apellido_paterno']." ".$value['apellido_materno']."</td>
                                        ");

                                        // Si el estado pago es TRUE comprobamos si tiene adjunto, si lo tiene es un pago realizado y validado, si no tiene adjunto significa que fue un canje de un paquete
                                        // Si es FALSE se comprueba si tiene adjunto, si tiene adjunto significa que esta pendiente de validacion, si no tiene significa que esta pendiente de pago
                                        if($value['estado_pago']==true){
                                            if (is_null($value['adjunto'])) {
                                                echo("<td>Pago realizado mediante canje de paquete</td>");
                                            }else{
                                                echo("<td>Pago validado</td>");
                                            }
                                            
                                        }else{
                                            if (is_null($value['adjunto'])) {
                                                echo("<td>Pendiente de pago</td>");
                                            }else{
                                                echo("<td>Pago pendiente de validacion</td>");
                                            }
                                            
                                        }

                                        // Añadimos por ultimo un boton para enviar mensajes
                                        // El cual SOLO permite enviar mensaje si es profesor, si no, esta desactivado
                                        if ($_SESSION['roluser']==2) {
                                            if ($value['activado']==1) {
                                                $editar = '';
                                            }else{
                                                $editar = 'disabled';
                                            }
                                            
                                        }else{
                                            $editar = 'disabled';
                                        }
                                        
                                        // Imprimimos el boton
                                        echo("
                                            <td class='subearchivo' style='text-align: center'>
                                                <form action='mandarMensaje.php' method='POST' enctype='multipart/form-data' class='upload-form'>
                                                    <input type='hidden' name='receptor' value='$key'>
                                                    <input type='hidden' name='idclase' value='".$_GET['idclase']."'>
                                                    <button type='submit' class='icon-button' $editar>
                                                        <i class='fa fa-envelope-o' aria-hidden='true'></i>
                                                    </button>
                                                </form>
                                            </td>
                                        ");
                                        


                                        echo("</tr>");
                                    }
                                    echo("</table>");
                                }else{
                                    echo("No hay usuarios inscritos en este momento");
                                }
                               
                            } 
                        ?>
                    </div>

                    



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
                        .tabla_clases {
                            width: 60%;
                            border-collapse: collapse; /* Elimina los espacios entre bordes */
                            font-family: Arial, sans-serif;
                            font-size: 14px;
                            text-align: left;
                            margin: 20px 0;
                            background-color: #ffffff; /* Fondo blanco */
                            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Sombra ligera */
                            overflow: hidden;
                            margin-top: 20px;
                        }

                        /* Estilo para las cabeceras */
                        .tabla_clases th {
                            background-color: #f5bc42; /* Verde elegante */
                            color: black; /* Texto blanco */
                            padding: 12px; /* Espaciado interno */
                            text-transform: uppercase; /* Texto en mayúsculas */
                            letter-spacing: 1px; /* Separación de letras */
                        }

                        /* Estilo para las filas */
                        .tabla_clases td {
                            padding: 12px; /* Espaciado interno */
                            border-bottom: 1px solid #dddddd; /* Línea divisoria */
                            color: #333; /* Texto oscuro */
                        }

                        /* Cambiar el color de fondo en las filas pares */
                        .tabla_clases tr:nth-child(even) {
                            background-color: #f2f2f2; /* Fondo gris claro */
                        }

                        /* Resaltar fila al pasar el mouse */
                        .tabla_clases tr:hover {
                            background-color: #ffedc7; /* Fondo verde claro */
                        }

                        

                        /* Botones o enlaces dentro de la tabla */
                        .tabla_clases td a {
                            text-decoration: none; /* Eliminar subrayado */
                            color: #f5bc42; /* Color verde */
                            padding: 6px 12px; /* Espaciado interno */
                            border: 1px solid #1a1a1a; /* Borde verde */
                            border-radius: 4px; /* Bordes redondeados */
                            transition: all 0.3s ease; /* Transición suave */
                        }

                        .tabla_clases td a:hover {
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
                            color: black; /* Cambia el color si lo deseas */
                        }

                        .icon-button:hover i {
                            color: orangered; /* Cambia el color al pasar el ratón (opcional) */
                            scale: 1.5;
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

