<?php
include 'sesion.php';


if (!isset($_SESSION['iduser'])) {
    header("Location: index.php");
}

if ($_SESSION['roluser']!=3) {
    header("Location: index.php");
}

// Incluir la conexión a la base de datos
include 'conexion.php';


// Consulta seleccionando todas las notificaciones cuyo receptor sea nuestro usuario
// Utilizamos JOIN para obtener datos de otras tablas, como por ejemplo los datos del emisor (profesor que envió la notificacion)
// Y datos de la clase de donde viene la notificación
$sql = "SELECT 
    n.id_notificacion,
    n.id_emisor,
    u.nombre AS nombre_emisor,
    u.apellido_paterno AS apellido_paterno_emisor,
    u.apellido_materno AS apellido_materno_emisor,
    n.id_receptor,
    n.asunto,
    n.mensaje,
    n.adjunto,
    n.id_clase,
    c.nombre AS nombre_clase,
    s.estado_pago AS estado_solicitud
FROM 
    notificaciones n
LEFT JOIN 
    usuarios u ON n.id_emisor = u.id_usuario
LEFT JOIN 
    clases c ON n.id_clase = c.id_clase
LEFT JOIN 
    solicitudes s 
    ON n.id_clase = s.id_clase AND s.id_usuario = n.id_receptor
WHERE 
    n.id_receptor = ".$_SESSION['iduser']."
    AND n.id_clase IS NOT NULL;";


$result = $conn->query($sql); // Ejecutamos la consulta

// Variables donde guardamos la lista de notificaciones
// La lista de mensajes prohibidos son aquellos cuya cuota no hayamos pagado todavia
$listaMensajes = [];
$listaMensajesProhibidos = [];
$listaBorrar = [];
$cont=0; // Contador de notificaciones 

if ($result->num_rows > 0) { // Comprueba si hay resultados
        // Hay resultados
        while($row = $result->fetch_assoc()) { // Recorre los resultados

            if ($row['estado_solicitud']) { // Comprueba si la solicitud esta pagada
                
                $listaBorrar[$cont]=$row['id_notificacion']; // Añade su id a una lista para posteriormente borrarla y no almacenarla
                $cont = $cont+1;

                // Añade la notificacion a la lista 
                $listaMensajes[$row['id_notificacion']] = [
                    'nombre_profesor' => $row['nombre_emisor'] . " " . $row['apellido_paterno_emisor'] . " ". $row['apellido_materno_emisor'],
                    'asunto' => $row['asunto'],
                    'mensaje' => $row['mensaje'],
                    'adjunto' => $row['adjunto'],
                    'nombreClase' => $row['nombre_clase']
                ];
            }else{ // Notificaciones bloqueadas hasta que el usuario pague la cuota
                $listaMensajesProhibidos[$row['id_notificacion']] = [
                    'nombre_profesor' => $row['nombre_emisor'] . " " . $row['apellido_paterno_emisor'] . " ". $row['apellido_materno_emisor'],
                    'asunto' => $row['asunto'],
                    'mensaje' => $row['mensaje'],
                    'adjunto' => $row['adjunto'],
                    'nombreClase' => $row['nombre_clase']
                ];
            }

            
        }
    }

    // Borra las notificaciones de la lista borrar recorriendo dicha lista
foreach ($listaBorrar as $key => $value) {
    $sqlBorrar = "DELETE FROM notificaciones WHERE id_notificacion=".$value; // Consulta
    $resultBorrar = $conn->query($sqlBorrar); // Ejecutar consulta
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

            <style>
    .notification-card {
        max-width: 600px;
        margin: 20px auto;
        padding: 0;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        font-family: 'Arial', sans-serif;
        color: #333;
        overflow: hidden;
    }
    .notification-header {
        background-color: #f5bc42;
        padding: 15px;
        font-size: 18px;
        font-weight: bold;
        color: #fff;
        text-align: center;
    }
    .notification-body {
        padding: 15px;
    }
    .notification-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px;
        border-top: 1px solid #ddd;
        background-color: #ffedc7;
    }
    .notification-link {
        padding: 8px 15px;
        background-color: #007BFF;
        color: #fff;
        text-decoration: none;
        border-radius: 5px;
        transition: background-color 0.3s ease;
        border: none;
        cursor: pointer;
    }
    .notification-link:hover {
        background-color: #0056b3;
    }
    .notification-info {
        font-size: 14px;
        color: #555;
    }

    .icon-button {
        background: none; /* Sin fondo */
        border: none; /* Sin borde */
        padding: 0; /* Sin padding */
        cursor: pointer; /* Cambia el cursor al estilo de clic */
    }

    .icon-button i {
        font-size: 1.5em; /* Aumenta el tamaño del ícono */
        color: red; /* Cambia el color si lo deseas */
    }

    .icon-button:hover i {
        color: darkred; /* Cambia el color al pasar el ratón (opcional) */
        scale: 1.5;
    }

    .headerbloqueado{
        background-color: darkred;
    }

    .footerbloqueado{
        background-color: salmon;
    }

