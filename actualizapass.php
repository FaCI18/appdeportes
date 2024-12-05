<?php
session_start();

// Comprobar que el usuario haya iniciado sesión y que la contraseña nueva que llega desde el formulario no este vacía
if (!isset($_SESSION['iduser'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_POST['pass'])) {
    header("Location: perfil.php");
    exit();
}else{
    if($_POST['pass'] == ''){
        header("Location: perfil.php");
        exit();
    }
}

// Incluir la conexión a la base de datos
include 'conexion.php';

// Guardamos en variables los datos del formulario
$pass = $_POST['pass'];
$id_usuario = $_SESSION['iduser'];

// Agregamos seguridad a las variables para evitar bugs y hackeos
$id_usuario = $conn->real_escape_string($id_usuario);
$pass = $conn->real_escape_string($pass);


// Guardamos la consulta en una variable
$sql = "UPDATE usuarios SET pass = '$pass' WHERE id_usuario = $id_usuario";

if ($conn->query($sql) === TRUE) {
    // Si la inserción fue exitosa, mostrar alerta y redirigir a gestionUsuarios.php
    echo "<script>
            alert('Contraseña actualizada con exito');
            window.location.href = 'perfil.php';
          </script>";
} else {
    // Si ocurre un error, mostrar un mensaje de error
    echo "<script>
            alert('Error al actualizar la contraseña: " . $conn->error . "');
            window.location.href = 'perfil.php';
          </script>";
}

// Cerrar la conexión
$conn->close();
?>
