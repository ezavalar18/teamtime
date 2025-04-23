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
    public function crearUsuario($usuario, $contrasena) {
        $conn = $this->db->connect();
        $hash = password_hash($contrasena, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO usuarios_admin (usuario, contrasena) VALUES (?, ?)");
        $stmt->bind_param("ss", $usuario, $hash);
    
        $exito = $stmt->execute();
        $stmt->close();
        return $exito;
    }
    
    
}



