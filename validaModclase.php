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

    // Incluir la conexión a la base de datos
    include 'conexion.php';

    // Guardar variables
    $id_clase = $_POST['idclase'];
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $fecha =  $_POST['fecha'];
    $hora =  $_POST['hora'];
    $cupos =  $_POST['cupos'];
    $profesores =  $_POST['profesores'];
    $activado = $_POST['activado'];

    // Actualizar la clase en la base de datos (sin modificar el precio)
    $sql = "UPDATE clases SET 
                nombre = '$nombre', 
                descripcion = '$descripcion', 
                fecha_clase = '$fecha', 
                id_horario = '$hora', 
                cupos = '$cupos',
                activado = '$activado'
            WHERE id_clase = $id_clase";

    if ($conn->query($sql) === TRUE) { // Ejecutamos la consulta

        // Si la actualización fue exitosa, actualizar los profesores
        // Primero eliminar los profesores existentes para esta clase
        $sql = "DELETE FROM clases_profesores WHERE id_clase = $id_clase";
        $conn->query($sql);

        // Insertar nuevamente los profesores asociados
        foreach ($profesores as $key => $value) {
            $sql = "INSERT INTO clases_profesores (id_clase, id_profesor) 
                VALUES ($id_clase, $value)";
            $conn->query($sql);
        }

        echo "<script>
                alert('Clase actualizada con éxito!');
                window.location.href = 'gestionClases.php';
            </script>";

    } else {
        // Si ocurre un error, mostrar un mensaje de error
        echo "<script>
                alert('Error al actualizar la clase: " . $conn->error . "');
                window.location.href = 'gestionClases.php';
            </script>";
    }

    // Cerrar la conexión
    $conn->close();
?>
