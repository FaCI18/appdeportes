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

    // Guardar variables que provienen del POST del formulario
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $fecha_inicio =  $_POST['fecha_inicio'];
    $fecha_fin =  $_POST['fecha_fin'];
    $precio =  $_POST['precio'];
    $cantidadClases =  $_POST['cantClases'];

    // Insertar el usuario en la base de datos
    $sql = "INSERT INTO paquetes (nombre, descripcion, fecha_inicio, fecha_fin, cantidadClases, precio) 
    VALUES ('$nombre', '$descripcion', '$fecha_inicio', '$fecha_fin', '$cantidadClases', '$precio')"; // Consulta

    if ($conn->query($sql) === TRUE) { // Ejecuta consulta y comprueba que sea exitosa

        echo "<script>
                alert('Paquete creado con éxito!');
                window.location.href = 'gestionPaquetes.php';
            </script>";

    } else {
        // Si ocurre un error, mostrar un mensaje de error
        echo "<script>
                alert('Error al crear el paquete: " . $conn->error . "');
                window.location.href = 'gestionPaquetes.php';
            </script>";
    }

    //

    // Cerrar la conexión
    $conn->close();
?>