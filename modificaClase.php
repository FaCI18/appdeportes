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

    // Comprueba que exista el elemento idclase con la id a modificar 
    if (!isset($_GET['idclase'])) {
        header("Location: index.php");
    }

    // Incluir la conexión a la base de datos
    include 'conexion.php';


    # Extraer lista horas mediante una consulta
    $sql_horas = "SELECT * FROM horario";
    $result_horas = $conn->query($sql_horas); // Ejecuta consulta

    // Variable para la lista de horas
    $horas = [];
    if ($result_horas->num_rows > 0) { // Comprueba que hay resultados
        while($row = $result_horas->fetch_assoc()) { // Recorre los resultados y guarda las horas en una lista
            $horas[$row['id_horario']]=$row['hora'];
        }
    }
    

    # Extraer lista profesores
    $sql_profesores = "SELECT * FROM usuarios WHERE rol=2"; // variable con la consulta
    $result_profesores = $conn->query($sql_profesores); // Ejecuta la consulta

    // Variable con la lista de profesores
    $profesores = [];
    if ($result_profesores->num_rows > 0) { // Comprueba que haya resultaods
        while($row = $result_profesores->fetch_assoc()) { // Recorremos los resultados
            $profesores[$row['id_usuario']]=[
            "nombre" => $row['nombre']." ".$row['apellido_materno']." ".$row['apellido_paterno'],
            "estado" => $row['activado']
            ];
    
        }
    }
    

    // Obtenemos los datos de la clase que vamos a modificar
    $sql = "SELECT * FROM clases WHERE id_clase = ".$_GET['idclase']; // Consulta
    $result = $conn->query($sql); // Ejecutar consulta

    if ($result->num_rows > 0) { // Comprobamos que hay resultados
        while($row = $result->fetch_assoc()) { // Recorremos los resultados y los añadimos en variables
            $nombreClase = $row['nombre'];
            $descripcionClase = $row['descripcion'];
            $fechaClase = $row['fecha_clase'];
            $idHoraClase = $row['id_horario'];
            $cuposClase = $row['cupos'];
            $precioClase = $row['precio'];
            $estadoClase = $row['activado'];
        }
    }


    // Obtenemos mediante consulta la cantidad de inscripciones para limitar la cantidad de cupos de la clase
    // Asi evitamos que al modificarla establezcamos menos cupos que la cantidad de alumnos inscritos
    $sql = "SELECT COUNT(id_solicitud) AS total FROM solicitudes WHERE id_clase = ".$_GET['idclase']; // Consulta
    $result = $conn->query($sql); // Ejecutar consulta

    // Recorremos los resultados y guardamos el total
    $min = 1;
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $min = $row['total'];
    }


    // Mediante consulta buscamos los profesores que esten asignados a esta clase
    $profesAsignados = [];
    $sql = "SELECT * FROM clases_profesores WHERE id_clase=".$_GET['idclase']; // COnsulta

    $result = $conn->query($sql); // Ejecutamos consulta
    if ($result->num_rows > 0) { // Comprobamos que hay resultaods
        while($row = $result->fetch_assoc()) { // Recorremos los resultados
            array_push($profesAsignados, $row['id_profesor']); // Array push añade un elemento a un array simple (lista)
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
                        <h1 style="margin-top: 5px">Modificando clase</h1>

                        <!-- Formulario con los datos de la clase para modificar todo excepto precio -->
                        <form id="creaClase" action="validamodclase.php" method="POST">
                        <div>
                            <label for="activado">Estado: </label>
                            <select name="activado" class="form-control" style="width: 200px">
                            <?php

                                // Añadimos al desplegable las opciones activado y desactivado (Ponemos por defecto la opcion actual de la clase mediante selected='selected')
                                if ($estadoClase==1) {
                                    echo("
                                        <option selected='selected' value='1'>Activa</option>
                                        <option value='0'>Desactivado</option>
                                    ");
                                }else{
                                    echo("
                                        <option value='1'>Activado</option>
                                        <option selected='selected' value='0'>Desactivada</option>
                                    ");
                                }
                             ?>
                             
                            </select>
                             
                             </div>

                            <input type="hidden" name="idclase" value="<?php echo($_GET['idclase']) ?>">

                            <div class="form-group">
                                <label for="nombre">Nombre</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo($nombreClase) ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="descripcion">Descripcion</label>
                                <br>
                                <textarea name="descripcion" id="descripcion"  form="creaClase" rows="5" required><?php echo($descripcionClase) ?></textarea>
                            </div>
                            <div class="form-group">
                                <label for="fecha">Fecha de clase</label>
                                <input type="date" class="form-control" id="fecha" value="<?php echo($fechaClase) ?>" name="fecha" required>
                            </div>
                            <div class="form-group">
                                <label for="hora">Hora de clase</label>
                                <select name="hora" class="form-control" required>
                                <?php

                                    // Recorremos las horas y las añadimos  a un desplegable
                                    // Comprobamos sus id para encontrar cual es la que tiene establecida la clase actualmente, y mediante un selected="selected" seleccionamos por defecto la hora actual de la clase
                                    foreach ($horas as $key => $value) {
                                        if ($key == $idHoraClase) {
                                            echo('<option selected="selected" value="'.$key.'">'.$value.'</option>
                                        ');
                                        }else{
                                            echo('<option value="'.$key.'">'.$value.'</option>
                                            ');
                                        }
                                        
                                    }
                                ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="cupos">Cupos</label>
                                <input type="number" class="form-control" id="cupos" value="<?php echo($cuposClase) ?>" name="cupos" min="<?php echo($min) ?>" max="100" required>
                            </div>
                            <div class="form-group">
                                <label for="precio">Precio ( $ )</label>
                                <input type="number" class="form-control" value="<?php echo($precioClase) ?>" id="precio" name="precio" min="1" disabled>
                            </div>
                            <div class="form-group">
                                <label for="profesores">Profesores asignados (Puede seleccionar varios con <b>CTRL</b>)</label>
                                <select multiple class="form-control" name="profesores[]" id="profesores" size="6" form="creaClase" required>
                                <?php

                                    // Añadimos los profesores a una lista seleccionable multiple
                                    // Esto permite utilizar la tecla CTRL para seleccionar varias opciones

                                    foreach ($profesores as $key => $value) { // Recorremos la lista de profesores
                                        
                                        if (in_array($key, $profesAsignados)) { // Comprueba si el profesor esta actualmente asignado a la clase y lo selecciona por defecto en la lista si es asi
                                            $seleccionado = 'selected="selected"';
                                        }else{
                                            $seleccionado = '';
                                        }

                                        if ($value['estado']==1) { // Comprueba si el profesor esta activado o desactivado, si esta desactivado le quita un 50% de opacidad para que el admin pueda detectarlo facilmente
                                            echo("<option $seleccionado value='".$key."'>".$value['nombre']."</option>");
                                        }else{
                                            echo("<option $seleccionado style='opacity: 0.5' value='".$key."'>".$value['nombre']."</option>");
                                        }
                                        
                                    }
                                ?>
                                </select>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Actualizar</button>
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
    // Obtén la fecha de mañana
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(today.getDate() + 1);

    // Convierte la fecha a formato YYYY-MM-DD
    const formattedDate = tomorrow.toISOString().split('T')[0];

    // Establece el atributo min en el elemento de fecha
    document.getElementById('fecha').setAttribute('min', formattedDate);
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

