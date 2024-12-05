<?php
include 'sesion.php';

if (isset($_SESSION['iduser'])) {

    # Comprueba que sea profesor
    if ($_SESSION['roluser'] !=1){
        header("Location: index.php");
    }
}else{
    header("Location: index.php");
}

// Incluir la conexión a la base de datos
include 'conexion.php';

// Variables para almacenar la cantidad que hay registrada de cada tipo de usuario
$contAlumnos=0;
$contProfesores=0;
$contAdmins=0;
$contUsuarios=0;

// Consulta a la base de datos para extraer los datos de TODOS los usuarios
$sql = "SELECT * FROM usuarios";
$result = $conn->query($sql);

// Mediante IFS comparamos el ROL del usuario y lo sumamos a la variable correspondiente (1 para admins, 2 para profesores, 3 para alumnos)
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        if ($row['rol']==3) {
            $contAlumnos++;
        }elseif ($row['rol']==2) {
            $contProfesores++;
        }else{
            $contAdmins++;
        }
        $contUsuarios++;
    }
}


// Extraemos la cantidad de clases que hay creadas
$countClases=0;

$sql = "SELECT COUNT(id_clase) AS total FROM clases"; // Variable con la consulta
$result = $conn->query($sql); // Ejecutamos consulta


// Comprobamos el resultado y lo guardamos en una variable
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $countClases=$row['total'];
}

// Extraemos la cantidad de paquetes que hay creadas
$countPaquetes=0;

$sql = "SELECT COUNT(id_paquete) AS total FROM paquetes"; // Variable con la consulta
$result = $conn->query($sql); // Ejecutamos consulta

// Comprobamos el resultado y lo guardamos en una variable
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $countPaquetes=$row['total'];
}

// Calculamos el total de ganancias por clases y la media de ganancias por clase

// Variables para almacenar los datos
$ganado_clases = 0;
$media_ganado_clases = 0;

// Consulta para extraer las solicitudes pagadas de las clases, así como su precio
$sql = "
    SELECT 
        solicitudes.*, 
        clases.precio AS pr
    FROM 
        solicitudes 
    INNER JOIN 
        clases 
    ON 
        solicitudes.id_clase = clases.id_clase 
    WHERE 
        solicitudes.id_clase IS NOT NULL 
        AND solicitudes.estado_pago = 1
        AND solicitudes.id_solicitud_paquete IS NULL
";

$result = $conn->query($sql); // Ejecutamos consulta

// Comprobamos los resultados y sumamos las ganancias
// Luego dividimos las ganancios entre las clases para calcular la media 
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()){
        $ganado_clases=$ganado_clases+$row['pr'];
    }
    $media_ganado_clases = $ganado_clases/$countClases;
}


// Calculamos el total de ganancias por paquetes y la media de ganancias por paquete
// Variables para almacenar los datos
$ganado_paquetes = 0;
$media_ganado_paquetes = 0;

$sql = "
    SELECT 
        solicitudes_paquetes.*, 
        paquetes.precio AS pr
    FROM 
        solicitudes_paquetes
    INNER JOIN 
        paquetes
    ON 
        solicitudes_paquetes.id_paquete = paquetes.id_paquete
    WHERE 
        solicitudes_paquetes.id_paquete IS NOT NULL
";


$result = $conn->query($sql);// Ejecutamos consulta

// Comprobamos los resultados y sumamos las ganancias
// Luego dividimos las ganancios entre las paquetes para calcular la media 
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()){
        $ganado_paquetes=$ganado_paquetes+$row['pr'];
    }
    $media_ganado_paquetes = $ganado_paquetes/$countPaquetes;
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
      <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
    
    <section>
        <!-- Tipos de usuarios -->
        <div class="col-md-12" style="margin-top: 200px; text-align: left; margin-bottom: 50px">
            <div style="max-width: 600px; margin: 0 auto;" class="cuadrosombra">
                <center style="text-align: center">
                    <h2>Ingresos</h2>
                </center>

                <!-- Mostramos los datos que calculamos arriba, y añadimos un canva con las estadisticas
                 El canva se genera en un script abajo, con Javascript y JChart -->
                <p>Total ingresos en clases: $<?php echo $ganado_clases; ?></p>
                <p>Media de ingresos por clase: $<?php echo round($media_ganado_clases, 2); ?></p>
                <p>Total ingresos en paquetes: $<?php echo $ganado_paquetes; ?></p>
                <p>Media de ingresos por paquete: $<?php echo round($media_ganado_paquetes, 2); ?></p>
                <hr>
                <canvas id="barChart" width="400" height="400"></canvas>
            </div>
        </div>
    

    
    </section>

    <!-- Footer -->
    <footer id="footer" >
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
                        &copy; 2016 Be. Created By <a href="http://templatestock.co">Template Stock</a>
					</p>

				</div>
				<div data-wow-delay=".6s" class="col-xs-12 col-sm-6 col-md-6 wow bounceIn  animated" style="visibility: visible; animation-delay: 0.6s; animation-name: bounceIn;">

					  <section class="widget widget_text" id="text-15">
                         <h3 class="widget-title">California, United States</h3> <div class="textwidget">786, Firs Avenue, The Mall,<br>
                        <p>Tel: 01 234-56786<br>
                        Mobile: 01 234-56786<br>
                        E-mail: <a href="#">info@Be.com</a></p>
                        <a href="#">Get directions on the map</a> →</div>
                    </section>

				</div>
			</div>
    </div><!-- =============== container end =============== -->
	</footer>    

        <style>
            .cuadrosombra{
                border-radius: 15px;
                background-color: #e3e3e3;
                box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15),
                    0 6px 6px rgba(0, 0, 0, 0.1);
                transition: transform 0.3s ease, box-shadow 0.3s ease;
                margin: 20px;
                padding: 20px;
                padding-top: 2px;
            }
        </style>

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
        // SCRIPT ESTANDAR PARA LAS ESTADISTICAS
        // NO HAY MUCHO QUE EXPLICAR, SE COPIA DE LA PÁGINA DE JCHART Y SE LE DAN LAS VARIABLES DE LOS DATOS
        document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('barChart').getContext('2d');
    var barChart = new Chart(ctx, {
        type: 'bar',  // Tipo de gráfico: barras
        data: {
            labels: ['Total Ganado Clases', 'Media Ganada por Clase', 'Total Ganado Paquetes', 'Media Ganada por Paquete'],  // Etiquetas de las barras
            datasets: [{
                label: 'Ingresos',
                data: [
                    <?php echo $ganado_clases; ?>, 
                    <?php echo $media_ganado_clases; ?>, 
                    <?php echo $ganado_paquetes; ?>, 
                    <?php echo $media_ganado_paquetes; ?>
                ],  // Datos: las variables PHP con los valores correspondientes
                backgroundColor: '#36A2EB',  // Color de las barras
                borderColor: '#1D4F8C',      // Color del borde de las barras
                borderWidth: 1               // Ancho del borde
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: {
                    beginAtZero: true  // Asegurarse de que las barras comiencen en 0 en el eje X
                }
            }
        }
    });
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

