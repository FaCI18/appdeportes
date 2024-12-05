<?php
include 'sesion.php';

// Comprueba que haya iniciado la sesion
if (!isset($_SESSION['iduser'])) {
    header("Location: index.php");
}

// Incluir la conexión a la base de datos
include 'conexion.php';

// Comprueba si se ha usado el formulario de buscaPaquete
// Si se ha usado, tambien se comprueba si existe el checkBox marcado de solo mis paquetes
if (isset($_POST['buscaPaquete'])){
    $busqueda = $_POST['buscaPaquete'];
    $id_usuario = $_SESSION['iduser'];

    // EL checkbox esta marcado para filtrar por solo mis paquetes
    if (isset($_POST['soloMios'])){

        $sql = "SELECT 
            c.id_paquete,
            c.nombre,
            c.descripcion,
            c.fecha_inicio,
            c.fecha_fin,
            c.precio,
            c.activado,
            c.cantidadClases
        FROM 
            paquetes c
        INNER JOIN 
            solicitudes_paquetes sp ON c.id_paquete = sp.id_paquete
        WHERE 
            sp.id_usuario = $id_usuario
            AND c.nombre LIKE '%$busqueda%'
        LIMIT 30;";

    }else{
        $sql = "SELECT 
            c.id_paquete,
            c.nombre,
            c.descripcion,
            c.fecha_inicio,
            c.fecha_fin,
            c.precio,
            c.activado,
            c.cantidadClases
            FROM 
                paquetes c
            WHERE 
                c.nombre LIKE '%$busqueda%'
            LIMIT 30;";
    }

    
    

}else{
    $sql = "SELECT 
        c.id_paquete,
        c.nombre,
        c.descripcion,
        c.fecha_inicio,
        c.fecha_fin,
        c.precio,
        c.activado,
        c.cantidadClases
FROM 
    paquetes c
LIMIT 30;";

}

$result = $conn->query($sql); // Ejecuta la consulta

// Listas con los paquetes
$paquetes_activos = [];
$paquetes_finalizados = [];

