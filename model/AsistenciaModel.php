<?php
 
class AsistenciaModel {
    private $conn;
 
    public function __construct($db) {
        $this->conn = $db->connect();
    }
 
    /**
     * Verifica si el token corresponde a un empleado válido
     */
    private function validarTokenEmpleado($id_empleado, $token) {
        $stmt = $this->conn->prepare("SELECT id FROM empleados WHERE id = ? AND token_empleado = ?");
        $stmt->bind_param("is", $id_empleado, $token);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
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
        // Validar token primero
        if (!$this->validarTokenEmpleado($id_empleado, $token)) {
            return false;
        }
 
        // Verificar si ya existe un registro para ese día
        $registro = $this->obtenerRegistroDelDia($id_empleado, $fecha);
        if ($registro->num_rows > 0) {
            return false; // Ya existe un registro
        }
 
        // Registrar entrada
        $stmt = $this->conn->prepare("INSERT INTO registro_asistencia (id_empleado, fecha, hora_entrada) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $id_empleado, $fecha, $hora);
        return $stmt->execute();
    }
 
    public function registrarSalida($id_empleado, $token, $fecha, $hora) {
        // Validar token primero
        if (!$this->validarTokenEmpleado($id_empleado, $token)) {
            return "Token no válido para este empleado";
        }
 
        // Verificar que existe una entrada sin salida
        $stmt = $this->conn->prepare("SELECT id FROM registro_asistencia
                                    WHERE id_empleado = ? AND fecha = ?
                                    AND hora_entrada IS NOT NULL
                                    AND hora_salida IS NULL");
        $stmt->bind_param("is", $id_empleado, $fecha);
        $stmt->execute();
        $stmt->store_result();
       
        if ($stmt->num_rows === 0) {
            return "No se puede registrar salida sin entrada previa o ya tiene salida registrada";
        }
 
        // Registrar salida
        $stmt = $this->conn->prepare("UPDATE registro_asistencia
                                     SET hora_salida = ?
                                     WHERE id_empleado = ? AND fecha = ?
                                     AND hora_salida IS NULL");
        $stmt->bind_param("sis", $hora, $id_empleado, $fecha);
        $stmt->execute();
 
        return $stmt->affected_rows > 0
            ? "Salida registrada correctamente"
            : "Error al registrar salida";
    }
 
    public function actualizarHora($tipo, $id_empleado, $fecha, $hora) {
        // Validar tipo de operación
        if (!in_array($tipo, ['entrada', 'salida'])) {
            return false;
        }
 
        $campo = ($tipo === 'entrada') ? 'hora_entrada' : 'hora_salida';
        $query = "UPDATE registro_asistencia SET $campo = ? WHERE id_empleado = ? AND fecha = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sis", $hora, $id_empleado, $fecha);
        return $stmt->execute();
    }
}