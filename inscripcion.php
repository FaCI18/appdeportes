<?php
    include 'sesion.php';

    // Verificar que el usuario haya iniciado sesión y tenga rol de alumno (rol 3) o rol de admin (rol1)
    if (!isset($_SESSION['iduser'])) {
        header("Location: login.php");
        exit();
    } elseif ($_SESSION['roluser'] ==2) {
        header("Location: index.php");
        exit();
    }

    // Comprueba que llegue el usuario de la inscripcion
    if (!isset($_POST['iduserinscripcion'])) {
        header("Location: login.php");
        exit();
    }

    // Incluir la conexión a la base de datos
    include 'conexion.php';


    // Comprueba que desde el formulario le haya llegado la variable de POST llamada tipo
    // Para saber si es clase o paquete a lo que estamos inscribiendo
    if (!isset($_POST['tipo'])) {
        header("Location: index.php");
        exit();
    }

    if ($_POST['tipo']=='clase'){
        // 1 Comprobar que no esté ya inscrito este alumno
        // 2 Comprobar que haya cupos
        // 3 Se inscribe
        // 4 Devolvemos a verclase

        // 1 Comprobamos que no este inscrito ya a esta clase buscando una solicitud para esa clase de este mismo usuario
        $sql = "SELECT * from solicitudes WHERE id_clase=".$_POST['idclase']." AND id_usuario=".$_POST['iduserinscripcion'];
        $result = $conn->query($sql);

        if ($result->num_rows > 0) { // Si hay resultados significa que esta inscrito
            echo "<script> alert('Ya esta inscrito en esta clase'); window.location.href = '".$_POST['regresar']."'; </script>";
        }else{
            // Si no hay resultados, no esta inscrito

            // Comprobamos que haya cupos contando las solicitudes a esta clase mediante una consulta con COUNT
            $sql2 = "SELECT COUNT(id_solicitud) AS total from solicitudes WHERE id_clase=".$_POST['idclase'];
            $result2 = $conn->query($sql2); // Ejecutamos la consulta


            // Si hay resultados guardamos el total, si no, lo dejamos a 0
            if ($result2->num_rows > 0) {
                $row = $result2->fetch_assoc();
                $inscritos = $row['total'];
            } else {
                $inscritos = 0;
            }

            // Obtenemos los cupos de la clase
            $sql3 = "SELECT cupos from clases WHERE id_clase=".$_POST['idclase'];
            $result3 = $conn->query($sql3); // Ejecutamos la consulta


            // Recogemos el numero de cupos
            if ($result3->num_rows > 0) {
                $row = $result3->fetch_assoc();
                $cupos = $row['cupos'];
            } else {
                $cupos = 0;
            }

            // Comprobamos que haya menos inscritos que cupos
            if ($inscritos<$cupos) {

                // Si hay cupos, creamos una consulta con un INSERT a la base de datos
                // Comprobamos si existe en el POST el id_solicitud_paquete, lo cual significa que el alumno esta canjeando una clase de un paquete
                if (isset($_POST['id_solicitud_paquete'])) {
                    $sqlinsert = "INSERT INTO solicitudes (id_usuario, id_clase, id_solicitud_paquete, estado_pago) VALUES (".$_POST['iduserinscripcion'].", ".$_POST['idclase'].",".$_POST['id_solicitud_paquete'].", 1)";
                }else{
                    $sqlinsert = "INSERT INTO solicitudes (id_usuario, id_clase, estado_pago) VALUES (".$_POST['iduserinscripcion'].", ".$_POST['idclase'].", 'FALSE')";
                }

                $resultinsert = $conn->query($sqlinsert); // Ejecutamos la consulta

                echo "<script> alert('Inscrito con éxito a la clase'); window.location.href = '".$_POST['regresar']."'; </script>";
            }else{
                echo "<script> alert('Lo sentimos no quedan cupos disponibles'); window.location.href = '".$_POST['regresar']."'; </script>";
            }
        }

    }else{
        // Caso que sea un paquete

        // 1 Comprobar que no esté ya inscrito este alumno
        // 3 Se inscribe
        // 4 Devolvemos a verpaquete


        // Al igual que con las clases, comprobamos que no exista ya una solicitud de este alumno a esta clase
        $sql = "SELECT * from solicitudes_paquetes WHERE id_paquete=".$_POST['idpaquete']." AND id_usuario=".$_POST['iduserinscripcion'];
        $result = $conn->query($sql); // Ejecutamos la consulta

        // Si hay resultados, esta inscrito
        if ($result->num_rows > 0) {
            echo "<script> alert('Ya esta inscrito en este paquete'); window.location.href = '".$_POST['regresar']."'; </script>";
        }else{

            // Si no hay resultados creamos una consulta con un INSERT a la tabla solicitudes

            $sqlinsert = "INSERT INTO solicitudes_paquetes (id_usuario, id_paquete) VALUES (".$_POST['iduserinscripcion'].", ".$_POST['idpaquete'].")"; // Consulta con el INSERT
            $resultinsert = $conn->query($sqlinsert); // Ejecutamos la consulta

            echo "<script> alert('Inscrito con éxito al paquete'); window.location.href = '".$_POST['regresar']."'; </script>";
        }
    }

    // Cerrar la conexión
    $conn->close();
?>