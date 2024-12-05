<?php
    include 'sesion.php';

    // Verificar que el usuario haya iniciado sesión y tenga rol de alumno (rol 3)
    if (!isset($_SESSION['iduser'])) {
        header("Location: login.php");
        exit();
    } elseif ($_SESSION['roluser'] != 3) {
        header("Location: index.php");
        exit();
    }

   // Incluir la conexión a la base de datos
    include 'conexion.php';

    // Verificar los datos del POST y que esten todos
    if (!isset($_POST['id_solicitud']) || !isset($_POST['tipo_solicitud']) || !isset($_FILES['archivo'])) {
        header("Location: mispagos.php");
        exit();
    }

    // Guardar variables del usuario y del POST
    $id_usuario = $_SESSION['iduser'];
    $id_solicitud = $_POST['id_solicitud'];
    $tipo_solicitud = $_POST['tipo_solicitud'];
    $archivo = $_FILES['archivo'];


    // Mover el archivo a la carpeta files/pagos y renombrarlo
    $ruta_carpeta = 'files/pagos/';
    $nombre_archivo = "pago_" . $id_solicitud . ".pdf";
    $ruta_archivo = $ruta_carpeta . $nombre_archivo;

    if (!is_dir($ruta_carpeta)) {
        mkdir($ruta_carpeta, 0777, true); // Crear la carpeta si no existe
    }

    // Comprueba que no haya un error al mover el archivo
    if (!move_uploaded_file($archivo['tmp_name'], $ruta_archivo)) {
        echo "<script>
                alert('Error al subir el archivo.');
                window.location.href = 'mispagos.php';
              </script>";
        exit();
    }

    // Actualizar la solicitud con la URL del archivo
    // Consulta en una variable
    $sql_update = "UPDATE solicitudes SET adjunto = ? WHERE id_solicitud = ? AND id_usuario = ?"; 
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("sii", $ruta_archivo, $id_solicitud, $id_usuario);

    if ($stmt->execute()) { // Ejecutamos la consulta y mostramos la alerta de exito o error
        echo "<script>
                alert('Archivo subido con éxito.');
                window.location.href = 'mispagos.php';
              </script>";
    } else {
        echo "<script>
                alert('Error al actualizar la solicitud: " . $conn->error . "');
                window.location.href = 'mispagos.php';
              </script>";
    }

    // Cerrar la conexión
    $stmt->close();
    $conn->close();
?>
