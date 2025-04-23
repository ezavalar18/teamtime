<?php

// AsistenciaController.php
date_default_timezone_set('America/Lima');

require_once __DIR__ . '/../Database.php';
require_once __DIR__ . '/../model/AsistenciaModel.php';

class AsistenciaController {
    private $modelo;

    public function __construct() {
        $db = new Database();
        $this->modelo = new AsistenciaModel($db);
    }

    public function mostrarMarcacion() {
        date_default_timezone_set('America/Lima');
        $fecha = date('Y-m-d');
        $hora = $_POST['hora'] ?? date('H:i:s');
        require __DIR__ . '/../view/marcacion.php';
    }

    public function registrarMarcacion() {
        date_default_timezone_set('America/Lima');
        $token = $_POST['cod_usuario'] ?? '';
        $tipo = $_POST['tipo'] ?? '';
        $mensaje = '';
    
        if (!preg_match('/^\d{5}$/', $token) || !in_array($tipo, ['entrada', 'salida'])) {
            $mensaje = "Token inválido o tipo incorrecto.";
        } else {
            $empleado = $this->modelo->obtenerEmpleadoPorToken($token);
            if (!$empleado) {
                $mensaje = "Empleado no encontrado.";
            } else {
                $id_empleado = $empleado['id'];
                $fecha = date('Y-m-d');
                $hora = date('H:i:s');
                $registro = $this->modelo->obtenerRegistroDelDia($id_empleado, $fecha);
                
                if ($tipo === 'entrada') {
                    if ($registro->num_rows > 0) {
                        // Ya existe un registro de entrada para hoy
                        $fila = $registro->fetch_assoc();
                        if (!empty($fila['hora_entrada'])) {
                            $mensaje = "El token $token ya tiene una marcación de ingreso para hoy.";
                        } else {
                            $mensaje = "Error inesperado: ya existe un registro, pero sin hora de entrada.";
                        }
                    } else {
                        // No existe registro, se registra la entrada
                        $this->modelo->registrarEntrada($id_empleado, $token, $fecha, $hora);
                        $mensaje = "Entrada registrada correctamente.";
                    }
                } else { // salida
                    if ($registro->num_rows > 0) {
                        // Ya existe un registro para hoy, verificamos si tiene entrada
                        $fila = $registro->fetch_assoc();
                        if (empty($fila['hora_entrada'])) {
                            $mensaje = "No se puede registrar una salida sin haber registrado una entrada primero.";
                        } elseif (!empty($fila['hora_salida'])) {
                            $mensaje = "El token $token ya tiene una marcación de salida para hoy.";
                        } else {
                            // Se puede registrar la salida
                            $this->modelo->actualizarHora('salida', $id_empleado, $fecha, $hora);
                            $mensaje = "Salida registrada correctamente.";
                        }
                    } else {
                        $mensaje = "No se puede registrar salida sin haber registrado una entrada.";
                    }
                }
            }
        }
    
        // Mostrar la vista con el mensaje
        $hora = date('H:i:s');
        require __DIR__ . '/../view/marcacion.php';
    }
    
    
    
    
}
