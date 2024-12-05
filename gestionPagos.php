<?php
include 'sesion.php';

// COmprobamos que este la sesion iniciada
if (!isset($_SESSION['iduser'])) {
    header("Location: index.php");
}

// Comprobamos que el usuario sea admin
if ($_SESSION['roluser']!=1) {
    header("Location: index.php");
}

// Incluir la conexión a la base de datos
include 'conexion.php';

// Guardamos la id del usuario en una variable para trabajar mas facilmente
$idusuario = $_SESSION['iduser'];

// Comprueba que el usuario haya utilizado o no el formulario de buscar por nombre
// Dependiendo de si lo ha utilizado o no, añáde a la consulta SQL un LIKE%% para buscar con el termino que introdujo el usuario
if (isset($_POST['buscaClase'])){
    $busqueda = $_POST['buscaClase'];
    $sql = "SELECT 
        solicitudes.id_solicitud,
        solicitudes.estado_pago,
        solicitudes.adjunto,
        clases.id_clase,
        clases.nombre AS nombre_clase,
        clases.fecha_clase,
        clases.precio AS precio_clase,
        usuarios.id_usuario,
        usuarios.nombre AS nombre_usuario,
        usuarios.apellido_paterno AS apellido_paterno_usuario,
        usuarios.apellido_materno AS apellido_materno_usuario
    FROM 
        solicitudes
    LEFT JOIN 
        clases ON solicitudes.id_clase = clases.id_clase
    LEFT JOIN 
        usuarios ON solicitudes.id_usuario = usuarios.id_usuario
    WHERE 
        (clases.nombre LIKE '%$busqueda%' OR
         usuarios.nombre LIKE '%$busqueda%' OR
         usuarios.apellido_paterno LIKE '%$busqueda%' OR
         usuarios.apellido_materno LIKE '%$busqueda%')
    LIMIT 30;";


$sqlPaquetes = "SELECT 
    sp.id_solicitud_paquete,
    p.nombre AS nombre_paquete,
    p.precio AS precio_paquete,
    p.id_paquete AS id_paquete,
    p.activado,
    p.fecha_inicio,
    p.fecha_fin,
    u.nombre AS nombre_usuario,
    u.apellido_paterno AS apellido_paterno_usuario,
    u.apellido_materno AS apellido_materno_usuario
    FROM 
    solicitudes_paquetes sp
    INNER JOIN 
    paquetes p ON sp.id_paquete = p.id_paquete
    INNER JOIN 
    usuarios u ON sp.id_usuario = u.id_usuario
    WHERE 
    (p.nombre LIKE '%$busqueda%' OR
         u.nombre LIKE '%$busqueda%' OR
         u.apellido_paterno LIKE '%$busqueda%' OR
         u.apellido_materno LIKE '%$busqueda%')
    LIMIT 30;";






}else{
    $sql = "SELECT 
        solicitudes.id_solicitud,
        solicitudes.estado_pago,
        solicitudes.adjunto,
        clases.id_clase,
        clases.nombre AS nombre_clase,
        clases.fecha_clase,
        clases.precio AS precio_clase,
        usuarios.id_usuario,
        usuarios.nombre AS nombre_usuario,
        usuarios.apellido_paterno AS apellido_paterno_usuario,
        usuarios.apellido_materno AS apellido_materno_usuario
    FROM 
        solicitudes
    LEFT JOIN 
        clases ON solicitudes.id_clase = clases.id_clase
    LEFT JOIN 
        usuarios ON solicitudes.id_usuario = usuarios.id_usuario
    LIMIT 30;";

    
$sqlPaquetes = "SELECT 
    sp.id_solicitud_paquete,
    p.nombre AS nombre_paquete,
    p.precio AS precio_paquete,
    p.id_paquete AS id_paquete,
    p.activado,
    p.fecha_inicio,
    p.fecha_fin,
    u.nombre AS nombre_usuario,
    u.apellido_paterno AS apellido_paterno_usuario,
    u.apellido_materno AS apellido_materno_usuario
    FROM 
    solicitudes_paquetes sp
    INNER JOIN 
    paquetes p ON sp.id_paquete = p.id_paquete
    INNER JOIN 
    usuarios u ON sp.id_usuario = u.id_usuario
    LIMIT 30;";

}

$result = $conn->query($sql); // Ejecutamos la consulta


