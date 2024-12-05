<?php
    include 'sesion.php';

    // Comprueba que la sesion este iniciada
    if (!isset($_SESSION['iduser'])) {
        header("Location: login.php");
        exit();
    }

    // Comprueba que llegue el idpaquete desde el formulario o lista de paquetes
    if (!isset($_GET['idpaquete'])) {
        header("Location: index.php");
        exit();
    }

    $id_usuario = $_SESSION['iduser'];

    // Incluir la conexión a la base de datos
    include 'conexion.php';

     // Extraer datosl paquete
    $sql = "SELECT 
        c.id_paquete,
        c.nombre,
        c.descripcion,
        c.fecha_inicio,
        c.fecha_fin,
        c.precio,
        c.cantidadClases
    FROM 
        paquetes c
    WHERE 
        id_paquete = ".$_GET['idpaquete'];


    $result = $conn->query($sql); // Ejecutar la consulta

    
    if ($result->num_rows > 0) { // Comprobar los resultados y guardarlos en variables
        $row = $result->fetch_assoc();
        
        $id_paquete = $row['id_paquete'];
        $nombre = $row['nombre'];
        $descripcion = $row['descripcion'];
        $fecha_inicio = $row['fecha_inicio'];
        $fecha_fin = $row['fecha_fin'];
        $precio = $row['precio'];
        $cantidadClases = $row['cantidadClases'];

    }else{
        header("Location: gestionPaquetes.php");
        exit();
    }

    // Comprobar si el usuario que revisa el paquete esta inscrito ya
    $sql_estado = "SELECT id_solicitud_paquete FROM solicitudes_paquetes WHERE id_paquete=".$id_paquete." AND id_usuario=".$_SESSION['iduser']; // Consulta
    $result_estado = $conn->query($sql_estado); // Ejecutar consulta

    // Si hay resultados, esta inscrito
    if ($result_estado->num_rows > 0) {
        $row = $result_estado->fetch_assoc();

        $id_solicitud_paquete = $row['id_solicitud_paquete'];
        $estado = 'inscrito';
    } else {
        $estado = 'noinscrito';
    }

    // Lista de usuarios inscritos
    // Además la query hace una subquery donde cuenta las suscriones de cada usuario a clases utilziando este paquete
    $lista_usuarios = [];
    $sql_usuarios = "
    SELECT 
        solicitudes_paquetes.id_solicitud_paquete,
        usuarios.id_usuario,
        usuarios.nombre,
        usuarios.apellido_materno,
        usuarios.apellido_paterno,
        usuarios.activado,
        (
            SELECT COUNT(*)
            FROM solicitudes
            WHERE solicitudes.id_usuario = usuarios.id_usuario
              AND solicitudes.id_solicitud_paquete = solicitudes_paquetes.id_solicitud_paquete
        ) AS clases_suscritas
    FROM 
        solicitudes_paquetes
    JOIN 
        usuarios ON solicitudes_paquetes.id_usuario = usuarios.id_usuario
    WHERE 
        solicitudes_paquetes.id_paquete = $id_paquete;
