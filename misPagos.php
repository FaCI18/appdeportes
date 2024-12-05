<?php
include 'sesion.php';

// Comprueba que la sesion este inicada
if (!isset($_SESSION['iduser'])) {
    header("Location: index.php");
}

// Comprueba que el usuario sea alumno
if ($_SESSION['roluser']!=3) {
    header("Location: index.php");
}

// Incluir la conexión a la base de datos
include 'conexion.php';

// Guardamos la id del usuario para facilitar el trabajo
$idusuario = $_SESSION['iduser'];

// Al igual que siempre se comprueba si utiliza el formulario de busqueda para filtrar por nombre de clase y modificar la consulta con un LIKE%%
if (isset($_POST['buscaClase'])){
    $busqueda = $_POST['buscaClase'];
    $sql = "SELECT 
        solicitudes.id_solicitud,
        solicitudes.estado_pago,
        solicitudes.adjunto,
        solicitudes.id_solicitud_paquete,
        clases.fecha_clase,
        clases.nombre,
        clases.precio,
        paquetes.fecha_fin AS fecha_fin_paquete,
        paquetes.nombre AS nombre_paquete,
        paquetes.precio AS precio_paquete
    FROM 
        solicitudes
    LEFT JOIN 
        clases ON solicitudes.id_clase = clases.id_clase
    LEFT JOIN 
        solicitudes_paquetes ON solicitudes.id_solicitud_paquete = solicitudes_paquetes.id_solicitud_paquete
    LEFT JOIN 
        paquetes ON solicitudes_paquetes.id_paquete = paquetes.id_paquete
    WHERE 
        solicitudes.id_usuario = $idusuario
        AND (
            (solicitudes.id_clase IS NOT NULL AND clases.nombre LIKE '%$busqueda%') OR
            (solicitudes.id_solicitud_paquete IS NOT NULL AND paquetes.nombre LIKE '%$busqueda%')
        )
    LIMIT 30;
";

$sqlPaquetes = "SELECT 
        sp.id_solicitud_paquete,
        p.nombre AS nombre_paquete,
        p.precio AS precio_paquete,
        p.id_paquete AS id_paquete,
        p.activado,
        p.fecha_inicio,
        p.fecha_fin
    FROM 
        solicitudes_paquetes sp
    INNER JOIN 
        paquetes p ON sp.id_paquete = p.id_paquete
    WHERE 
        sp.id_usuario = $idusuario
        AND paquetes.nombre LIKE '%$busqueda%'
    LIMIT 30;";





}else{
    $sql = "SELECT 
        solicitudes.id_solicitud,
        solicitudes.estado_pago,
        solicitudes.adjunto,
        solicitudes.id_solicitud_paquete,
        clases.fecha_clase,
        clases.nombre,
        clases.precio,
        paquetes.fecha_fin AS fecha_fin_paquete,
        paquetes.nombre AS nombre_paquete,
        paquetes.precio AS precio_paquete
    FROM 
        solicitudes
    LEFT JOIN 
        clases ON solicitudes.id_clase = clases.id_clase
    LEFT JOIN 
        solicitudes_paquetes ON solicitudes.id_solicitud_paquete = solicitudes_paquetes.id_solicitud_paquete
    LEFT JOIN 
        paquetes ON solicitudes_paquetes.id_paquete = paquetes.id_paquete
    WHERE 
        solicitudes.id_usuario = $idusuario
    LIMIT 30;
";

$sqlPaquetes = "SELECT 
        sp.id_solicitud_paquete,
        p.nombre AS nombre_paquete,
        p.precio AS precio_paquete,
        p.id_paquete AS id_paquete,
        p.activado,
        p.fecha_inicio,
        p.fecha_fin
    FROM 
        solicitudes_paquetes sp
    INNER JOIN 
        paquetes p ON sp.id_paquete = p.id_paquete
    WHERE 
        sp.id_usuario = $idusuario
    LIMIT 30;";


}

$result = $conn->query($sql); // Ejecutamos la consulta

