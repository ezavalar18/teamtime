<?php

class AdminModel {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    public function obtenerUsuarioPorNombre($usuario) {
        $conn = $this->db->connect(); // ← Aquí usamos connect()
        $stmt = $conn->prepare("SELECT * FROM usuarios_admin WHERE usuario = ?");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    public function crearUsuario($nombres, $apellidos, $contrasena) {
        $conn = $this->db->connect();
        $usuario = $this->generarUsuario($nombres, $apellidos);
        $nombres_completos = $nombres . " " . $apellidos;
        $hash = password_hash($contrasena, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO usuarios_admin (nombre, usuario, contrasena) VALUES (?, ?, ?)");
        $stmt->bind_param("sss",$nombres_completos, $usuario, $hash);
    
        $exito = $stmt->execute();
        $stmt->close();
        return $exito;
    }

    //funcion para generar username
    public function generarUsuario($nombres, $apellidos) {
        // Separar nombres y apellidos
        $nombresArray = explode(' ', trim($nombres));
        $apellidosArray = explode(' ', trim($apellidos));
    
        // Tomar la primera letra del primer nombre
        $inicialNombre = strtolower(substr($nombresArray[0], 0, 1));
    
        // Tomar el primer apellido completo
        $primerApellido = strtolower($apellidosArray[0]);
    
        // Concatenar y devolver
        return $inicialNombre . $primerApellido;
    }
    
    
}