";

    $result_usuarios = $conn->query($sql_usuarios);

    if ($result_usuarios->num_rows > 0) {
        while($row = $result_usuarios->fetch_assoc()) {
           $lista_usuarios[$row['id_usuario']] = [
            "nombre" => $row['nombre'],
            "apellido_materno" => $row['apellido_materno'],
            "apellido_paterno" => $row['apellido_paterno'],
            "activado" => $row['activado'],
            "totalclases" => $row['clases_suscritas'],
            "id_solicitud_paquete" => $row['id_solicitud_paquete']
           ];
        }
    }

    // Lista para almacenar usuarios NO inscritos y poder añadirlos
    // Solo recibe usuarios activos
    $lista_usuarios_no_inscritos = [];

    // Obtenemos la lista de alumnos NO inscritos en este paquete
    $sql = "
        SELECT u.id_usuario, u.nombre, u.apellido_paterno, u.apellido_materno, u.email, u.activado
        FROM usuarios u
        LEFT JOIN solicitudes_paquetes sp ON u.id_usuario = sp.id_usuario AND sp.id_paquete = $id_paquete
        WHERE sp.id_usuario IS NULL
        AND u.rol=3
        AND u.activado=1;
    ";

    $result = $conn->query($sql); // Ejecutar la consulta
    
    if ($result->num_rows > 0) { // Comprueba resultados y los guarda en variables
        while($row = $result->fetch_assoc()) {
           $lista_usuarios_no_inscritos[$row['id_usuario']] = [
            "nombre" => $row['nombre'] . " " .  $row['apellido_paterno'] . " " . $row['apellido_materno'],
            "estado" => $row['activado']
           ];
        }
    }

    $totalCanjeos=0;
    // Lista de clases a las que poder inscribirse SOLO si eres alumno

    if ($_SESSION['roluser']==3) {
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
                    solicitudes.id_usuario = ".$_SESSION['iduser']."
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

        if ($estado=='inscrito') {
            $sql = "
            SELECT COUNT(id_solicitud) as totalCanjeos
            FROM solicitudes
            WHERE id_usuario = ".$_SESSION['iduser']."
            AND id_solicitud_paquete=$id_solicitud_paquete
        ;";

        $result = $conn->query($sql); // Ejecutar consulta

        if ($result->num_rows > 0) { // Comprueba resultados y los guarda en variables
            while($row = $result->fetch_assoc()) {
                $totalCanjeos=$row['totalCanjeos'];
            }
        }
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
                        <h3><?php echo($nombre) ?></h3>

                        <!-- Mostramos al alumno el estado actual, si esta suscrito o no -->
                         <?php
                            // Comprueba que sea alumno (rol 3)
                            if($_SESSION['roluser']==3){
                                if($estado=='inscrito'){ // Comprueba si esta inscrito
                                    echo("
                                    <form id='inscribirse' action='#' method='post'>
                                        <input type='hidden' name='tipo' value='paquete'>
                                        <button type='submit' class='btn btn-success mb-2' disabled>Ya inscrito</button>
                                    </form>
                                ");
                                }else{ // Si no esta inscrito, comprobamos que la fecha de el paquete no haya terminado ni sea de hoy (debe inscribirse con 1 dia de antelacion minimo)

                                    // Variables de fecha
                                    $hoy = new DateTime();
                                    $fclase = new DateTime($fecha_fin);

                                    if ($fclase<=$hoy) { // Comprueba que la fecha sea anterior o igual a hoy
                                        echo("
                                            <form id='inscribirse' action='#' method='post'>
                                                <input type='hidden' name='tipo' value='paquete'>
                                                <input type='hidden' name='idpaquete' value='".$_GET['idpaquete']."'>
                                                <input type='hidden' name='cupos' value='".$cupos."'>
                                                <button type='submit' class='btn btn-danger mb-2' disabled>Paquete finalizado</button>
                                            </form>
                                        ");
                                    }else{ // Si no es anterior puede inscribirse
                                        echo("
                                        <form id='inscribirse' action='#' method='post'>
                                            <input type='hidden' name='tipo' value='paquete'>
                                            <input type='hidden' name='idpaquete' value='".$_GET['idpaquete']."'>
                                            <button type='submit' class='btn btn-primary mb-2' disabled>Inscribase en Caja</button>
                                        </form>
                                    ");
                                    }
                                    
                                }
                                
                            }
                         ?>

                        <!-- Mostramos en una tabla los detalles del Paquete -->
                        <table class="tabla_clases">
                            <tr>
                                <td><b>Fecha de inicio</b></td>
                                <td><?php echo($fecha_inicio) ?></td>
                                <td><b>Fecha de finalizacion</b></td>
                                <td><?php echo($fecha_fin) ?></td>
                            </tr>
                            <hr>
                            <tr>
                                <td ><b>Precio</b></td>
                                <td ><b>$</b><?php echo($precio) ?></td>
                                <td><b>Cantidad de clases incluidas</b></td>
                                <td><?php echo($cantidadClases) ?></td>
                            </tr>
                            <?php
                                if ($_SESSION['roluser']==3) {
                                    $restantes = $cantidadClases - $totalCanjeos;
                                    echo("
                                    <tr>
                                        <td ><b>Clases a canjear restantes:</b></td>
                                        <td >$restantes</td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    ");
                                } 
                            ?>
                            
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

                        <?php
                            // Si es alumno, añade una lista de clases para poder inscribirse desde el paquete
                            if ($_SESSION['roluser']==3 && $estado=='inscrito') {
                                

                                 // Variables de fecha
                                 $hoy = new DateTime();
                                 $fclase = new DateTime($fecha_fin);
                                
                                 // Creamos el formulario con un select y un boton para inscribir alumnos
                                 // Añadimos al POST de manera oculta tanto la id del usuario a inscribir como el lugar al que regresar (a esta misma pagina)
                                echo('<form id="inscribeClase" class="form-inline" action="inscripcion.php" method="POST">');
                                echo("<h3 style='margin-top: 50px'>Canjear Clase</h3>");

                                // Añadimos de forma oculta al POST el id del alumno, el tipo de inscripcion y a donde debe regresar el programa cuando lo inscriba 
                                // Añadimos tambien los cupos de la clase para evitar realizar mas consultas
                                echo("
                                    <input type='hidden' name='iduserinscripcion' value='$id_usuario'>
                                    <input type='hidden' name='tipo' value='clase'>
                                    <input type='hidden' name='regresar' value='verpaquete.php?idpaquete=$id_paquete'>
                                    <input type='hidden' name='id_solicitud_paquete' value='$id_solicitud_paquete'>
                                ");
                                
                                echo('<div class="form-group mx-sm-3 mb-2"><select name="idclase" class="form-control" style="margin: 5px" required>');
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
                                echo('</select></div>');
 
                                if ($fclase<=$hoy) { // Comprueba que la fecha de finalizacion sea anterior o igual a hoy
                                    echo('<button disabled type="submit" class="btn btn-danger">Fecha límite alcanzada</button>');
                                }else{ // Si no es anterior puede inscribise a clases canjeando el paquete
                                    if ($totalCanjeos<$cantidadClases) {
                                        echo('<button type="submit" class="btn btn-primary">Inscribirse</button>');
                                    }else{
                                        echo('<button disabled type="submit" class="btn btn-primary">Ya canjeo todas sus clases</button>');
                                    }
                                    
                                }

                                echo("</form>");
                            } 
                        ?>

                        <?php
                            // Comprueba si es Admin, y añade otro menú para Inscribir alumnos a este paquete
                            if ($_SESSION['roluser']==1) {
                                

                                 // Variables de fecha
                                 $hoy = new DateTime();
                                 $fclase = new DateTime($fecha_fin);
                                
                                 // Creamos el formulario con un select y un boton para inscribir alumnos
                                 // Añadimos al POST de manera oculta tanto la id del usuario a inscribir como el lugar al que regresar (a esta misma pagina)
                                echo('<form id="inscribeUsuario" class="form-inline" action="inscripcion.php" method="POST">');
                                echo("<h3 style='margin-top: 50px'>Inscribir alumnos</h3>");
                                echo("<input type='hidden' name='tipo' value='paquete'>");
                                echo("<input type='hidden' name='regresar' value='verpaquete.php?idpaquete=".$id_paquete."'>");
                                echo("<input type='hidden' name='idpaquete' value='$id_paquete'>");
                                echo('<div class="form-group mx-sm-3 mb-2"><select placeholder="Seleccione Usuario" name="iduserinscripcion" class="form-control" style="margin: 5px" required>');
                                // Añadimos al desplegable los alumnos no inscritos
                                foreach ($lista_usuarios_no_inscritos as $key => $value) {
                                    $idalumno = $key;
                                    $nombrealumno = $value['nombre'];
                                    echo("
                                        <option value='$idalumno'>$nombrealumno</option>
                                    ");
                                }
                                echo('</select></div>');
 
                                if ($fclase<=$hoy) { // Comprueba que la fecha de finalizacion sea anterior o igual a hoy
                                    echo('<button disabled type="submit" class="btn btn-danger">Fecha límite alcanzada</button>');
                                }else{ // Si no es anterior puede inscribir alumnos al paquete
                                    echo('<button type="submit" class="btn btn-primary">Inscribir</button>');
                                }

                                echo("</form>");
                            } 
                        ?>
                        <hr>
                        <?php
                            
                            // Si el usuario es Admin mostramos los alumnos inscritos en este Paquete
                            // También muestra la cantidad de clases que ha canjeado de este paquete
                            if ($_SESSION['roluser']==1){
                                echo("<h3 style='margin-top: 50px'>Lista de alumnos inscritos</h3>");

                                // Comprueba que haya alumnos inscritos y rellena la tabla
                                if(count($lista_usuarios)>0){
                                    echo("<table class='tabla_clases' style='width: 1200px'>");
                                    echo("<tr>
                                        <th>Ver perfil</th>
                                    ");
                                    echo("<th>Nombre</th>");
                                    echo("<th>Clases Canjeadas</th>");
                                    echo("<th>Inscribir a Clases</th>");
                                    echo("</tr>");

                                    // Recorre la lista de alumnos
                                    foreach ($lista_usuarios as $key => $value) {

                                        // Comprueba que el alumno este activo y si no, pone la opacidad al 60%
                                        if ($value['activado']==1) {
                                            $alumnoEstado = '';
                                        }else{
                                            $alumnoEstado = 'style="opacity: 0.6"';
                                        }


                                        // Enlace a la web para ver el perfil del alumno
                                        echo("
                                            <tr $alumnoEstado>
                                                <td ><a href='"."verperfil.php?usrid=$key'><i class='fa fa-eye' aria-hidden='true'></i></a></td>
                                                <td>".$value['nombre']." ".$value['apellido_paterno']." ".$value['apellido_materno']."</td>
                                        ");

                                        // Contador con las clases utilizadas de lpaquete
                                        echo("<td>".$value['totalclases']." / <b>".$cantidadClases."</b></td>");

                                        // Imprimimos el boton para inscribir al alumno a alguna clase

                                        // Si ya ha gastado todas las clases del paquete, desactivamos el boton

                                        if ($value['totalclases']<$cantidadClases) {
                                            $editar='';
                                        }else{
                                            $editar = 'disabled';
                                        }

                                        echo("
                                            <td class='subearchivo' style='text-align: left'>
                                                <form action='adminInscribeAlumnos.php' method='POST'>
                                                    <input type='hidden' name='nombreAlumno' value='".$value['nombre']." ".$value['apellido_paterno']." ".$value['apellido_materno']."'>
                                                    <input type='hidden' name='iduserinscripcion' value='".$key."'>
                                                    <input type='hidden' name='id_paquete' value='".$id_paquete."'>
                                                    <input type='hidden' name='id_solicitud_paquete' value='".$value['id_solicitud_paquete']."'>
                                                    <button type='submit' class='icon-button' $editar>
                                                        <i style='color:green;' class='fa fa-plus-circle' aria-hidden='true'></i>
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

