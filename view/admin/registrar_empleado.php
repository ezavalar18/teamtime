<?php
// Configurar la zona horaria
date_default_timezone_set('America/Lima');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Conexión a la base de datos
require_once(__DIR__ . '/../../Database.php');
$database = new Database();
$conn = $database->connect(); // Asegúrate que esta conexión funcione

// Procesar formulario de registro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos del formulario
    $nombres = $conn->real_escape_string($_POST['nombre']);
    $apellidos = $conn->real_escape_string($_POST['apellido']);
    $dni = $conn->real_escape_string($_POST['dni']);
    $genero = $conn->real_escape_string($_POST['genero']);
    $nacimiento = $conn->real_escape_string($_POST['fecha_nacimiento']); // Corregido nombre del campo
    $celular = $conn->real_escape_string($_POST['celular']);
    $email = $conn->real_escape_string($_POST['email']);
    $direccion = $conn->real_escape_string($_POST['direccion']);
    $tipo_contrato = $conn->real_escape_string($_POST['tipo_contrato']);
    $jornada = $conn->real_escape_string($_POST['jornada']);
    $salario = $conn->real_escape_string($_POST['salario']);
    
    // Generar código de empleado y token (ejemplo)
    $codigo_empleado = substr($dni, -6);
    $token = bin2hex(random_bytes(16));

    try {
        // Insertar en tabla empleado (corregida la consulta SQL)
        $sql_empleado = "INSERT INTO empleado (codigo_empleado, nombres, apellidos, dni, token, genero, nacimiento, celular, email, direccion) 
                         VALUES ('$codigo_empleado', '$nombres', '$apellidos', '$dni', '$token', '$genero', '$nacimiento', '$celular', '$email', '$direccion')";
        
        if (!$conn->query($sql_empleado)) {
            throw new Exception("Error al registrar empleado: " . $conn->error);
        }
    
        $id_empleado = $conn->insert_id;
    
        // Insertar en tabla planilla
        $sql_planilla = "INSERT INTO planilla (id_empleado, tipo_contrato, jornada, salario) 
                         VALUES ('$id_empleado', '$tipo_contrato', '$jornada', '$salario')";
        
        if (!$conn->query($sql_planilla)) {
            throw new Exception("Error al registrar en planilla: " . $conn->error);
        }
    
        echo "<script>alert('Empleado registrado exitosamente'); window.location.href='registrar_empleado.php';</script>";
        exit;
    
    } catch (Exception $e) {
        error_log("Error en registro: " . $e->getMessage());
        echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Empleado</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 90%;
            max-width: 600px;
            margin: 50px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="date"],
        input[type="number"],
        input[type="email"],
        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .radio-group {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }
        .radio-option {
            display: flex;
            align-items: center;
        }
        button {
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            background-color: #4CAF50;
            color: white;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
            transition: 0.3s;
        }
        button:hover {
            background-color: #45a049;
        }
        .button-container {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Registrar Empleado</h1>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" required>
            </div>
            
            <div class="form-group">
                <label for="apellido">Apellido:</label>
                <input type="text" id="apellido" name="apellido" required>
            </div>
            
            <div class="form-group">
                <label for="dni">DNI:</label>
                <input type="text" id="dni" name="dni" required>
            </div>
            
            <div class="form-group">
                <label>Género:</label>
                <div class="radio-group">
                    <div class="radio-option">
                        <input type="radio" id="masculino" name="genero" value="1" required>
                        <label for="masculino" style="display: inline; font-weight: normal;">Masculino</label>
                    </div>
                    <div class="radio-option">
                        <input type="radio" id="femenino" name="genero" value="2">
                        <label for="femenino" style="display: inline; font-weight: normal;">Femenino</label>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required>
            </div>
            
            <div class="form-group">
                <label for="celular">Celular:</label>
                <input type="text" id="celular" name="celular" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="direccion">Dirección:</label>
                <input type="text" id="direccion" name="direccion" required>
            </div>
            
            <div class="form-group">
                <label for="tipo_contrato">Tipo de Contrato:</label>
                <select id="tipo_contrato" name="tipo_contrato" required>
                    <option value="planilla">Planilla</option>
                    <option value="honorario">Honorario</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Jornada:</label>
                <div class="radio-group">
                    <div class="radio-option">
                        <input type="radio" id="completa" name="jornada" value="completa" required>
                        <label for="completa" style="display: inline; font-weight: normal;">Completa</label>
                    </div>
                    <div class="radio-option">
                        <input type="radio" id="parcial" name="jornada" value="parcial">
                        <label for="parcial" style="display: inline; font-weight: normal;">Parcial</label>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="salario">Salario:</label>
                <input type="number" id="salario" name="salario" step="0.01" required>
            </div>
            
            <div class="button-container">
                <button type="button" onclick="window.location.href='index.php'">Volver</button>
                <button type="submit">Registrar Empleado</button>
            </div>
        </form>
    </div>
</body>
</html>
