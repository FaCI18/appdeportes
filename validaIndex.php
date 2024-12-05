<?php
session_start();

// Verificar que el usuario haya iniciado sesión y tenga rol de administrador (rol 1)
if (!isset($_SESSION['iduser'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['roluser'] != 1) {
    header("Location: index.php");
    exit();
}

  // Incluir la conexión a la base de datos
  include 'conexion.php';

// Comprobar si se ha enviado el POSt de una noticia, o de una modificacion de las imagenes
if (isset($_POST['noticia'])) {

    // Guardamos la noticia en una variable
    $noticia = $_POST['noticia'];
    $sqlinsert = "INSERT INTO noticias (fecha, noticia) VALUES (CURDATE(), '$noticia')"; // Consulta para añadir una noticia
    $resultinsert = $conn->query($sqlinsert); // Ejecutamos la consulta

    echo "<script>
            alert('Noticia agregada con éxito');
            window.location.href = 'modIndex.php';
          </script>";
}

// Comprobar si el POST es de una modificacion de imagenes
if (isset($_POST['imagenes'])) {

    // Lista de nombres de archivos de imagen
    $imagenes = ['imagen1', 'imagen2', 'imagen3', 'imagen4'];
    
    // Carpeta destino donde se moverán las imágenes
    $carpetaDestino = 'files/imgIndex/';
    
    // Iteramos sobre las imágenes que podrían ser subidas
    foreach ($imagenes as $index => $imagen) {

        // Verificar si el archivo ha sido enviado
        // Esto básicamente es para comprobar si ha sido la 1, la 2, la 3 o la 4
        if (isset($_FILES[$imagen]) && $_FILES[$imagen]['error'] == 0) {

            // Obtener la información del archivo
            $archivoTmp = $_FILES[$imagen]['tmp_name'];
            $nombreArchivo = $_FILES[$imagen]['name'];
            $tipoArchivo = $_FILES[$imagen]['type'];


            // Generar un nuevo nombre para la imagen (modImg1, modImg2, ...)
            $nuevoNombre = 'modImg' . ($index + 1) . '.' . pathinfo($nombreArchivo, PATHINFO_EXTENSION);
            $rutaDestino = $carpetaDestino . $nuevoNombre;

            // Mover la imagen al directorio de destino
            if (move_uploaded_file($archivoTmp, $rutaDestino)) {
                // Actualizar la URL de la imagen correspondiente en la base de datos
                $idImagen = $index + 1; // 1, 2, 3, 4
                $sqlUpdate = "UPDATE imgIndice SET url_imagen = '$rutaDestino' WHERE id_imagen = $idImagen";
                $conn->query($sqlUpdate);
            } else {
                echo "<script>
                        alert('Error al mover el archivo $imagen.');
                        window.location.href = 'modIndex.php';
                      </script>";
                exit();
            }
        }
    }

    echo "<script>
            alert('Imágenes modificadas con éxito');
            window.location.href = 'modIndex.php';
          </script>";
}

// Cerrar la conexión
$conn->close();
?>
