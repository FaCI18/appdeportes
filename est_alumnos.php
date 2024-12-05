<?php
include 'sesion.php';

// Comprueba que la sesion este iniciada
if (isset($_SESSION['iduser'])) {

    # Comprueba que sea Admin
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

// Variables para extraer la edad media de los alumnos
$edadMedia=0;
$contMedia=0;

// La siguiente consulta utiliza AVG para extraer una media, y CAST para convertir el STRING de la base de datos a NUMERICO y lo mostrará en la consulta con el nombre de edadMedia
// Además utilizamos el WHERE para decirle que solo tenga en cuenta los campos de la tabla datos con el id 1 que es edad
$sql = "SELECT AVG(CAST(info AS UNSIGNED)) AS edadMedia FROM datos WHERE id_dato_estudiante = 1";
$result = $conn->query($sql); // Ejecutamos la consulta

// Si hubo resultados extraemos la edad media
// Si no hubo resultados significa que ningún alumno tiene guardada su edad
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $edadMedia = $row['edadMedia'];
} else {
    $edadMedia = 0; // Manejo de caso cuando no hay datos
}

// Si la edadMedia es null lo cambiamos a 0 para poder mostrarlo en las estadisticas sin error
if ($edadMedia==null) {
    $edadMedia = 0;
}

// Media problemas de salud por alumno
$media_psalud = 0;

// En este caso, solamente extraemos la cantidad total de problemas de salud (COUNT), y lo dividimos entre la cantidad de alumnos que existe
$sql = "SELECT COUNT(id_psalud) AS total FROM psalud";
$result = $conn->query($sql);

// Comprobamos que haya resultados y que la cantidad de alumnos sea mayor que 0
if ($result->num_rows > 0 && $contAlumnos>0) {
    $row = $result->fetch_assoc();
    $media_psalud=$row['total']/$contAlumnos;
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
        <div class="col-md-12" style="margin-top: 200px; text-align: center">  
            <div style="max-width: 600px; margin: 0 auto; text-align: left; margin-bottom: 50px" class="cuadrosombra">
                <center style="text-align: center">
                    <h2>Estadísticas por alumnos</h2>
                </center>
                
                <p>Total de alumnos registrados: <?php echo($contAlumnos) ?></p>
                <p>Media de edad del alumno : <?php echo($edadMedia) ?></p>
                <p>Media de problemas de salud por alumno : <?php echo($media_psalud) ?></p>
                <hr>

                <!-- MOSTRAMOS EL CANVA (LIENZO) DE LA ESTADISTICA 
                 EL CANVA SE CREA EN UN SCRIPT ABAJO -->


                <div class="chart-container" style="width: 50%; margin: auto; padding: 20px;">
                    <canvas id="polarAreaChart"></canvas>
                </div>
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

    // Pasamos las variables de PhP a Javascript
    const edadMedia = <?= json_encode($edadMedia) ?>;
    const mediaPSalud = <?= json_encode($media_psalud) ?>;

    // Configuración del gráfico de Polar Area
    const ctx = document.getElementById('polarAreaChart').getContext('2d');
    const polarAreaChart = new Chart(ctx, {
        type: 'polarArea',
        data: {
            labels: ['Edad Media', 'Problemas de Salud (Media)'],
            datasets: [{
                label: 'Estadísticas Generales',
                data: [edadMedia, mediaPSalud],
                backgroundColor: [
                    'rgba(255, 99, 132, 0.5)',
                    'rgba(54, 162, 235, 0.5)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${context.label}: ${context.raw.toFixed(2)}`;
                        }
                    }
                }
            }
        }
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

