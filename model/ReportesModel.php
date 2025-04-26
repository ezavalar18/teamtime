<?php
class ReportesModel {
    private $conn;

    public function __construct($database) {
        $this->conn = $database->connect();
    }

    public function getAsistencias($filters) {
        $query = "SELECT 
                    e.id as id_empleado,
                    e.codigo_empleado,
                    e.nombres as nombres,
                    e.apellidos as apellido,
                    a.area as area,
                    e.dni,
                    e.token_empleado as token,
                    r.fecha,
                    r.hora_entrada,
                    r.hora_salida,
                    tr.descripcion as tipo_registro
                  FROM registro_asistencia r
                  JOIN empleados e ON r.id_empleado = e.id
                  LEFT JOIN area a ON e.id_area = a.id_area
                  JOIN tipo_registro tr ON r.id_tipo_registro = tr.id
                  WHERE 1=1";

        // Aplicar filtros
        if (!empty($filters['search_term'])) {
            $searchTerm = $this->conn->real_escape_string($filters['search_term']);
            $searchType = $this->conn->real_escape_string($filters['search_type'] ?? 'nombres');
            
            switch($searchType) {
                case 'nombres': $query .= " AND e.nombres LIKE '%$searchTerm%'"; break;
                case 'apellido': $query .= " AND e.apellidos LIKE '%$searchTerm%'"; break;
                case 'codigo_empleado': $query .= " AND e.codigo_empleado LIKE '%$searchTerm%'"; break;
                case 'dni': $query .= " AND e.dni LIKE '%$searchTerm%'"; break;
                case 'token': $query .= " AND e.token LIKE '%$searchTerm%'"; break;
                case 'area': $query .= " AND a.nombres LIKE '%$searchTerm%'"; break;
            }
        }

        if (!empty($filters['fecha_inicio'])) {
            $query .= " AND r.fecha >= '".$this->conn->real_escape_string($filters['fecha_inicio'])."'";
        }

        if (!empty($filters['fecha_fin'])) {
            $query .= " AND r.fecha <= '".$this->conn->real_escape_string($filters['fecha_fin'])."'";
        }

        $query .= " ORDER BY r.fecha DESC, r.hora_entrada DESC";

        $result = $this->conn->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getHorasExtras($filters) {
        $query = "SELECT 
                    e.id as id_empleado,
                    e.codigo_empleado,
                    e.nombres as nombres,
                    e.apellidos as apellido,
                    a.area as area,
                    e.dni,
                    r.fecha,
                    TIMESTAMPDIFF(HOUR, e.hora_salida, r.hora_salida) as horas_extras
                  FROM registro_asistencia r
                  JOIN empleados e ON r.id_empleado = e.id
                  LEFT JOIN area a ON e.id_area = a.id_area
                  WHERE r.hora_salida > e.hora_salida";

        // Aplicar filtros
        if (!empty($filters['search_term'])) {
            $searchTerm = $this->conn->real_escape_string($filters['search_term']);
            $searchType = $this->conn->real_escape_string($filters['search_type'] ?? 'nombres');
            
            switch($searchType) {
                case 'nombres': $query .= " AND e.nombres LIKE '%$searchTerm%'"; break;
                case 'apellido': $query .= " AND e.apellidos LIKE '%$searchTerm%'"; break;
                case 'codigo_empleado': $query .= " AND e.codigo_empleado LIKE '%$searchTerm%'"; break;
                case 'dni': $query .= " AND e.dni LIKE '%$searchTerm%'"; break;
                case 'token': $query .= " AND e.token LIKE '%$searchTerm%'"; break;
                case 'area': $query .= " AND a.nombres LIKE '%$searchTerm%'"; break;
            }
        }

        if (!empty($filters['fecha_inicio'])) {
            $query .= " AND r.fecha >= '".$this->conn->real_escape_string($filters['fecha_inicio'])."'";
        }

        if (!empty($filters['fecha_fin'])) {
            $query .= " AND r.fecha <= '".$this->conn->real_escape_string($filters['fecha_fin'])."'";
        }

        $query .= " ORDER BY horas_extras DESC, r.fecha DESC";

        $result = $this->conn->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
}