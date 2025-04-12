<?php

class AsistenciaModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db->connect();
    }

    public function registrarMarcacion($token, $tipo) {
        $stmt = $this->conn->prepare("SELECT id FROM empleados WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $empleado = $resultado->fetch_assoc();
            $empleado_id = $empleado['id'];
            $fecha = date('Y-m-d');
            $hora = date('H:i:s');

            $stmt = $this->conn->prepare("SELECT * FROM asistencia WHERE empleado_id = ? AND fecha = ?");
            $stmt->bind_param("is", $empleado_id, $fecha);
            $stmt->execute();
            $resultado = $stmt->get_result();

            if ($resultado->num_rows > 0) {
                $asistencia = $resultado->fetch_assoc();
                if ($tipo == 'entrada' && $asistencia['hora_entrada'] == null) {
                    $stmt = $this->conn->prepare("UPDATE asistencia SET hora_entrada = ? WHERE id = ?");
                    $stmt->bind_param("si", $hora, $asistencia['id']);
                    $stmt->execute();
                    return "Hora de entrada registrada correctamente.";
                } elseif ($tipo == 'salida' && $asistencia['hora_salida'] == null) {
                    $stmt = $this->conn->prepare("UPDATE asistencia SET hora_salida = ? WHERE id = ?");
                    $stmt->bind_param("si", $hora, $asistencia['id']);
                    $stmt->execute();
                    return "Hora de salida registrada correctamente.";
                } else {
                    return "Ya se ha registrado la $tipo.";
                }
            } else {
                if ($tipo == 'entrada') {
                    $stmt = $this->conn->prepare("INSERT INTO asistencia (empleado_id, fecha, hora_entrada) VALUES (?, ?, ?)");
                    $stmt->bind_param("iss", $empleado_id, $fecha, $hora);
                    $stmt->execute();
                    return "Hora de entrada registrada correctamente.";
                } else {
                    return "Debe registrar la hora de entrada antes que la de salida.";
                }
            }
        } else {
            return "Token no válido.";
        }
    }
}

