<?php
session_start();
//es igual para todos los archivos del admin
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

// Obtener datos del formulario y guardarlos en una variable
$nombre = $_POST['nombre'];
$apellido_materno = $_POST['apellido_materno'];
$apellido_paterno = $_POST['apellido_paterno'];
$email = $_POST['email'];
$pass = $_POST['pass'];
$rol = $_POST['rol'];

// Asegurar los valores para evitar inyecciones SQL, bugs o hackeos
$nombre = $conn->real_escape_string($nombre);
$apellido_materno = $conn->real_escape_string($apellido_materno);
$apellido_paterno = $conn->real_escape_string($apellido_paterno);
$email = $conn->real_escape_string($email);
$pass = $conn->real_escape_string($pass);
$rol = $conn->real_escape_string($rol);

// Insertar el usuario en la base de datos
$sql = "INSERT INTO usuarios (nombre, apellido_materno, apellido_paterno, email, pass, rol) 
        VALUES ('$nombre', '$apellido_materno', '$apellido_paterno', '$email', '$pass', '$rol')";

if ($conn->query($sql) === TRUE) { // Ejecutamos la consulta
    // Si la inserción fue exitosa, mostrar alerta y redirigir a gestionUsuarios.php
    echo "<script>
            alert('Registro completado con éxito');
            window.location.href = 'gestionUsuarios.php';
          </script>";
} else {
    // Si ocurre un error, mostrar un mensaje de error
    echo "<script>
            alert('Error al registrar el usuario: " . $conn->error . "');
            window.location.href = 'gestionUsuarios.php';
          </script>";
}

// Cerrar la conexión
$conn->close();
?>
