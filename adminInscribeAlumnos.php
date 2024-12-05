<?php
include 'sesion.php';

    # Comprueba que exista sesion iniciada
    if (isset($_SESSION['iduser'])) {

        # Comprueba que sea admin
        if ($_SESSION['roluser'] != 1){
            header("Location: index.php");
        }
    }else{
        header("Location: index.php");
    }

    // Incluir la conexión a la base de datos
    include 'conexion.php';

    // Comprobar que el id del usuario al que vamos a inscribir a alguna clase nos llegue en el POST
    if (!isset($_POST['iduserinscripcion'])){
        header("Location: index.php");
    }

    // Añadimos el POST a variables para facilitar el trabajo
    $iduserinscripcion = $_POST['iduserinscripcion'];
    $nombrealumno = $_POST['nombreAlumno'];
    $id_paquete = $_POST['id_paquete'];
    $id_solicitud_paquete = $_POST['id_solicitud_paquete'];

    // Consulta para obtener todas las clases del alumno a las que NO este inscrito
    // Además filtra que la clase tenga una fecha mínimo de mañana para asegurar que se inscribe con al menos 1 dia de antelacion
    // También filtra por clases activas
    // Añadimos a la consulta la cantidad total de inscripciones que tiene para evitar sobrepasar los cupos

$sql = "
        SELECT 
            clases.id_clase,
            clases.nombre,
            clases.descripcion,
            clases.fecha_clase,
            horario.hora,  -- Incluir la hora de la clase
            clases.cupos,
            clases.precio,
            (
                SELECT COUNT(*)
                FROM solicitudes
                WHERE solicitudes.id_clase = clases.id_clase
            ) AS inscripciones
        FROM 
            clases
        JOIN 
            horario ON clases.id_horario = horario.id_horario  -- Relación con la tabla horario
        WHERE 
            clases.id_clase NOT IN (
                SELECT 
                    solicitudes.id_clase
                FROM 
                    solicitudes
                WHERE 
                    solicitudes.id_usuario = $iduserinscripcion
                    AND solicitudes.id_clase IS NOT NULL
            )
            AND clases.fecha_clase > CURDATE()  -- Filtra clases de hoy o fechas posteriores
            AND clases.activado = 1;  -- Solo clases activadas
    ";

    $result = $conn->query($sql); // Ejecutar consulta

    $lista_clases = [];

    if ($result->num_rows > 0) { // Comprueba resultados y los guarda en variables
        while($row = $result->fetch_assoc()) {

            $lista_clases[$row['id_clase']] =[
                "nombre" => $row['nombre'],
                "cuposTotales" => $row['cupos'],
                "inscripciones" => $row['inscripciones'],
                "fecha_clase" => $row['fecha_clase'],
                "hora" => $row['hora']
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
<?php
    include 'menu.php';    
?>
    
    <!-- =============== About =============== -->
    <section id="about" class="">
		<!-- =============== container =============== -->
    <!-- style="margin-left: 0px; margin-right: 0px" -->
        <div class="container" >            
            <div class="row justify-content-center" style="padding: 0px">
                <div class="col-md-6">
                    <div style="margin-top: 25px; margin-bottom: 25px" class="cuadrosombra">
                    <h3 style="margin-top: 5px">Inscribir <?php echo($nombrealumno); ?> a una clase</h3>
                        <form id="creaPaquete" action="inscripcion.php" method="POST">


                            <!-- Añadimos de forma oculta al POST el id del alumno, el tipo de inscripcion y a donde debe regresar el programa cuando lo inscriba -->
                            <!-- Añadimos tambien los cupos de la clase para evitar realizar mas consultas  -->
                            <input type='hidden' name='iduserinscripcion' value='<?php echo($iduserinscripcion); ?>'>
                            <input type='hidden' name='tipo' value='clase'>
                            <input type='hidden' name='regresar' value='verpaquete.php?idpaquete=<?php echo($id_paquete); ?>'>
                            <input type='hidden' name='id_solicitud_paquete' value='<?php echo($id_solicitud_paquete); ?>'>

                            <!-- Formulario para inscribir alumnos en una clase -->
                            <!-- Solo mostraremos los clases que NO estén ya llenas y esten activas -->
                             <!-- No se mostrarán las clases en las que el alumno ya esté inscrito -->
                            <div class="form-group">
                                <label for="idclase">Seleccione clase:</label>
                                <select name="idclase" class="form-control" style="width: 400px" required>
                                    <?php
                                        // Añadimos al desplegable las clases
                                        foreach ($lista_clases as $key => $value) {

                                            // Comprueba que haya cupos antes de listar
                                            if ($value['cuposTotales']>$value['inscripciones']) {
                                                $nombreClase = $value['nombre'];
                                                $idClase = $key;
                                                $fecha_clase = $value['fecha_clase'];
                                                $hora = $value['hora'];

                                                echo("
                                                    <option value='$idClase'>$hora - $fecha_clase - $nombreClase</option>
                                                ");
                                            }
                                        }
                                    ?>
                            </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Inscribir</button>
                        </form>

                    </div>
                </div>
            </div>
        </div>
        
        <style>
            .cuadrosombra{
                            border-radius: 15px;
                            background-color: #f5f4f2;
                            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15),
                                0 6px 6px rgba(0, 0, 0, 0.1);
                            transition: transform 0.3s ease, box-shadow 0.3s ease;
                            margin: 20px;
                            padding: 20px;
                            padding-top: 2px;
                        }
            #descripcion{
                width: 100%;
                box-sizing: border-box;
                resize: none;
            }
        </style>
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
    // Obtener referencias a los campos de fecha
    const fechaInicio = document.getElementById('fecha_inicio');
    const fechaFin = document.getElementById('fecha_fin');

    // Configurar la fecha mínima inicial
    const today = new Date();
    const tomorrow = new Date(today);
    const afterTomorrow = new Date(today);

    tomorrow.setDate(today.getDate() + 1);
    afterTomorrow.setDate(today.getDate() + 2);

    const formattedTomorrow = tomorrow.toISOString().split('T')[0];
    const formattedAfterTomorrow = afterTomorrow.toISOString().split('T')[0];

    fechaInicio.setAttribute('min', formattedTomorrow);
    fechaFin.setAttribute('min', formattedAfterTomorrow);

    // Actualizar dinámicamente la fecha mínima de 'fecha_fin' cuando cambia 'fecha_inicio'
    fechaInicio.addEventListener('change', function () {
        const selectedDate = new Date(fechaInicio.value);
        const nextDay = new Date(selectedDate);
        nextDay.setDate(selectedDate.getDate() + 1);
        const formattedNextDay = nextDay.toISOString().split('T')[0];
        
        fechaFin.setAttribute('min', formattedNextDay);

        // Si la fecha de fin es menor a la nueva fecha mínima, ajustar su valor
        if (fechaFin.value < formattedNextDay) {
            fechaFin.value = formattedNextDay;
        }
    });
</script>


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