// Variables para guardar los datos en listas
$pagos_pendientes = [];
$pagos_realizados = [];
$pagos_caducados = [];

    if ($result->num_rows > 0) { // Comprueba si hay resultados

        // Creamos una variable fecha con la fecha de hoy
        $hoy = new DateTime();

        // Hay resultados
        while($row = $result->fetch_assoc()) { // Recorremos los resultados

            // Comprobamos las fechas de las clase
            $fechaclase = new DateTime($row['fecha_clase']);

            // Comprobamos si el estado pago es 1
            // Si es 1 comprobamos si tiene adjunto, si lo tiene es una clase pagada y validada, si no tiene adjunto, es una clase canjeada mediante paquete
            // Si no es 1, se comprueba si tiene adjunto, si lo tiene esta pagada y falta validacion por parte de Admin, si no tiene adjunto es un pago pendiente
            if ($row['estado_pago']==1) { // Pagada
                
                if (is_null($row['adjunto'])) {
                    $pagos_realizados[$row['id_solicitud']] = [
                        "estado_pago" => $row['estado_pago'],
                        "nombre" => $row['nombre'],
                        "precio" => $row['precio'],
                        'adjunto' => $row['adjunto'],
                        'fecha_clase' => $row['fecha_clase'],
                        'nombre_paquete' => $row['nombre_paquete']
                    ];
                }else{
                    $pagos_realizados[$row['id_solicitud']] = [
                        "estado_pago" => $row['estado_pago'],
                        "nombre" => $row['nombre'],
                        "precio" => $row['precio'],
                        'adjunto' => $row['adjunto'],
                        'fecha_clase' => $row['fecha_clase'],
                        'tipo' => ''
                    ];
                }
            }else{
                // Si la fecha de la clase es anterior al dia de hoy entonces el pago ha caducado, no lo mostramos
                if ($fechaclase>=$hoy) {

                    // Comprobamos si tiene adjunto
                    if (is_null($row['adjunto'])){
                        // Lo agregamos a la lista de pagos pendientes
                        $pagos_pendientes[$row['id_solicitud']] =  [
                            "estado_pago" => $row['estado_pago'],
                            "nombre" => $row['nombre'],
                            "precio" => $row['precio'],
                            'adjunto' => $row['adjunto'],
                            'fecha_clase' => $row['fecha_clase'],
                            'tipo' => ''
                        ];
                    }else{
                        $pagos_realizados[$row['id_solicitud']] = [
                            "estado_pago" => $row['estado_pago'],
                            "nombre" => $row['nombre'],
                            "precio" => $row['precio'],
                            'adjunto' => $row['adjunto'],
                            'fecha_clase' => $row['fecha_clase'],
                            'tipo' => ''
                        ];
                    }


                    
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
        $paquetes[$row['id_paquete']] =  [
            "nombre" => $row['nombre_paquete'],
            "precio" => $row['precio_paquete'],
            "activado" => $row['activado'],
            "fecha_fin" => $row['fecha_fin'],
            "fecha_inicio" => $row['fecha_inicio']
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

                    <form class="form-inline" id="buscaEdita" action="misPagos.php" method="post" style="margin-bottom: 10px">
                        <div class="form-group mx-sm-3 mb-2">
                            <input type="text" minlength="3" class="form-control" name="buscaClase" id="buscaClase">
                        </div>
                        <button type="submit" class="btn btn-warning mb-2">Buscar</button>
                    </form>

                    
                    <?php
                        // Si el usuario es admin se agrega un formulario con un boton de crear clase para ir a un formulario para su creacion
                        if ($_SESSION['roluser']==1){
                            echo('
                                <form id="creaClase" action="creaclase.php" method="post">
                                    <input type="hidden" name="registroRol" value="1">
                                    <button type="submit" class="btn btn-primary">Crear clase</button>
                                </form>
                            ');
                        }
                    ?>
                    
                </div>

                <h3>Pagos de clases pendientes:</h3>     
                    <table class="tabla_clases">
                    <tr>
                        <th>Nombre clase</th>
                        <th>Fecha Límite</th>
                        <th>Precio</th>
                        <th>Adjuntar pago</th>
                    </tr>
                    <?php
                        // Comprueba que la lista de pagos pendientes no este vacia
                        if(count($pagos_pendientes)>0){
                            foreach ($pagos_pendientes as $key => $value) { // Recorremos la lista de pagos

                                // Agregamos los datos así como un mini formulario para subir un comprobante de pago de la clase
                                // Comprueba si es un paquete para sustituir el formulario de pago con un mensaje para los paquetes (Esto va a desaparecer)
                                echo("
                                    <tr>
                                        <td>" . $value['nombre'] . "</td>
                                        <td>" . $value['fecha_clase'] . "</td>
                                        <td><b>$</b>" . $value['precio'] . "</td>");
                                        echo("
                                        <td class='subearchivo'>
                                            <form action='subepago.php' method='POST' enctype='multipart/form-data' class='upload-form'>
                                                <input type='hidden' name='id_solicitud' value='$key'>
                                                <input type='hidden' name='tipo_solicitud' value='".$value['tipo']."'>
                                                <input type='file' name='archivo' accept='application/pdf' class='file-input' required>
                                                <button type='submit' class='upload-btn' id='icon-$key'>
                                                    <i class='fa fa-upload' aria-hidden='true'></i>
                                                </button>
                                            </form>
                                        </td>");
                                        
                                       

                                echo("
                                    </tr>
                                ");
                            }
                        }else{
                            echo('No hay pagos pendientes ahora mismo');
                        }
                    ?>
                    </table>
                    <h5>Mostrando máximo <b>30</b> pagos pendientes. Recomendado utilizar la búsqueda.</h5>
                    
                    <h3>Pagos de clases realizados:</h3>     
                    <table class="tabla_clases">
                    <tr>
                        <th>Nombre clase</th>
                        <th>Fecha</th>
                        <th>Precio</th>
                        <th>Estado</th>
                        <th>Justificante pago</th>
                    </tr>
                    <?php

                        // Comprobamos que haya elementos en la lista de pagos realizados
                        if(count($pagos_realizados)>0){
                            foreach ($pagos_realizados as $key => $value) { // Recorremos la lista de pagos realizados

                                // Si el estado es 0 significa que no esta validado
                                if ($value['estado_pago']==0) {
                                    $estado = "Pendiente de validacion";
                                }else{
                                    $estado = "Aceptado";
                                }

                                // Imprimimos los datos en una fila de tabla, junto a un pequeño formulario que lleva al archivo verpdf.php donde podemos ver el comprobante de pago
                                echo("
                                    <tr>
                                        <td>" . $value['nombre'] . "</td>
                                        <td>" . $value['fecha_clase'] . "</td>");

                                        // SI el pago viene de un paquete se tacha el precio
                                        if (is_null($value['adjunto'])) {
                                            echo("<td><del><b>$</b>" . $value['precio'] . "</del></td>");
                                        }else{
                                            echo("<td><b>$</b>" . $value['precio'] . "</td>");
                                        }
                                        
                                        echo("<td>$estado</td>");

                                        if (!is_null($value['adjunto'])) {
                                            echo("
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
                                        }else{
                                            echo("
                                                <td class='subearchivo' style='text-align: left'>
                                                    Pago realizado mediante canje de paquete: ".$value['nombre_paquete']."
                                                </td>
                                            </tr>
                                            ");
                                        }
                                        
                            }
                        }else{
                            echo('No hay pagos realizados.');
                        }
                    ?>
                    </table>
                    <h5>Mostrando máximo <b>30</b> pagos realizados. Recomendado utilizar la búsqueda.</h5>

                    <h3>Paquetes contratados:</h3>     
                    <table class="tabla_clases">
                    <tr>
                        <th>Nombre paquete</th>
                        <th>Fecha Inicio</th>
                        <th>Fecha Finalizacion</th>
                        <th>Precio</th>
                    </tr>
                    <?php

                        // Comprobamos que haya elementos en la lista de paquetes contratados
                        if(count($paquetes)>0){
                            foreach ($paquetes as $key => $value) { // Recorremos la lista de pagos realizados
                                // Imprimimos los datos en una fila de tabla
                                echo("
                                    <tr>
                                        <td>" . $value['nombre'] . "</td>
                                        <td>" . $value['fecha_inicio'] . "</td>
                                        <td>" . $value['fecha_fin'] . "</td>
                                        <td>" . $value['precio'] . "</td></tr>");
                             }
                        }else{
                            echo('No hay pagos realizados.');
                        }
                    ?>
                    </table>
                    <h5>Mostrando máximo <b>30</b> pagos realizados. Recomendado utilizar la búsqueda.</h5>
                

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

                        .icon-button:hover i {
                            color: darkred; /* Cambia el color al pasar el ratón (opcional) */
                            scale: 1.5;
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

