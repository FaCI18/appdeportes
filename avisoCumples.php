<?php

include 'sesion.php';

if (isset($_SESSION['iduser'])) {

    # Comprueba que sea admin
    if ($_SESSION['roluser'] !=1){
        header("Location: index.php");
    }
}else{
    header("Location: index.php");
}

// Incluir la conexión a la base de datos
include 'conexion.php';

// Extraemos de la base de datos una lista de los profesores
$listaProfes=[];

$sql = "SELECT * FROM usuarios WHERE rol=2"; // Variable con la consulta
$result = $conn->query($sql); // Ejecutamos la consulta

// Creamos diccionario con nombre y email de cada profesor
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $listaProfes[$row['id_usuario']]=[
            "nombreProfe" => $row['nombre'],
            "emailProfe" => $row['email'],
        ];
    }
}

// Obtenemos lista de alumnos que cumplen años al dia siguiente
$listaCumple=[];

// Guardamos la consulta en una variable
// Es una consulta avanzada, con JOIN a distintas tablas donde comprobamos que la fecha de nacimiento guardada del alumno sea igual al dia de mañana
// SIN contar el año, solo tenemos en cuenta mes y dia

$sql = "SELECT u.*
    FROM usuarios u
    JOIN datos d ON u.id_usuario = d.id_usuario
    JOIN datos_estudiantes de ON d.id_dato_estudiante = de.id_dato_estudiante
    WHERE de.dato = 'fecha_nacimiento'
    AND d.id_dato_estudiante = 4
    AND MONTH(STR_TO_DATE(d.info, '%Y-%m-%d')) = MONTH(CURDATE() + INTERVAL 1 DAY)
    AND DAY(STR_TO_DATE(d.info, '%Y-%m-%d')) = DAY(CURDATE() + INTERVAL 1 DAY);
    ";
$result = $conn->query($sql); // Ejecutamos consulta
$count = 0; // Contador para mostrar en la alerta cuantos Alumnos cumplen años

// Recorremos los resultados, y guardamos en un diccionario el nombre del alumno y el email
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $count++; // Aumentamos el count para la alerta
        $listaCumple[$row['id_usuario']]=[
            "nombreAlumno" => $row['nombre'] . " ". $row['apellido_paterno'] . " ". $row['apellido_materno'],
            "emailAlumno" => $row['email'],
        ];
    }
}



// Incluir las librerias necesarias para mandar Emails
// En este caso utilizaremos PhPMailer como libreria para enviar emails, y Brevo como API para intermediar entre PhPMailer y nuestra dirección de Email
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Crear una instancia de PHPMailer
$mail = new PHPMailer(true);

// Le decimos a PhPMailer que utilice por defecto la codificación UTF-8 para poder poner acentos y Ñ
$mail->CharSet = 'UTF-8';


try {
    // Configuración SMTP de Brevo
    // Esto es la configuración del servicio Brevo, proporcionado por ellos desde el panel de control

    $mail->isSMTP();
    $mail->Host = 'smtp-relay.brevo.com';
    $mail->SMTPAuth = true;
    $mail->Username = '804eb9001@smtp-brevo.com'; // Nuestro usuario
    $mail->Password = 'csxgL6YqQyJw32Mm'; // Nuestra contraseña o TOKEN para la API
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;


    // Comprueba que haya al menos 1 elemento en la lista de alumnos que cumplen años
    if (count($listaCumple)>0) {

        // Recorremos la lista de profesores a los que mandarle el email
        foreach ($listaProfes as $key => $value) {

            // Guardamos el elemento actual del bucle en variables para facilitar el trabajar con los datos
            $email = $value['emailProfe'];
            $nombreProfe= $value['nombreProfe'];

            // Dirección de remitente y destinatario
            $mail->setFrom('proyectoingenieria444@gmail.com', 'Administrador');
            $mail->addAddress("$email", "$nombreProfe"); // Rellenamos con el email y nombre del profesor al que mandarle el mail

            // Contenido del correo
            $mail->isHTML(true); // Establecemos que lo mande como HTML (Es mas simple y rápido)
            $mail->Subject = "Lista de cumpleaños en MarInc $nombreProfe"; // Asunto del Email
            
            // Construir la lista de cumpleañeros como si fuera una lista de HTML
            $listaCumpleñosHtml = '<ul>';  // Comienza la lista

            // Recorremos la lista de cumpleaños
            foreach ($listaCumple as $key => $value) {
                $nombreAlumno = $value['nombreAlumno'];
                $listaCumpleñosHtml .= "<li>$nombreAlumno</li>";  // Añade el nombre a la lista
            }
            $listaCumpleñosHtml .= '</ul>';  // Cierra la lista

            // Contenido del correo con la lista de cumpleaños
            $mail->Body = "Buenos días $nombreProfe, te adjunto la lista de los alumnos que cumplen años mañana para que puedas felicitarlos!<br><br>";
            $mail->Body .= "Alumnos que cumplen años mañana:<br>";
            $mail->Body .= $listaCumpleñosHtml;  // Añadir la lista de cumpleañeros al cuerpo del correo


            // Enviar el correo
            $mail->send();
        }

        // Mostrar alerta con la cantidad de cumpleaños totales
        echo "<script> alert('Avisos de email enviados, total de cumpleaños mañana: $count'); window.location.href = 'index.php'; </script>";
    }else{
        // MOstrar alerta de que no existen cumpleaños al dia siguiente
        echo "<script> alert('No hay cumpleaños para mañana, no se envió ningún email'); window.location.href = 'index.php'; </script>";
    }
    
   
} catch (Exception $e) {

    // SI hay algun error, se muestra la alerta con el error
    echo "<script> alert('Error al enviar los email'); window.location.href = 'index.php'; </script>";
}
?>
