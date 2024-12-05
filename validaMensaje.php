<?php
include 'sesion.php';

// Verificar que el usuario haya iniciado sesión y tenga rol de profesor (rol 2)
if (!isset($_SESSION['iduser'])) {
    header("Location: login.php");
    exit();
} elseif ($_SESSION['roluser'] != 2) {
    header("Location: index.php");
    exit();
}

// Incluir la conexión a la base de datos
include 'conexion.php';

// Guardar variables del usuario y del POST
$emisor = $_SESSION['iduser'];
$receptor = $_POST['receptor'];
$idcp = $_POST['idcp']; // ID de clase o paquete
$asunto = $_POST['asunto'];
$mensaje = $_POST['mensaje'];

// Inicializar valores para id_clase y id_paquete según el tipo
$id_clase = $idcp;


$id_clase = $idcp;
$url = 'verclase.php?idclase='.$id_clase;


// Verificar si hay archivo adjunto
$archivo_adjunto = '';
if (isset($_FILES['archivo'])) {
    $archivo_adjunto = $_FILES['archivo'];
}



// Insertar la notificación en la base de datos
$sql = "INSERT INTO notificaciones (
            id_emisor, id_receptor, id_clase, asunto, mensaje
        ) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiiss", $emisor, $receptor, $id_clase, $asunto, $mensaje);

if ($stmt->execute()) { // Ejecutar la consulta
    $id_insercion = $stmt->insert_id;

    // // Comprueba si hay archivo adjunto y no hay error
    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {

        // Mover el archivo a la carpeta files/notificaciones y renombrarlo con notif_ id del problema de salud .pdf
        $ruta_carpeta = 'files/notificaciones/';
        $nombre_archivo = "notif_" . $id_insercion . ".pdf";
        $ruta_archivo = $ruta_carpeta . $nombre_archivo;

        if (!is_dir($ruta_carpeta)) {
            mkdir($ruta_carpeta, 0777, true); // Crear la carpeta si no existe
        }

        // Movemos el archivo a la carpeta del servidor
        if (move_uploaded_file($archivo_adjunto['tmp_name'], $ruta_archivo)) {

            // Actualizar la notificación con la URL del archivo
            $sql_update = "UPDATE notificaciones SET adjunto = ? WHERE id_notificacion = ?";
            $stmt = $conn->prepare($sql_update);
            $stmt->bind_param("si", $ruta_archivo, $id_insercion);

            if ($stmt->execute()) { // Ejecuta la consulta
                echo "<script>
                        alert('Notificación enviada con archivo adjunto con éxito.');
                        window.location.href = '$url';
                      </script>";
            } else {
                echo "<script>
                        alert('Error al enviar la notificación: " . $conn->error . "');
                        window.location.href = '$url';
                      </script>";
            }
        } else {
            echo "<script>
                    alert('Error al subir el archivo.');
                    window.location.href = 'mispagos.php';
                  </script>";
            exit();
        }
    } else {
        // No hay archivo adjunto, mensaje enviado sin problema
        echo "<script>
                alert('Notificación enviada con éxito.');
                window.location.href = '$url';
              </script>";
    }
} else {
    echo "<script>
        alert('Error al enviar la notificación.');
        window.location.href = '$url';
    </script>";
}


$stmt->close();
$conn->close();
?>
