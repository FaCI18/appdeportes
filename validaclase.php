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
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $fecha =  $_POST['fecha'];
    $hora =  $_POST['hora'];
    $cupos =  $_POST['cupos'];
    $profesores =  $_POST['profesores'];

    // Insertar el usuario en la base de datos
    $sql = "INSERT INTO clases (nombre, descripcion, fecha_clase, id_horario, cupos, precio) 
    VALUES ('$nombre', '$descripcion', '$fecha', '$hora', '$cupos', 100)";

    if ($conn->query($sql) === TRUE) { // Ejecutar consulta y comprobar que sea correcta

        // Si la inserción fue exitosa, adjuntamos los profesores
        $ultimo_id = $conn->insert_id; // Obtener el id del INSERT
        
        // Recorremos todos los profesores y los añádimos en un INSERT a la tabla de clases profesores
        foreach ($profesores as $key => $value) {
            $sql = "INSERT INTO clases_profesores (id_clase, id_profesor) 
                VALUES ($ultimo_id, $value)";
            $conn->query($sql);
        }

        echo "<script>
                alert('Clase creada con éxito!');
                window.location.href = 'gestionClases.php';
            </script>";

    } else {
        // Si ocurre un error, mostrar un mensaje de error
        echo "<script>
                alert('Error al crear la clase: " . $conn->error . "');
                window.location.href = 'gestionClases.php';
            </script>";
    }

    //

    // Cerrar la conexión
    $conn->close();
?>