if ($result->num_rows > 0) {
        $hoy = new DateTime();
        $manana = (new DateTime())->modify('+1 day');

        // Hay resultados
        while($row = $result->fetch_assoc()) {

            // Comprobamos las fechas
            
            $fechapaquete = new DateTime($row['fecha_fin']);
            
            if ($fechapaquete<=$hoy) {
                $paquetes_finalizados[$row['id_paquete']] = [
                    'nombre' => $row['nombre'],
                    'fecha_inicio' => $row['fecha_inicio'],
                    'fecha_fin' => $row['fecha_fin'],
                    'precio' => $row['precio'],
                    'descripcion' => $row['descripcion'],
                    'activado' => $row['activado'],
                    'cantidadClases' => $row['cantidadClases']
                ];
            }else{
                $paquetes_activos[$row['id_paquete']] = [
                    'nombre' => $row['nombre'],
                    'fecha_inicio' => $row['fecha_inicio'],
                    'fecha_fin' => $row['fecha_fin'],
                    'precio' => $row['precio'],
                    'descripcion' => $row['descripcion'],
                    'activado' => $row['activado'],
                    'cantidadClases' => $row['cantidadClases']
                ];
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

                    <!-- Formulario para buscar o filtrar paquetes -->
                    <form class="form-inline" id="buscaEdita" action="gestionPaquetes.php" method="post" style="margin-bottom: 10px">
                        <?php
                            // Si es alumno, mostramos un checkbox para filtrar solo sus paquetes suscritos
                            if ($_SESSION['roluser']==3) {

                                // Comprueba si ya estaba marcado para volver a marcarlo por defecto
                                if (isset($_POST['soloMios'])){
                                    $marcado = 'checked';
                                }else{
                                    $marcado = '';
                                }

                                echo('<div class="form-group mx-sm-3 mb-2">
                                    <input type="checkbox" class="" name="soloMios" id="soloMios" value="soloMios" '.$marcado.'>
                                    <label for="soloMios">Mostrar solo paquetes en los que estoy inscrito</label>
                                </div>');
                            }
                            
                        ?>
                        
                        <br>
                        <div class="form-group mx-sm-3 mb-2">
                            <input type="text" minlength="3" class="form-control" name="buscaPaquete" id="buscaPaquete">
                        </div>
                        <button type="submit" class="btn btn-warning mb-2">Buscar</button>

                        <?php
                        if ($_SESSION['roluser']==3) {
                            echo("
                            <h5>Puede realizar una búsqueda por nombre de paquete y filtrar solo por paquetes en las que esté inscrito</h5>
                            <h5>Recuerde presionar el boton de buscar para que el filtro funcione, tambien puede volver a pulsar en blanco sin ninguna busqueda para resetear</h5>
                            ");
                        }else{
                            echo("
                            <h5>Puede realizar una búsqueda por nombre de paquete</h5>
                            ");
                        }
                            
                        ?>
                        
                        
                        
                    </form>
                    

                    
                    <?php
                    // Si el usuario es Admin, añadimos un boton para crear paquetes
                        if ($_SESSION['roluser']==1){
                            echo('
                                <form id="creapaquete" action="creapaquete.php" method="post">
                                    <input type="hidden" name="registroRol" value="1">
                                    <button type="submit" class="btn btn-primary">Crear paquete</button>
                                </form>
                            ');
                        }
                    ?>
                    
                </div>

                <h3>Lista de paquetes en curso:</h3>     
                    
                    <table class="tabla_clases">
                    <tr>
                        <th>Ver</th>
                        <?php if ($_SESSION['iduser']==1) { // Si es admin, añadiremos tambien un boton para modificar el paquete
                            echo("<th>Modifica</th>");
                        } ?>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Fecha Inicio</th>
                        <th>Fecha Fin</th>
                        <th>Precio</th>
                        <th>Clases incluidas</th>
                        <th>Descripcion</th>
                    </tr>
                    <?php
                        // Comprueba si hay paquetes
                        if(count($paquetes_activos)>0){
                            foreach ($paquetes_activos as $key => $value) { // Recorremos la lista
                                $activado = '';

                                // Comprobamos si está activado o no para reducir la opacidad
                                if ($_SESSION['roluser']==3 && $value['activado']==0) {
                                    continue;
                                }elseif($_SESSION['roluser']==1 && $value['activado']==0) {
                                    $activado = 'style="opacity: 0.6"';
                                }

                                // Imprimimos todos los datos
                                echo("
                                    <tr $activado>
                                        <td><a href='"."verpaquete.php?idpaquete=$key'><i class='fa fa-eye' aria-hidden='true'></i></a></td>");
                                if ($_SESSION['iduser']==1) {
                                    echo("<td><a href='"."modificaPaquete.php?idpaquete=$key'><i class='fa fa-pencil-square-o' aria-hidden='true'></i></a></td>");
                                }
                                    echo("
                                        <td>$key</td>
                                        <td>" . $value['nombre'] . "</td>
                                        <td>" . $value['fecha_inicio'] . "</td>
                                        <td>" . $value['fecha_fin'] . "</td>
                                        <td>" . "<b> $</b>" . $value['precio'] . "</td>
                                        <td>" . $value['cantidadClases'] . "</td>
                                        <td>" . $value['descripcion'] . "</td>
                                    </tr>
                                ");
                            }
                        }else{
                            echo('No hay paquetes activos ahora mismo');
                        }
                                    
                                


                    ?>
                    </table>
                    <h5>Mostrando máximo <b>30</b> paquetes. Recomendado utilizar la búsqueda.</h5>
                    
                    <h3 style="margin-top: 50px;">Lista de paquetes finalizados:</h3>     
                    
                    <table class="tabla_clases">
                    <tr>
                        <th>Ver</th>
                        
                        <?php if ($_SESSION['iduser']==1) {

                            // Si el usuario es admin añadiremos boton para modificar el paquete
                            echo("<th>Modifica</th>");
                        } ?>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Fecha Inicio</th>
                        <th>Fecha Fin</th>
                        <th>Precio</th>
                        <th>Clases incluidas</th>
                        <th>Descripcion</th>
                    </tr>
                    <?php

                        // Comprobamos que hay paquetes
                        if(count($paquetes_finalizados)>0){
                            foreach ($paquetes_finalizados as $key => $value) { // Recorremos a lista de paquetes
                                $activado = '';

                                // Comprobamos si esta activado para reducir la opacidad
                                if ($_SESSION['roluser']==3 && $value['activado']==0) {
                                    continue;
                                }elseif($_SESSION['roluser']==1 && $value['activado']==0) {
                                    $activado = 'style="opacity: 0.6"';
                                }

                                // Imprimimos los datos
                                echo("
                                    <tr $activado>
                                        <td><a href='"."verpaquete.php?idpaquete=$key'><i class='fa fa-eye' aria-hidden='true'></i></a></td>");
                                        if ($_SESSION['iduser']==1) {
                                            echo("<td><a href='"."modificaPaquete.php?idpaquete=$key'><i class='fa fa-pencil-square-o' aria-hidden='true'></i></a></td>");
                                        }
                                    echo("
                                        <td>$key</td>
                                        <td>" . $value['nombre'] . "</td>
                                        <td>" . $value['fecha_inicio'] . "</td>
                                        <td>" . $value['fecha_fin'] . "</td>
                                        <td>" . "<b> $</b>" . $value['precio'] . "</td>
                                        <td>" . $value['cantidadClases'] . "</td>
                                        <td>" . $value['descripcion'] . "</td>
                                    </tr>
                                ");
                            }
                        }else{
                            echo('No hay paquetes finalizados');
                        }
                                    
                                


                    ?>
                    </table>
                    <h5>Mostrando máximo <b>30</b> paquetes. Recomendado utilizar la búsqueda.</h5>

                    <style>
                        .cuadrosombra{
                            border-radius: 15px;
                            background-color: #f5f4f2;
                            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15),
                                0 6px 6px rgba(0, 0, 0, 0.1);
                            transition: transform 0.3s ease, box-shadow 0.3s ease;
                            margin: 20px;
                        }

                        #estadoClases th{
                            border: 0px;
                            padding: 5px;
                        }

                        /* Estilo general para la tabla */
                        .tabla_clases {
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

                        /* Estilo para celdas de acción */
                        .tabla_clases td:first-child {
                            text-align: center; /* Centrar las acciones */
                            font-weight: bold; /* Resaltar */
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

                        .Activo{
                            background-color: lightskyblue;
                            font-weight: bold;
                        }

                        .Finalizado{
                            background-color: lightcoral;
                            font-weight: bold;
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

