<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../Database.php';
require_once __DIR__ . '/../model/AdminModel.php';

class AdminController {
    private $modelo;

    public function __construct() {
        $db = new Database();
        $this->modelo = new AdminModel($db);
    }

    public function login() {
        require __DIR__ . '/../view/admin/login.php';
    }

    public function autenticar() {
        $usuario = $_POST['usuario'] ?? '';
        $clave = $_POST['contrasena'] ?? '';

        if (empty($usuario) || empty($clave)) {
            echo "Usuario o contraseña vacíos";
            return;
        }

        $result = $this->modelo->obtenerUsuarioPorNombre($usuario);

        if ($result && password_verify($clave, $result['contrasena'])) {
            $_SESSION['admin'] = $result['usuario'];
            header("Location: /admin/dashboard");
            exit;
        } else {
            echo "Credenciales inválidas";
            require __DIR__ . '/../view/admin/login.php';
        }
    }

    public function logout() {
        session_destroy();
        header("Location: /admin/login");
        exit;
    }

    public function dashboard() {
        $this->verificarSesion();
        require __DIR__ . '/../view/admin/dashboard.php';
    }
    public function crearUsuario() {
        $this->verificarSesion();
        require __DIR__ . '/../view/admin/nuevo_usuario.php';
    }
    
    public function guardarUsuario() {
        $this->verificarSesion();
    
        //$usuario = $_POST['usuario'] ?? '';
        $nombres = $_POST['nombres'] ?? '';
        $apellidos = $_POST['apellidos'] ?? '';
        $contrasena = $_POST['contrasena'] ?? '';
    
        if (empty($nombres) || empty($apellidos) || empty($contrasena)) {
            echo "Nombres o Apellidos o contraseña no pueden estar vacíos.";
            return;
        }
    
        $resultado = $this->modelo->crearUsuario($nombres, $apellidos, $contrasena);
    
        if ($resultado) {
            header("Location: /admin/dashboard");
            exit;
        } else {
            echo "Error al crear el usuario.";
        }
    }
    

    private function verificarSesion() {
        if (!isset($_SESSION['admin'])) {
            header("Location: /admin/login");
            exit;
        }
    }
}
