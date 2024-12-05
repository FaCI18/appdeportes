
<?php
    include 'sesion.php';
    if (isset($_SESSION['iduser'])) {
        header("Location: index.php");
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Login - Escuela de Deportes</title>

    <!-- =============== Bootstrap Core CSS =============== -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" type="text/css">
    <!-- =============== fonts awesome =============== -->
    <link rel="stylesheet" href="assets/font/css/font-awesome.min.css" type="text/css">
    <!-- =============== Custom CSS =============== -->
    <link rel="stylesheet" href="assets/css/style.css" type="text/css">
    
    <!-- =============== Custom Style for Background =============== -->
    <style>
        body {
            background-image: url('assets/img/fondo.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            font-family: 'Courier', sans-serif;
        }
        .login-panel {
            margin-top: 150px; 
            background-color: rgba(255, 255, 255, 0.8); 
            padding: 20px;
            border-radius: 10px;
        }
    </style>

</head>

<body>
    <!-- =============== nav =============== -->
    <?php include 'menu.php'; ?>

    <!-- =============== Login Container =============== -->
    <div class="container">
        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <div class="panel panel-default login-panel">
                    <div class="text-center" style="opacity: 1">
                        <h3>Iniciar Sesión</h3>
                    </div>
                    <div class="panel-body">
                        <!--En esta parte lo que hacemos es pasarle mediante metodo POST los datos de inicio de sesion es decir, 
                        usuario(email) y contraseña a nuestro archivo validalogin.php, no se hace todo el proceso en el mismo archivo para evitar
                        bugs y por seguridad-->
                        <form action="validalogin.php" method="POST">
                            <div class="form-group">
                                <label for="username">Email</label>
                                <input type="text" class="form-control" name="email" id="email" placeholder="Ingresa tu email" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Contraseña</label>
                                <input type="password" class="form-control" name="password" id="password" placeholder="Ingresa tu contraseña" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">Aceptar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- =============== jQuery =============== -->
    <script src="assets/js/jquery.js"></script>
    <!-- =============== Bootstrap Core JavaScript =============== -->
    <script src="assets/js/bootstrap.min.js"></script>
</body>
</html>
