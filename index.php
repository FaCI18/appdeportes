<?php
include 'sesion.php';

// Comprueba que la sesion este iniciada
if (!isset($_SESSION['iduser'])) {
    header("Location: login.php");
}

// Incluir la conexión a la base de datos
include 'conexion.php';

// Consulta para obtener todas las noticias del admin ordenadas por id para que las muestre en orden de mas recientes a mas antiguas
$sql = "SELECT * FROM noticias ORDER BY id_noticia DESC LIMIT 10";
$result = $conn->query($sql); // Ejecutamos consulta

// Variable con la lista para las noticias
$noticias = [];

    if ($result->num_rows > 0) { // Comprobamos que hay resultados
        while($row = $result->fetch_assoc()) { // Recorremos los resultados
            $noticias[$row['id_noticia']] = [
                "fecha" => $row['fecha'],
                "noticia" => $row['noticia']
            ];
        }
    }

// Añadimos a la lista las imagenes del index las cuales puede modificar el Admin

// Consulta para extraer las imagenes
$sql = "SELECT * FROM imgIndice";
$result = $conn->query($sql); // Ejecutamos consultas

// Lista para almacenar la url de las imagenes
$imagenes = [];

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) { // Recorremos lel resultado de la consulta
            $imagenes[$row['id_imagen']] = [
                "url_imagen" => $row['url_imagen']
            ];
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
	<?php include 'menu.php'; ?>
    <!-- =============== About =============== -->
    <section id="about" class="">
		<!-- =============== container =============== -->
        <div class="container" style="margin-top: 150px;">            
    <div class="row">

        <!-- Columna para las imágenes -->
        <div class="col-xs-12 col-sm-12 col-md-6 wow fadeInDown animated" data-wow-delay=".5s" >
            <div class="row">
                <?php
                    // $imagenes siempre es un array con 4 imágenes
                    // Añadimos un div por imagen
                    foreach ($imagenes as $key => $value) { // Recorremos la lista
                        $imagen = $value['url_imagen'];
                        if ($imagen == null || $imagen == '') {
                            echo('<div class="col-md-6 mb-4"><p>No hay imagen</p></div>');  // En caso de que no haya imagen
                        } else {
                            echo("<div style='margin-bottom: 5px' class='col-md-6 mb-4'><img class='imagenIndex img-fluid' src='$imagen' alt='Imagen $key' style='width: 100%;'></div>");
                        }
                    }
                ?>
            </div>
        </div>

        <!-- Columna con una tabla para las noticias -->
        <div class="col-xs-12 col-sm-12 col-md-6 wow fadeInRight animated" data-wow-delay=".5s">
            <center>
                <h3 style="margin-top: 80px;">Noticias:</h3>
            </center>
            <table class="tabla_usuarios">
                <tr>
                    <th>Fecha</th>
                    <th>Noticia</th>
                </tr>
                <?php

                    // comprueba que haya noticias en la lista
                    if (count($noticias) > 0) {
                        foreach ($noticias as $key => $value) { // Recorremos la lista

                            // Añadimos una fila a la tabla con fecha y la noticia
                            echo("
                                <tr>
                                    <td>" . $value['fecha'] . "</td>
                                    <td>" . $value['noticia'] . "</td>
                                </tr>
                            ");
                        }
                    } else {
                        echo "<tr><td colspan='2'>No se encontraron noticias.</td></tr>";
                    }
                ?>
            </table>           
        </div>

    </div>
</div>

 
		<!-- =============== container end =============== -->		
    </section>

    <style>
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

