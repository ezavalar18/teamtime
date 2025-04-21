<?php

class AsistenciaModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db->connect();
    }

    public function obtenerEmpleadoPorToken($token) {
        $stmt = $this->conn->prepare("SELECT id FROM empleados WHERE token_empleado = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function obtenerRegistroDelDia($id_empleado, $fecha) {
        $stmt = $this->conn->prepare("SELECT * FROM registro_asistencia WHERE id_empleado = ? AND fecha = ?");
        $stmt->bind_param("is", $id_empleado, $fecha);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function registrarEntrada($id_empleado, $token, $fecha, $hora) {
        $stmt = $this->conn->prepare("INSERT INTO registro_asistencia (id_empleado, token_empleado, fecha, hora_entrada) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $id_empleado, $token, $fecha, $hora);
        return $stmt->execute();
    }

    public function registrarSalida($id_empleado, $token, $fecha, $hora) {
        // Verificar si ya existe una entrada para este empleado en el mismo día
        $stmt = $this->conn->prepare("SELECT * FROM registro_asistencia WHERE id_empleado = ? AND fecha = ? AND token_empleado = ? AND hora_entrada IS NOT NULL LIMIT 1");
        $stmt->bind_param("iss", $id_empleado, $fecha, $token);
        $stmt->execute();
        $resultado = $stmt->get_result();
    
        // Si no existe una entrada, no permitir la salida
        if ($resultado->num_rows === 0) {
            return "No se puede registrar una salida sin haber registrado una entrada primero.";
        }
    
        // Si hay una entrada, registrar la salida
        $stmt = $this->conn->prepare("UPDATE registro_asistencia SET hora_salida = ? WHERE id_empleado = ? AND fecha = ? AND token_empleado = ? AND hora_salida IS NULL");
        $stmt->bind_param("siss", $hora, $id_empleado, $fecha, $token);
        $stmt->execute();
    
        // Verificamos si se actualizó correctamente
        if ($stmt->affected_rows > 0) {
            return "Salida registrada correctamente.";
        } else {
            return "Error al registrar la salida. Tal vez ya se haya registrado.";
        }
    }
    

    public function actualizarHora($tipo, $id_empleado, $fecha, $hora) {
        $campo = ($tipo === 'entrada') ? 'hora_entrada' : 'hora_salida';
        $query = "UPDATE registro_asistencia SET $campo = ? WHERE id_empleado = ? AND fecha = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sis", $hora, $id_empleado, $fecha);
        return $stmt->execute();
    }
    
}