</style>

<center><h1 style="margin-top: 20px;">Notificaciones</h1></center>
<center><h3><i style="color: red; scale: 1.2" class="fa fa-exclamation-triangle" aria-hidden="true"></i> Aviso: Las notificaciones se borran al abandonar esta página <i style="color: red; scale: 1.2" class="fa fa-exclamation-triangle" aria-hidden="true"></i></h3></center>
<center>
    <h3>Notificaciones pendientes</h3>
    <?php foreach ($listaMensajes as $key => $value) { ?>
        <?php

            // Cada notificacion es un div
            // Comprueba el nombre de la clase y lo añade a iuna variable 
            
            $nombreElemento = 'Clase: ' . $value['nombreClase'];
            
        ?>
        <div class="notification-card">
            <!-- Asunto -->
            <div class="notification-header">
                <?= htmlspecialchars($value['asunto']) ?> <!-- Agregamos el asunto de la notificacion -->
            </div>
            <!-- Mensaje -->
            
            <!-- Footer -->
            <div class="notification-footer" style="display: block; text-align: left">
                <div style="display: block;">
                    <b>Profesor:</b> <?= htmlspecialchars($value['nombre_profesor']) ?> <!-- Agregamos el nombre de quien mando la notificacion -->
                </div>
                <?php if ($nombreElemento): ?>
                <div style="display: block;">
                    <b><?= htmlspecialchars($nombreElemento) ?></b> <!-- Agregamos el nombre de la clase -->
                </div>
                <?php endif; ?>
                
                
            </div>
            <div class="notification-body">
                <p><?= htmlspecialchars($value['mensaje']) ?></p> <!-- Agregamos el texto de la notificacion -->
                <div style="text-align: left;">
                    <hr>
                <?php if (!empty($value['adjunto'])) { ?>
                <form action='verpdf.php' method='POST' enctype='multipart/form-data' class='upload-form' target="_blank" style="display: inline-block; margin: 0px">
                    <input type='hidden' name='archivo' value='<?= htmlspecialchars($value['adjunto']) ?>'> <!-- Agregamos el archivo adjunto de la notificacion en caso de haberlo -->
                    <label for="boton">Archivo adjunto:</label>
                    <button type='submit' id="boton" class='icon-button'>
                        <i class='fa fa-file-pdf-o' aria-hidden='true'></i>
                    </button>
                    </form>
                <?php } ?>
                </div>
                
            </div>

        </div>
    <?php } ?>
</center>

<center>
    <h3>Notificaciones Bloqueadas</h3>
    <?php foreach ($listaMensajesProhibidos as $key => $value) { ?>
        <?php
            // Cada notificacion es un div
            // Comprueba el nombre de la clase y lo añade a iuna variable 

            $nombreElemento = 'Clase: ' . $value['nombreClase'];

            
        ?>
        <div class="notification-card">
            <!-- Asunto -->
            <div class="notification-header headerbloqueado">
                <?= htmlspecialchars($value['asunto']) ?> <!-- Agregamos el asunto de la notificacion -->
            </div>
            <!-- Mensaje -->
            
            <!-- Footer -->
            <div class="notification-footer footerbloqueado" style="display: block; text-align: left">
                <div style="display: block;">
                    <b>Profesor:</b> <?= htmlspecialchars($value['nombre_profesor']) ?> <!-- Agregamos el nombre de quien envio la notificacion -->
                </div>
                <?php if ($nombreElemento): ?>
                <div style="display: block;">
                    <b><?= htmlspecialchars($nombreElemento) ?></b> <!-- Agregamos el nombre de la clase -->
                </div>
                <?php endif; ?>
                
                
            </div>
            <div class="notification-body" style="text-align: center;">
                <i style="color: red; scale: 2" class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                <p>Para poder ver este mensaje, por favor póngase al corriente de pago con esta clase</p> <!-- NO mostramos el mensaje, para verlo debe pagar la clase primer -->
                <div style="text-align: left;">
                    <hr>
                <?php if (!empty($value['adjunto'])) { ?> <!-- NO mostramos el adjunto, para verlo debe pagar la clase primero-->
                <form action='verpdf.php' method='POST' enctype='multipart/form-data' class='upload-form' target="_blank" style="display: inline-block; margin: 0px; opacity: 0.7">
                    <input type='hidden' name='archivo' value='<?= htmlspecialchars($value['adjunto']) ?>'>
                    <label for="boton">Archivo adjunto:</label>
                    <button type='submit' id="boton" class='icon-button' disabled>
                        <i class='fa fa-file-pdf-o' aria-hidden='true'></i>
                    </button>
                    </form>
                <?php } ?>
                </div>
                
            </div>

        </div>
    <?php } ?>
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

