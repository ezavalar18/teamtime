<?php
class DashboardModel {
    private $conn;

    public function __construct($database) {
        $this->conn = $database->connect();
    }

    public function getTotalEmpleados() {
        $result = $this->conn->query("SELECT COUNT(*) as total FROM empleados WHERE estado = 1");
        return $result ? $result->fetch_assoc()['total'] ?? 0 : 0;
    }

    public function getEmpleadosActivos() {
        $result = $this->conn->query(
            "SELECT COUNT(DISTINCT id_empleado) as total 
             FROM registro_asistencia 
             WHERE MONTH(fecha) = MONTH(CURRENT_DATE()) 
             AND YEAR(fecha) = YEAR(CURRENT_DATE())"
        );
        return $result ? $result->fetch_assoc()['total'] ?? 0 : 0;
    }

    public function getFaltasMes() {
        $result = $this->conn->query(
            "SELECT COUNT(*) as total 
             FROM registro_asistencia 
             WHERE id_tipo_registro IN (2,8,9,10) 
             AND MONTH(fecha) = MONTH(CURRENT_DATE())"
        );
        return $result ? $result->fetch_assoc()['total'] ?? 0 : 0;
    }

    public function getHorasExtras() {
        $result = $this->conn->query(
            "SELECT SUM(TIMESTAMPDIFF(HOUR, e.hora_salida, r.hora_salida)) as total 
             FROM registro_asistencia r 
             JOIN empleados e ON r.id_empleado = e.id 
             WHERE r.hora_salida > e.hora_salida 
             AND MONTH(fecha) = MONTH(CURRENT_DATE())"
        );
        return $result ? $result->fetch_assoc()['total'] ?? 0 : 0;
    }

    public function getTardanzas() {
        $result = $this->conn->query(
            "SELECT COUNT(*) as total 
             FROM registro_asistencia 
             WHERE TIME(hora_entrada) > '08:05:00' 
             AND id_tipo_registro = 11 
             AND MONTH(fecha) = MONTH(CURRENT_DATE())"
        );
        return $result ? $result->fetch_assoc()['total'] ?? 0 : 0;
    }

    public function getPromedioAsistencia($totalEmpleados) {
        $result = $this->conn->query(
            "SELECT COUNT(DISTINCT fecha) as total 
             FROM registro_asistencia 
             WHERE MONTH(fecha) = MONTH(CURRENT_DATE())"
        );
        $totalDiasLaborales = $result ? $result->fetch_assoc()['total'] ?? 0 : 0;

        $result = $this->conn->query(
            "SELECT COUNT(*) as total 
             FROM registro_asistencia 
             WHERE id_tipo_registro = 1 
             AND MONTH(fecha) = MONTH(CURRENT_DATE())"
        );
        $asistencias = $result ? $result->fetch_assoc()['total'] ?? 0 : 0;

        if ($totalDiasLaborales > 0 && $totalEmpleados > 0) {
            return round(($asistencias / ($totalEmpleados * $totalDiasLaborales)) * 100);
        }
        return 0;
    }

    public function getAsistenciaMensual() {
        $result = $this->conn->query(
            "SELECT 
                MONTH(fecha) as mes,
                COUNT(CASE WHEN id_tipo_registro = 1 THEN 1 END) as presentes,
                COUNT(CASE WHEN id_tipo_registro IN (2,8,9,10) THEN 1 END) as faltas,
                COUNT(CASE WHEN id_tipo_registro = 11 THEN 1 END) as tardanzas
             FROM registro_asistencia 
             WHERE YEAR(fecha) = YEAR(CURRENT_DATE())
             GROUP BY MONTH(fecha)
             ORDER BY mes"
        );
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getUltimosRegistros() {
        $result = $this->conn->query(
            "SELECT e.nombres, e.apellidos, r.fecha, r.hora_entrada, r.hora_salida, r.id_tipo_registro 
             FROM registro_asistencia r 
             JOIN empleados e ON r.id_empleado = e.id 
             ORDER BY r.fecha DESC, r.id DESC 
             LIMIT 5"
        );
        return $result ?: [];
    }

    public function getTopTardanzas() {
        $result = $this->conn->query(
            "SELECT e.nombres, e.apellidos, COUNT(*) as total 
             FROM registro_asistencia r 
             JOIN empleados e ON r.id_empleado = e.id 
             WHERE r.id_tipo_registro = 11 
             GROUP BY r.id_empleado 
             ORDER BY total DESC 
             LIMIT 5"
        );
        return $result ?: [];
    }

    public function getTopHorasExtras() {
        $result = $this->conn->query(
            "SELECT e.nombres, e.apellidos, SUM(TIMESTAMPDIFF(HOUR, e.hora_salida, r.hora_salida)) as total 
             FROM registro_asistencia r 
             JOIN empleados e ON r.id_empleado = e.id 
             WHERE r.hora_salida > e.hora_salida 
             GROUP BY r.id_empleado 
             ORDER BY total DESC 
             LIMIT 5"
        );
        return $result ?: [];
    }
}
?>