<?php
//session star lo unico que hace es iniciar la sesion es decir 
//que nuestro programa tendra acceso a los datos que el servidor tiene guardados para nuestro dispositivo y navegador
//puede haber varias sesiones en un dispositivo en varios navegadores(1 por navegador) 
session_start();
//este if comprueba que le haya llegado datos del formulario en este caso el email
//en caso de que no lleguen datos nos redirigira al login
//el hecho de que le lleguen datos no quiere decir que que sean correctos eso se valida despues
if (!isset($_POST['email'])) {
    header("Location: login.php");
}

// Incluir la conexión a la base de datos
include 'conexion.php';

// Obtener datos del formulario
//guardasmos en variables, la informacion que nos llega del post(es la informacion que el login nos envia)
$email = $_POST['email'];
$pass = $_POST['password'];

// Evitar inyecciones SQL
//se protejen las variables que se pasaran en la query, es solo por seguridad ya que manejamos contraseñas y tecnicamente deberian ser seguras.
$email = $conn->real_escape_string($email);
$pass = $conn->real_escape_string($pass);

// Verificar credenciales en la base de datos
//de la linea 39 a la 67 es una sola query que explicare paso a pasito
$sql = "SELECT * FROM usuarios WHERE email = '$email' AND pass = '$pass'";  //primer paso guardamos la consulta en una variable
$result = $conn->query($sql);  //ejecutamos la consulta 

//en este caso el if comprueba que exitan resultados de la consulta, en dado caso que no encuentre resultados de los paramentros
//que enviamos (email y contraseña), nos regresara un mensaje de error
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();//tomamos uno de los resultados, solo podemos tener un usuarios con el mismo correo y contraseña
    
    //la variable row es donde metimos el resultado de la query, si el esuario existe entonces lo guarda en esta variable
    //en el if siguiente lo que hacemos es validar que el usuario este en activo que es decir que este en estado 1 en caso de inactivo estaria en estado 0
    //esto es por lo que pidio la profa de eliminar, en nuestro caso no eliminamos, solo desactivamos cosas es decir pasamos de estado 1 a 0
    //despues de verificar que este activo guardamos nombre, apellido paterno, rol e id en la sesion que activamos
    //el nombre y apellido solo es para que se muestre en pantalla en el perfil del usuario, las verificaciones y 
    //todas las comprobaciones se hacen unicamente en base al id_usuario y el rol
    //los roles de usuario son 1=administrador, 2=profesor y 3=alumno
    //se hace asi porque nuestra base de datos deberia estar normalizada y esto conlleva a no repetir 800 tablas de lo mismo
    //asignando roles evitamos tener tablas extras  
    if ($row['activado']==1) { 
        $_SESSION['iduser'] = $row['id_usuario'];
        $_SESSION['roluser'] = $row['rol'];
        $_SESSION['nombreuser'] = $row['nombre'];
        $_SESSION['apellido_paterno'] = $row['apellido_paterno'];
        header("Location: index.php");
    }else{
        echo "<script>alert('Cuenta desactivada'); window.location.href='login.php';</script>";
    }

    

    
} else {
    // Si los datos son incorrectos
    echo "<script>alert('Email o Contraseña incorrectos'); window.location.href='login.php';</script>";
}

$conn->close();
?>
