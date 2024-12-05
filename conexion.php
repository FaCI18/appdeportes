<?php
// Conexión a la base de datos
//hacemos la conexion, pasamos el nombre del servidor, el nombre del usuario de la base de datos, la contraseña en caso de haber(en nuestro vaso no hay)
//y el nombre de la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "appdeportes";

$conn = new mysqli($servername, $username, $password, $dbname);
//comprueba si la conexion de la base de datos falla, lo que hace es matar el proceso y muentra un mensaje de alerta 
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

?>