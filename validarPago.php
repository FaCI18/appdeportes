<?php
    include 'sesion.php';

    // Verificar que el usuario haya iniciado sesión y tenga rol de administrador (rol 1)
    if (!isset($_SESSION['iduser'])) {
        header("Location: login.php");
        exit();
    } elseif ($_SESSION['roluser'] != 1) {
        header("Location: index.php");
        exit();
    }


    // Comprueba que del POST le llegue la id de la solicitud
    if (!isset($_POST['idsolicitud'])) {
        header("Location: index.php");
        exit();
    }

      // Incluir la conexión a la base de datos
      include 'conexion.php';

    // Verificar si id_solicitud ha sido enviado por POST
    if (isset($_POST['idsolicitud'])) {
        $idsolicitud = $_POST['idsolicitud'];

        // Asegurar para evitar bugs o hackeos
        $idsolicitud = $conn->real_escape_string($idsolicitud);

        // Consultar para actualizar el estado de la solicitud
        $sql = "UPDATE solicitudes SET estado_pago = 1 WHERE id_solicitud = $idsolicitud";

        // Ejecutar la consulta
        if ($conn->query($sql) === TRUE) {
            echo "<script> alert('Validado con exito'); window.location.href = 'gestionPagos.php'; </script>";
        } else {
            echo "<script> alert('Error al validar'); window.location.href = 'gestionPagos.php'; </script>";
        }
    }

    // Cerrar la conexión
    $conn->close();
?>
