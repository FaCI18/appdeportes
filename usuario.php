<?php

class Usuario {
    // Conexión a la base de datos
    private $servername = "localhost";
    private $username = "root";
    private $password = "";
    private $dbname = "appdeportes";

    private $conn;

    # Datos basicos de usuario
    private $id_usuario;
    private $nombre;
    private $apellido_materno;
    private $apellido_paterno;
    private $email;
    private $pass;
    private $rol;
    private $responsabilidades;
    private $activado;

    // Datos específicos de Alumno
    // Es un diccionario con el nombre de los datos de la tabla datos_usuario, y de valor una lista siendo el primer valor de la lista el id del registro en la tabla, 
    // y el segundo valor, lo que el usuario tiene guardado en la base de datos
    private $datos_estudiantes = [
        'edad' => [1, ''],
        'domicilio' => [2, ''],
        'telefono' => [3, ''],
        'fecha_nacimiento' => [4, ''],
        'lugar_nacimiento' => [5, ''],
        'peso' => [6, ''],
        'estatura' => [7, ''],
        'nivel_estudios' => [8, ''],
        'ocupacion' => [9, ''],
        'que_estudia' => [10, ''],
        'profesion' => [11, ''],
        'puesto' => [12, ''],
        'interes' => [13, ''],
        'disciplinas' => [14, ''],
    ];


    // Lista para los problemas de salud
    private $lista_psalud=[];

    // Constructor donde se inicia la conexion y se llama a la funcion load para las consultas
    public function __construct($id_usuario) {
        $this->id_usuario = $id_usuario;
        $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);
        $this->load();
    }

    // Cargar los datos del usuario desde la base de datos
    private function load() {

        // Obtener los datos basicos
        $sql_usuario = "SELECT * from usuarios where id_usuario = ".$this->id_usuario; // Consulta
        $result_usuario = $this->conn->query($sql_usuario); // Ejecutar consulta

        // Comprobamos que haya resultados, y lo guardamos en variables
        if ($result_usuario->num_rows > 0) {
            $row = $result_usuario->fetch_assoc();

            $this->nombre = $row['nombre'];
            $this->apellido_materno = $row['apellido_materno'];
            $this->apellido_paterno = $row['apellido_paterno'];
            $this->email = $row['email'];
            $this->pass = $row['pass'];
            $this->rol = $row['rol'];
            $this->responsabilidades = $row['responsabilidades'];
            $this->activado = $row['activado'];
        }

        // Obtener todos los datos específicos de la tabla datos_usuarios
        $sql_datos_estudiantes = "
                SELECT 
                    datos.id_dato,
                    datos.id_dato_estudiante,
                    datos.id_usuario,
                    datos.info,
                    datos_estudiantes.dato AS nombre_dato
                FROM 
                    datos
                INNER JOIN 
                    datos_estudiantes ON datos.id_dato_estudiante = datos_estudiantes.id_dato_estudiante
                WHERE 
                    datos.id_usuario = ".$this->id_usuario;
        $result_datos_estudiantes = $this->conn->query($sql_datos_estudiantes); // Ejecutamos la consulta

        // recorremos los resultados y lo guardamos en su respectivo elemento del diccionario
        if ($result_datos_estudiantes->num_rows > 0) {
            while ($row = $result_datos_estudiantes->fetch_assoc()) {
                $this -> datos_estudiantes[$row['nombre_dato']][1] = $row['info'];
            }
        }

        // Obtener todos los problemas de salud
        $sql_psalud = "
                SELECT 
                    *
                FROM 
                    psalud
                WHERE 
                    id_usuario = ".$this->id_usuario;
        $result_psalud = $this->conn->query($sql_psalud); // Ejecutamos la consulta

        // Comprobamos si hay problemas de salud y si hay, los guardamos en su lista
        if ($result_psalud->num_rows > 0) {
            while ($row = $result_psalud->fetch_assoc()) {
                $this -> lista_psalud[$row['id_psalud']] = [
                    "enfermedad" => $row['enfermedad'],
                    "adjunto" => $row['adjunto']
                ];
            }
        }
    }


    // Getters y Setters

    public function getId() {
        return $this->id_usuario;
    }

    public function getNombre() {
        return $this->nombre;
    }
    
    public function setNombre($nombre) {
        $this->nombre=$nombre;
    }

    public function getApellido_materno() {
        return $this->apellido_materno;
    }

    public function setApellido_materno($apellido_materno) {
        $this->apellido_materno = $apellido_materno;
    }

    public function getApellido_paterno() {
        return $this->apellido_paterno;
    }

    public function setApellido_paterno($apellido_paterno) {
        $this->apellido_paterno = $apellido_paterno;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setEmail($email) {
        $this->email  =$email;
    }

    public function getRol() {
        return $this->rol;
    }

    public function getActivado() {
        return $this->activado;
    }

    public function getResponsabilidades() {
        return $this->responsabilidades;
    }

    public function getPsalud() {
        return $this->lista_psalud;
    }

    public function getDatos() {
        return $this->datos_estudiantes;
    }

    public function setDatos($datosnuevos){
        $this->datos_estudiantes = [];
        $this->datos_estudiantes = $datosnuevos;
    }

    
}
?>
