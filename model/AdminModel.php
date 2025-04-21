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

    public function guardarTokenRecuperacion($correo, $tokenr, $expira) {
        $stmt = $this->conn->prepare("UPDATE usuarios_admin SET reset_token = ?, token_expira = ? WHERE correo = ?");
        $stmt->bind_param("sss", $tokenr, $expira, $correo);
        return $stmt->execute();
    }

    public function crearUsuario($nombre, $usuario, $contrasena, $correo, $rol) {
        // Asegúrate de encriptar la contraseña antes de guardarla
        $contrasenaEncriptada = password_hash($contrasena, PASSWORD_DEFAULT);
    
        $stmt = $this->conn->prepare("INSERT INTO usuarios_admin (nombre, usuario, contrasena, correo, rol) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $nombre, $usuario, $contrasenaEncriptada, $correo, $rol);
    
        return $stmt->execute();
    }
    

    public function obtenerPorToken($tokenr) {
        $sql = "SELECT * FROM usuarios_admin WHERE reset_token = ? AND token_expira > NOW() LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $tokenr);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_assoc();
    }

    public function actualizarContrasena($correo, $nuevaContrasena) {
        $hash = password_hash($nuevaContrasena, PASSWORD_DEFAULT);
        $sql = "UPDATE usuarios_admin SET contrasena = ?, reset_token = NULL, token_expira = NULL WHERE correo = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $hash, $correo);
        return $stmt->execute();
    }

    public function limpiarTokenRecuperacion($correo) {
        $sql = "UPDATE usuarios_admin SET reset_token = NULL, token_expira = NULL WHERE correo = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $correo);
        return $stmt->execute();
    }
    public function obtenerUsuarios() {
        $query = "SELECT id, nombre, usuario, correo, rol FROM usuarios_admin";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    //para actualizar//
    public function obtenerUsuarioPorId($id) {
        $stmt = $this->conn->prepare("SELECT * FROM usuarios_admin WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function actualizarUsuario($id, $nombre, $usuario, $correo, $rol) {
        $stmt = $this->conn->prepare("UPDATE usuarios_admin SET nombre = ?, usuario = ?, correo = ?, rol = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $nombre, $usuario, $correo, $rol, $id);
        return $stmt->execute();
    }
    
    public function eliminarUsuarioPorId($id){
        $stmt = $this->conn->prepare("DELETE FROM usuarios_admin WHERE id = ?");
        $stmt->bind_param("i", $id); // Es importante asegurarse de que el tipo de parámetro es el correcto
        return $stmt->execute();
    }

    // Obtener marcaciones para el editor
public function obtenerMarcaciones() {
    $stmt = $this->conn->prepare("SELECT * FROM registro_asistencia WHERE DATE(fecha) = CURDATE()");
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Función para descargar reporte (en formato CSV)
public function descargarReporte() {
    $stmt = $this->conn->prepare("SELECT * FROM registro_asistencia");
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
// Obtener todas las marcaciones del día
public function obtenerMarcacionesHoy() {
    $stmt = $this->conn->prepare("SELECT * FROM registro_asistencia WHERE DATE(fecha) = CURDATE()");
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
// Editar una marcación
public function editarMarcacion($id, $horaEntrada, $horaSalida) {
    $stmt = $this->conn->prepare("UPDATE registro_asistencia SET hora_entrada = ?, hora_salida = ? WHERE id = ?");
    $stmt->bind_param("ssi", $horaEntrada, $horaSalida, $id);
    return $stmt->execute();
}
// Obtener todas las marcaciones
public function obtenerTodasLasMarcaciones() {
    $stmt = $this->conn->prepare("SELECT * FROM registro_asistencia");
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Función para generar reporte CSV
public function generarReporteCSV() {
    $data = $this->obtenerTodasLasMarcaciones();
    
    // Abrir un archivo en memoria
    $output = fopen('php://output', 'w');
    
    // Escribir el encabezado del CSV
    fputcsv($output, ['ID', 'Empleado', 'Fecha', 'Hora Entrada', 'Hora Salida']);
    
    // Escribir los datos
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    
    // Forzar la descarga del archivo CSV
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="reporte_marcaciones.csv"');
    fclose($output);
}

public function obtenerAsistenciasHoy() {
    $stmt = $this->conn->prepare("
        SELECT COUNT(*) as total 
        FROM registro_asistencia 
        WHERE DATE(fecha) = CURDATE()
    ");
    $stmt->execute();
    $resultado = $stmt->get_result()->fetch_assoc();
    return $resultado['total'] ?? 0;
}
public function obtenerFaltasHoy() {
    $stmt = $this->conn->prepare("
        SELECT COUNT(*) AS total 
        FROM empleados 
        WHERE id NOT IN (
            SELECT id_empleado 
            FROM registro_asistencia 
            WHERE DATE(fecha) = CURDATE()
        )
    ");
    $stmt->execute();
    $resultado = $stmt->get_result()->fetch_assoc();
    return $resultado['total'] ?? 0;
}
public function obtenerUsuariosRegistrados() {
    $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM usuarios_admin");
    $stmt->execute();
    $resultado = $stmt->get_result()->fetch_assoc();
    return $resultado['total'] ?? 0;
}
// En AdminModel.php
public function contarPorRol($rol) {
    // Usamos '?' en lugar de ':rol' para mysqli
    $query = "SELECT COUNT(*) FROM usuarios_admin WHERE rol = ?";
    
    // Preparando la consulta
    if ($stmt = $this->conn->prepare($query)) {
        // Vinculando el parámetro
        $stmt->bind_param("s", $rol);  // "s" es el tipo de dato (string)
        
        // Ejecutando la consulta
        $stmt->execute();
        
        // Obteniendo el resultado
        $stmt->bind_result($count);
        $stmt->fetch();
        
        // Cerramos el statement
        $stmt->close();
        
        return $count;
    } else {
        // En caso de error al preparar la consulta
        return null; 
    }
}
// En AdminModel.php

// En AdminModel.php

public function contarUsuariosRegistrados() {
    $query = "SELECT COUNT(*) AS total FROM usuarios_admin";  // Ajusta el nombre de la tabla según tu base de datos

    $stmt = $this->conn->prepare($query);
    $stmt->execute();

    $resultado = $stmt->get_result()->fetch_assoc();  // Usar get_result() y fetch_assoc() en lugar de fetch()
    return $resultado['total'];  // Retorna el total de usuarios registrados
}
// En AdminModel.php
public function getAsistenciasHoy() {
    $fecha_hoy = date('Y-m-d');
    $query = "SELECT a.id_empleado, e.nombres, a.fecha, a.hora_entrada, a.hora_salida
              FROM registro_asistencia a
              INNER JOIN empleados e ON a.id_empleado = e.id
              WHERE a.fecha = ?";

    // Preparar la consulta
    $stmt = $this->conn->prepare($query);
    
    // Verificar si la preparación fue exitosa
    if ($stmt === false) {
        die('Error en la preparación de la consulta: ' . $this->conexion->error);
    }

    // Vincular los parámetros
    $stmt->bind_param('s', $fecha_hoy);  // 's' significa que el parámetro es una cadena (string)
    
    // Ejecutar la consulta
    $stmt->execute();
    
    // Obtener los resultados
    $result = $stmt->get_result();
    
    // Devolver los resultados como un array asociativo
    return $result->fetch_all(MYSQLI_ASSOC);
}
public function actualizarAsistencia($id, $fecha, $hora_entrada, $hora_salida) {
    $stmt = $this->conn->prepare("UPDATE registro_asistencia SET fecha = ?, hora_entrada = ?, hora_salida = ? WHERE id = ?");
    $stmt->bind_param("sssi", $fecha, $hora_entrada, $hora_salida, $id);
    $stmt->execute();
}






    
}
