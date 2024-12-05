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

    // Guardar variables que vienen del POST del formulario
    $id_paquete = $_POST['idpaquete'];
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $fecha_inicio =  $_POST['fecha_inicio'];
    $fecha_fin =  $_POST['fecha_fin'];
    $activado = $_POST['activado'];

    // Actualizar la paquete en la base de datos (sin modificar el precio ni la cantidad de clases)
    $sql = "UPDATE paquetes SET 
                nombre = '$nombre', 
                descripcion = '$descripcion', 
                fecha_inicio = '$fecha_inicio',
                fecha_fin = '$fecha_fin',
                activado = '$activado'
            WHERE id_paquete = $id_paquete";

    if ($conn->query($sql) === TRUE) { // Ejecutamos consulta y comprobamos si fue exitosa

        echo "<script>
                alert('Paquete actualizado con éxito!');
                window.location.href = 'gestionPaquetes.php';
            </script>";

    } else {
        // Si ocurre un error, mostrar un mensaje de error
        echo "<script>
                alert('Error al actualizar el paquete: " . $conn->error . "');
                window.location.href = 'gestionPaquetes.php';
            </script>";
    }

    // Cerrar la conexión
    $conn->close();
?>
