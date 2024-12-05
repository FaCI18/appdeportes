<?php
session_start();

include 'usuario.php';

// Verificar que el usuario haya iniciado sesión y sea admin (rol =1)
if (!isset($_SESSION['iduser'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['roluser']!=1) {
    header("Location: index.php");
    exit();
}

// Incluir la conexión a la base de datos
include 'conexion.php';

// Guardamos los datos del formulario en variables para que sea mas fácil trabajar con ellos
$id_usuario = $_POST['id_usuario'];
$nombre= $_POST['nombre'];
$apellido_materno = $_POST['apellido_materno'];
$apellido_paterno = $_POST['apellido_paterno'];
$email= $_POST['email'];
$activado= $_POST['activado'];

// Creamos un diccionario con los datos de la TABLA datos y datos_estudiantes
// La clave es el nombre del campo
// El valor es una lista de 2 posiciones: Primera posicion: el id de ese dato en la tabla datos_estudiantes, la segunda posicion: El valor
// Por ejemplo: domicilio -> [2, Calle doctor San Juan]
$datos_estudiantes_nuevos = [
    'edad' => [1, $_POST['edad']],
    'domicilio' => [2, $_POST['domicilio']],
    'telefono' => [3, $_POST['telefono']],
    'fecha_nacimiento' => [4, $_POST['fecha_nacimiento']],
    'lugar_nacimiento' => [5, $_POST['lugar_nacimiento']],
    'peso' => [6, $_POST['peso']],
    'estatura' => [7, $_POST['estatura']],
    'nivel_estudios' => [8, $_POST['nivel_estudios']],
    'ocupacion' => [9, $_POST['ocupacion']],
    'que_estudia' => [10, $_POST['que_estudia']],
    'profesion' => [11, $_POST['profesion']],
    'puesto' => [12, $_POST['puesto']],
    'interes' => [13, $_POST['interes']],
    'disciplinas' => [14, $_POST['disciplinas']],
];

// Creamos una instancia de la tabla Usuario y guardamos sus datos en una variable $datos_usuario
$usuario = new Usuario($_POST['id_usuario']);
$datos_usuario = $usuario->getDatos();

// Actualizamos los datos del usuario mediante una consulta
$sql1 = "UPDATE usuarios SET nombre ='$nombre', apellido_materno='$apellido_materno', apellido_paterno='$apellido_paterno', email = '$email', activado='$activado' WHERE id_usuario = $id_usuario";
$conn->query($sql1); // Ejecutamos la consulta


// Funcion para actualizar cada uno de los datos de la tabla datos y datos_usuario
function actualizaDato($actDato) {
    global $datos_estudiantes_nuevos;
    global $datos_usuario;
    global $conn;
    global $id_usuario;
    
    // Hay varios casos posibles:
    // 1. Que no exista el registro en la base de datos, y haya que crearlo (INSERT)
    // 2. Que ya exista el registro en la base de datos, este caso se divide en 2:
    // 2.1 Si existe en la base de datos, pero el nuevo valor esta vacio, entonces hay que borrarlo de la base de datos (DELETE)
    // 2.2 Si existe en la base de datos y el valor nuevo es distinto, habrá que actualizar el reg istro en la base de datos (UPDATE)
    if ($datos_usuario[$actDato][1] == ''){
        if ($datos_estudiantes_nuevos[$actDato][1] == ''){
            $sqldato = "";
        }else{
            $info = $datos_estudiantes_nuevos[$actDato][1];
            $datoid = $datos_estudiantes_nuevos[$actDato][0];
            #$sqldato = "UPDATE datos SET info ='$info' WHERE id_dato_estudiante = $datoid AND id_usuario = $id_usuario";
            $sqldato = "INSERT INTO datos (id_dato_estudiante, id_usuario, info) VALUES ($datoid, $id_usuario, '$info')";
        }
    }else{
        if ($datos_estudiantes_nuevos[$actDato][1] == ''){
            $datoid = $datos_estudiantes_nuevos[$actDato][0];
            $sqldato = "DELETE FROM datos WHERE id_dato_estudiante = $datoid AND id_usuario=$id_usuario";
        }else{
            $info = $datos_estudiantes_nuevos[$actDato][1];
            $datoid = $datos_estudiantes_nuevos[$actDato][0];
            $sqldato = "UPDATE datos SET info ='$info' WHERE id_dato_estudiante = $datoid AND id_usuario = $id_usuario";
        }
    }

    if ($sqldato!=''){
        $conn->query($sqldato);
    }

}

// Ejecutamos la funcion de arriba para cada uno de los datos
actualizaDato('edad');
actualizaDato('domicilio');
actualizaDato('telefono');
actualizaDato('fecha_nacimiento');
actualizaDato('lugar_nacimiento');
actualizaDato('peso');
actualizaDato('estatura');
actualizaDato('nivel_estudios');
actualizaDato('ocupacion');
actualizaDato('que_estudia');
actualizaDato('profesion');
actualizaDato('puesto');
actualizaDato('interes');
actualizaDato('disciplinas');


// Muestra una alerta y nos manda de nuevo a gestionUsuarios.php
echo "<script> alert('Actualizado con exito'); window.location.href = 'gestionUsuarios.php'; </script>";


// Cerrar la conexión
$conn->close();
?>
