<?php
include 'sesion.php';

// Verificar que el usuario haya iniciado sesión y tenga rol de administrador (rol 1)
if (!isset($_SESSION['iduser'])) {
    header("Location: login.php");
    exit();
} elseif ($_SESSION['roluser'] != 3) {
    header("Location: index.php");
    exit();
}

  // Incluir la conexión a la base de datos
  include 'conexion.php';

// Verificar los datos del POST y que exista el nombre de la enfermedad o lesion
if (!isset($_POST['nombre_psalud'])) {
    echo "<script>
            alert('El formulario no contiene datos válidos.');
            window.location.href = 'perfil.php';
          </script>";
    exit();
}

// Guardar variables del usuario y del POST
$id_usuario = $_SESSION['iduser'];
$nombre_psalud = $conn->real_escape_string($_POST['nombre_psalud']);

// Insertar el registro en la tabla psalud (sin adjunto inicialmente)
$sql_insert = "INSERT INTO psalud (id_usuario, enfermedad) VALUES (?, ?)";

// Conexion especial para poder obtener el ID del registro que insertamos
$stmt = $conn->prepare($sql_insert);
$stmt->bind_param("is", $id_usuario, $nombre_psalud);

if ($stmt->execute()) { // Ejecutamos la consulta

    // Obtener el ID del INSERT
    $id_psalud = $stmt->insert_id;

    // Comprobamos que exista archivo adjunto y que no haya error en el
    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] == UPLOAD_ERR_OK) {

        // Guardamos en variables el nombre del archivo y la ruta
        $archivo = $_FILES['archivo'];
        $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);

        // Guardamos en variables la ruta nueva y su nombre nuevo que sera siempre psalud_ id del usuario .php
        $nuevo_nombre = "psalud_" . $id_psalud . "." . $extension;
        $ruta_carpeta = 'files/salud/';
        $ruta_archivo = $ruta_carpeta . $nuevo_nombre;

        // Crear la carpeta si no existe
        if (!is_dir($ruta_carpeta)) {
            mkdir($ruta_carpeta, 0777, true);
        }

        // Mover el archivo a la carpeta destino
        if (move_uploaded_file($archivo['tmp_name'], $ruta_archivo)) {

            // Actualizar el registro de la tabla con la ruta del archivo
            $sql_update = "UPDATE psalud SET adjunto = ? WHERE id_psalud = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("si", $ruta_archivo, $id_psalud);

            if ($stmt_update->execute()) { // Ejecutar consulta
                echo "<script>
                        alert('Problema de salud añadido correctamente con el archivo adjunto.');
                        window.location.href = 'perfil.php';
                      </script>";
            } else {
                echo "<script>
                        alert('Error al actualizar la ruta del archivo.');
                        window.location.href = 'perfil.php';
                      </script>";
            }
        } else {
            echo "<script>
                    alert('Error al mover el archivo adjunto.');
                    window.location.href = 'perfil.php';
                  </script>";
        }
    } else {
        // Si no hay archivo, solo inserta el registro
        echo "<script>
                alert('Problema de salud añadido correctamente.');
                window.location.href = 'perfil.php';
              </script>";
    }
} else {
    echo "<script>
            alert('Error al insertar el registro en la base de datos.');
            window.location.href = 'perfil.php';
          </script>";
}

// Cerrar la conexión
$stmt->close();
$conn->close();
?>
