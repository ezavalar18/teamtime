<?php

class AdminModel {
    private $conn;

    // Recibe el objeto Database y se conecta
    public function __construct($db) {
        $this->conn = $db->connect();
    }

    public function obtenerUsuarioPorNombre($usuario) {
        $stmt = $this->conn->prepare("SELECT * FROM usuarios_admin WHERE usuario = ?");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function obtenerPorCorreo($correo) {
        $stmt = $this->conn->prepare("SELECT * FROM usuarios_admin WHERE correo = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function guardarTokenRecuperacion($correo, $token, $expira) {
        $stmt = $this->conn->prepare("UPDATE usuarios_admin SET reset_token = ?, token_expira = ? WHERE correo = ?");
        $stmt->bind_param("sss", $token, $expira, $correo);
        return $stmt->execute();
    }

    public function crearUsuario($nombre, $usuario, $contrasena, $correo) {
        $hash = password_hash($contrasena, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("INSERT INTO usuarios_admin (nombre, usuario, contrasena, correo) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nombre, $usuario, $hash, $correo);
        return $stmt->execute();
    }
    public function obtenerPorToken($token) {
        $conn = $this->db->connect();
        $sql = "SELECT * FROM usuarios_admin WHERE token_recuperacion = ? AND token_expira > NOW() LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_assoc();
    }
    
    public function actualizarContrasena($correo, $nuevaContrasena) {
        $conn = $this->db->connect();
        $hash = password_hash($nuevaContrasena, PASSWORD_DEFAULT);
    
        $sql = "UPDATE usuarios_admin SET contrasena = ?, token_recuperacion = NULL, token_expira = NULL WHERE correo = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $hash, $correo);
    
        return $stmt->execute();
    }
    
}
