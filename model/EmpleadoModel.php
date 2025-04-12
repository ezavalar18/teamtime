<?php
class EmpleadoModel {
    private $conn;

    public function __construct($conexion) {
        $this->conn = $conexion;
    }

    public function existeDNI($dni) {
        $stmt = $this->conn->prepare("SELECT dni FROM vendedores WHERE dni = ?");
        $stmt->bind_param("s", $dni);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    public function generarCodigoNuevo() {
        $result = $this->conn->query("SELECT cod_usuario FROM vendedores ORDER BY cod_usuario DESC LIMIT 1");
        if ($row = $result->fetch_assoc()) {
            $ultimo = (int)substr($row['cod_usuario'], 3);
            return 'ven' . str_pad($ultimo + 1, 4, '0', STR_PAD_LEFT);
        }
        return 'ven0001';
    }

    public function registrarEmpleado($data) {
        $sql = "INSERT INTO vendedores (cod_usuario, vendedor, dni, genero, fecha_nacimiento, celular, email, direccion, estado, edad)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'activo', ?)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param(
            "ssssssssi",
            $data['cod_usuario'], $data['vendedor'], $data['dni'], $data['genero'],
            $data['fecha_nacimiento'], $data['celular'], $data['email'], $data['direccion'], $data['edad']
        );

        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }

        return false;
    }

    public function registrarPlanilla($id_vendedor, $tipo_contrato, $jornada, $salario) {
        $sql = "INSERT INTO planilla (id_vendedor, contrato, jornada, salario) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("issd", $id_vendedor, $tipo_contrato, $jornada, $salario);
        return $stmt->execute();
    }
}