// Variables para las listas de pagos
$pagos_pendientes = [];
$pagos_antiguos = [];

    if ($result->num_rows > 0) { // Comprueba que haya resultados

        // Variable de fecha con la fecha de hoy
        $hoy = new DateTime();

        // Hay resultados
        while($row = $result->fetch_assoc()) {

            // Comprobamos las fechas
            $fechaclase = new DateTime($row['fecha_clase']);


            // Comprueba que haya archivo adjunto en la solicitud
            // Si no hay archivo adjunto, no se ha pagado por lo que el admin no necesita hacer nada y no se muestra
            if (is_null($row['adjunto'])) {
            }else{

                // Si el estado pago es 0 significa que no se ha validado
                // Si es 0, se comprueba la fecha, si es anterior a hoy entra en pagos antiguos
                // SI es 0 y la fecha aun es posterior a hoy, entra en pagos pendientes
                if ($row['estado_pago']==0) {
                    
                    if ($fechaclase>$hoy) {
                        $pagos_pendientes[$row['id_solicitud']] = [
                            "nombre_usuario" => $row['nombre_usuario'] . " " . $row['apellido_paterno_usuario']. " " . $row['apellido_materno_usuario'],
                            "estado_pago" => $row['estado_pago'],
                            "nombre" => $row['nombre_clase'],
                            "precio" => $row['precio_clase'],
                            'adjunto' => $row['adjunto'],
                            'fecha' => $row['fecha_clase']
                        ];
                    }else{
                        $pagos_antiguos[$row['id_solicitud']] = [
                            "nombre_usuario" => $row['nombre_usuario'] . " " . $row['apellido_paterno_usuario']. " " . $row['apellido_materno_usuario'],
                            "estado_pago" => $row['estado_pago'],
                            "nombre" => $row['nombre_clase'],
                            "precio" => $row['precio_clase'],
                            'adjunto' => $row['adjunto'],
                            'fecha' => $row['fecha_clase']
                        ];
                    }  
                }else{
                    $pagos_antiguos[$row['id_solicitud']] = [
                        "nombre_usuario" => $row['nombre_usuario'] . " " . $row['apellido_paterno_usuario']. " " . $row['apellido_materno_usuario'],
                        "estado_pago" => $row['estado_pago'],
                        "nombre" => $row['nombre_clase'],
                        "precio" => $row['precio_clase'],
                        'adjunto' => $row['adjunto'],
                        'fecha' => $row['fecha_clase']
                    ];
                }
                
            }
        }
    }

    $resultPaquetes = $conn->query($sqlPaquetes); // Ejecutamos la consulta

    // Variables para guardar los datos en listas
    $paquetes = [];

    if ($resultPaquetes->num_rows > 0) { // Comprueba si hay resultados
        // Hay resultados
        while($row = $resultPaquetes->fetch_assoc()) {
            $paquetes[$row['id_solicitud_paquete']] =  [
                "nombre" => $row['nombre_paquete'],
                "precio" => $row['precio_paquete'],
                "activado" => $row['activado'],
                "fecha_fin" => $row['fecha_fin'],
                "fecha_inicio" => $row['fecha_inicio'],
                "nombre_usuario" => $row['nombre_usuario'] . " " . $row['apellido_paterno_usuario'] . " " . $row['apellido_materno_usuario']
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

                    <form class="form-inline" id="buscaEdita" action="gestionPagos.php" method="post" style="margin-bottom: 10px">
                        <div class="form-group mx-sm-3 mb-2">
                            <input type="text" minlength="3" class="form-control" name="buscaClase" id="buscaClase">
                        </div>
                        <button type="submit" class="btn btn-warning mb-2">Buscar</button>
                        <h5>Puede realizar una búsqueda por nombre de clase o paquete, o por nombre o apellidos de alumnos</h5>
                        <h5>Presione de nuevo Buscar sin una busqueda para resetear</h5>
                    </form>
                    
                </div>

                <h3>Pagos pendientes de aprobación:</h3>     

                    <!-- Tabla con los pagos pendientes de aprobaccion -->

                    <table class="tabla_clases">
                    <tr>
                        <th>Nombre clase</th>
                        <th>Alumno</th>
                        <th>Fecha Límite</th>
                        <th>Precio</th>
                        <th>Justificante de pago</th>
                        <th>Validar</th>
                    </tr>
                    <?php
                        // Comprobamos que haya pagos pendientes en la lista
                        if(count($pagos_pendientes)>0){

                            foreach ($pagos_pendientes as $key => $value) { // Recorremos la lista
                                
                            $recibo="
                                    <td class='subearchivo'>
                                            <form action='verpdf.php' method='POST' enctype='multipart/form-data' class='upload-form'>
                                                <input type='hidden' name='archivo' value='".$value['adjunto']."'>
                                                <button type='submit' class='icon-button' >
                                                    <i class='fa fa-file-pdf-o' aria-hidden='true'></i>
                                                </button>
                                            </form>
                                        </td>
                                ";
                                
                                
                                // Añade los demas datos incluyendo un formulario con un solo boton para validad los pagos que lleva al archivo validarPago.php
                                echo("
                                    <tr>
                                        <td>" . $value['nombre'] . "</td>
                                        <td>" . $value['nombre_usuario'] . "</td>
                                        <td>" . $value['fecha'] . "</td>
                                        <td><b>$</b>" . $value['precio'] . "</td>
                                        $recibo
                                        <td style='color: green;'>
                                            <form action='validarPago.php' method='POST' enctype='multipart/form-data' class='upload-form'>
                                                <input type='hidden' name='idsolicitud' value='$key'>
                                                <button type='submit' class='icon-button aprobar' >
                                                     <i class='fa fa-check' aria-hidden='true' style='font-size: 30px'></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                ");
                            }
                        }else{
                            echo('No hay pagos pendientes de aprobar ahora mismo');
                        }
                    ?>
                    </table>
                    <h5>Mostrando máximo <b>30</b> pagos. Recomendado utilizar la búsqueda.</h5>
                    <!-- Tabla con los pagos validados o caducados -->
                    <h3>Pagos validados o caducados:</h3>     
                    <table class="tabla_clases">
                    <tr>
                        <th>Nombre clase</th>
                        <th>Alumno</th>
                        <th>Fecha</th>
                        <th>Precio</th>
                        <th>Estado</th>
                        <th>Justificante pago</th>
                    </tr>
                    <?php
                        // Comprobamos que haya pagos antiguos en la lista
                        if(count($pagos_antiguos)>0){
                            foreach ($pagos_antiguos as $key => $value) { // recorremos toda la lista
                                if ($value['estado_pago']==1) { // Si estado pago es 1 significa que fue pagado y validado
                                    $estado = "Pago validado";
                                    $color = 'green'; // Color para el texto del estado
                                }else{
                                    $estado = "Caducado sin validar";
                                    $color = 'red'; // Color para el texto del estado
                                }

                                // Añade los demas datos incluyendo un formulario con un solo boton para ver el comprobante de pago que lleva al archivo verpdf.php
                                echo("
                                    <tr>
                                        <td>" . $value['nombre'] . "</td>
                                        <td>" . $value['nombre_usuario'] . "</td>
                                        <td>" . $value['fecha'] . "</td>
                                        <td><b>$</b>" . $value['precio'] . "</td>
                                        <td><b style='color: $color'>$estado</b></td>
                                        <td class='subearchivo'>
                                        <form action='verpdf.php' method='POST' enctype='multipart/form-data' class='upload-form'>
                                            <input type='hidden' name='archivo' value='".$value['adjunto']."'>
                                            <button type='submit' class='icon-button'>
                                                <i class='fa fa-file-pdf-o' aria-hidden='true'></i>
                                            </button>
                                        </form>
                                        </td>
                                    </tr>
                                ");
                            }
                        }else{
                            echo('No hay resultados.');
                        }
                    ?>
                    </table>
                    <h5>Mostrando máximo <b>30</b> pagos. Recomendado utilizar la búsqueda.</h5>

                    <h3>Historial de inscripciones a Paquetes</h3>   
                    <table class="tabla_clases">
                    <tr>
                        <th>Nombre paquete</th>
                        <th>Alumno</th>
                        <th>Fecha Inicio</th>
                        <th>Fecha Fin</th>
                        <th>Precio</th>
                    </tr>
                    <?php
                        // Comprobamos que haya pagos antiguos en la lista
                        if(count($paquetes)>0){
                            foreach ($paquetes as $key => $value) { // recorremos toda la lista

                                // Añade los demas datos incluyendo un formulario con un solo boton para ver el comprobante de pago que lleva al archivo verpdf.php
                                echo("
                                    <tr>
                                        <td>" . $value['nombre'] . "</td>
                                        <td>" . $value['nombre_usuario'] . "</td>
                                        <td>" . $value['fecha_inicio'] . "</td>
                                        <td>" . $value['fecha_fin'] . "</td>
                                        <td><b>$</b>" . $value['precio'] . "</td>
                                    </tr>
                                ");
                            }
                        }else{
                            echo('No hay resultados.');
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
                            text-align: left; /* Centrar las acciones */
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
                            color: red; /* Cambia el color si lo deseas */
                        }

                        .aprobar i {
                            color: green;
                        }

                        .icon-button:hover i {
                            color: darkred; /* Cambia el color al pasar el ratón (opcional) */
                            scale: 1.5;
                        }

                        .aprobar:hover i {
                            color: darkgreen;
                            
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

