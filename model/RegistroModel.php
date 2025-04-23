<?php
class RegistroModel {
    private $conn;

    public function __construct($conexion) {
        $this->conn = $conexion;
    }

    public function buscarUsuarios($criterio) {
        $usuarios = [];

        $sql = "SELECT u.cod_usuario, u.vendedor, u.dni, ra.hora_entrada, ra.hora_salida
                FROM vendedores u
                LEFT JOIN registro_asistencia ra ON u.cod_usuario = ra.cod_usuario
                WHERE u.cod_usuario LIKE ? OR u.dni LIKE ? OR u.vendedor LIKE ?
                ORDER BY ra.fecha ASC";

        $stmt = $this->conn->prepare($sql);
        $criterio_wildcard = "%" . $criterio . "%";
        $stmt->bind_param("sss", $criterio_wildcard, $criterio_wildcard, $criterio_wildcard);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $usuarios[] = $row;
        }

        return $usuarios;
    }
}
