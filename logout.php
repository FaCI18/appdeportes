<?php
    session_start();

    // Comprobamos que tenga la sesion inciada
    if (isset($_SESSION['iduser'])) {
        session_unset(); // Cerramos la sesion
        session_destroy(); // Destruimos los datos guardados en la sesion del servidor
        header("Location: index.php");
    } else {
        header("Location: index.php");
    }
?>