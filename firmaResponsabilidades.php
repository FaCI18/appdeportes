<?php
session_start();

// Verificar que el usuario haya iniciado sesión y tenga rol de administrador (rol 1)
if (!isset($_SESSION['iduser'])) {
    header("Location: login.php");
    exit();
}


// Incluir la conexión a la base de datos
include 'conexion.php';

// Obtenemos de la sesión el id del usuario
$id_usuario = $_SESSION['iduser'];

// Protegemos la variable de la consulta para evitar problemas de seguridad
$id_usuario = $conn->real_escape_string($id_usuario);


// Creamos consulta con un update para actualizar el campo de responsabilidades a 1 (aceptado)
$sql = "UPDATE usuarios SET responsabilidades = 1 WHERE id_usuario = $id_usuario";

if ($conn->query($sql) === TRUE) { // Ejecutamos consulta y comprobamos que tenga exito
    // Si la inserción fue exitosa, mostrar alerta y redirigir a gestionUsuarios.php
    echo "<script>
            alert('Responsabilidades firmadas con exito');
            window.location.href = 'perfil.php';
          </script>";
} else {
    // Si ocurre un error, mostrar un mensaje de error
    echo "<script>
            alert('Error al aceptar el deslinde de responsabilidades: " . $conn->error . "');
            window.location.href = 'perfil.php';
          </script>";
}

// Cerrar la conexión
$conn->close();
?>
