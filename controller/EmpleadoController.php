<?php
require_once '../model/Database.php';  // Asegúrate de incluir el modelo Database.php

class EmpleadoController {

    public function registrarEmpleado() {
        // Crear una instancia de la clase Database
        $db = new Database();
        $conn = $db->connect();  // Obtener la conexión a la base de datos
        
        // Aquí podrías obtener los datos del formulario y registrarlos en la base de datos
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $nombre = $_POST['nombre'];
            $dni = $_POST['dni'];

            // Insertar los datos del empleado en la base de datos
            $stmt = $conn->prepare("INSERT INTO empleados (nombre, dni) VALUES (?, ?)");
            $stmt->bind_param("ss", $nombre, $dni);
            $stmt->execute();

            if ($stmt->execute()) {
                echo "Empleado registrado con éxito.";
            } else {
                echo "Error al registrar el empleado: " . $stmt->error;
            }
            
        }

        // Cargar la vista de registrar_empleado
        require_once '../view/registrar_empleado.php';
    }
}
?>
