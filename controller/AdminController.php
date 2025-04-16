<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';
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
        
        $nombre = $_POST['nombre'] ?? '';
        $usuario = $_POST['usuario'] ?? '';
        $contrasena = $_POST['contrasena'] ?? '';
        $correo = $_POST['correo'] ?? '';

        if (empty($usuario) || empty($contrasena)) {
            echo "Usuario o contraseña no pueden estar vacíos.";
            return;
        }

        $resultado = $this->modelo->crearUsuario($nombre, $usuario, $contrasena, $correo);

        if ($resultado) {
            header("Location: /admin/dashboard");
            exit;
        } else {
            echo "Error al crear el usuario.";
        }
    }

    public function recuperar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $correo = $_POST['correo'] ?? '';
            $usuario = $this->modelo->obtenerPorCorreo($correo);

            if ($usuario) {
                $token = bin2hex(random_bytes(16));
                $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));
                $this->modelo->guardarTokenRecuperacion($correo, $token, $expira);

                if ($this->enviarCorreoRecuperacion($correo, $token)) {
                    $data['mensaje'] = "Se ha enviado un enlace de recuperación a tu correo.";
                } else {
                    $data['error'] = "No se pudo enviar el correo. Intenta más tarde.";
                }
            } else {
                $data['error'] = "No se encontró una cuenta con ese correo.";
            }

            require __DIR__ . '/../view/admin/recuperar.php';
        } else {
            $data = [];
            require __DIR__ . '/../view/admin/recuperar.php';
        }
    }

    public function mostrarFormularioRestablecer() {
        $token = $_GET['token'] ?? '';

        if (empty($token)) {
            echo "Token inválido.";
            return;
        }

        $usuario = $this->modelo->obtenerPorToken($token);
        if (!$usuario) {
            echo "Token inválido o expirado.";
            return;
        }

        require __DIR__ . '/../view/admin/restablecer.php';
    }

    public function restablecerContrasena() {
        $token = $_POST['token'] ?? '';
        $nuevaContrasena = $_POST['nueva_contrasena'] ?? '';

        if (empty($token) || empty($nuevaContrasena)) {
            echo "Faltan datos.";
            return;
        }

        $usuario = $this->modelo->obtenerPorToken($token);
        if (!$usuario) {
            echo "Token inválido o expirado.";
            return;
        }

        $resultado = $this->modelo->actualizarContrasena($usuario['correo'], $nuevaContrasena);

        if ($resultado) {
            echo "Contraseña restablecida con éxito.";
        } else {
            echo "Hubo un problema al actualizar la contraseña.";
        }
    }

    private function verificarSesion() {
        if (!isset($_SESSION['admin'])) {
            header("Location: /admin/login");
            exit;
        }
    }

    private function enviarCorreoRecuperacion($para, $token) {
        $mail = new PHPMailer(true);

        try {
            $mail->CharSet = 'UTF-8';
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'samu1588@gmail.com';
            $mail->Password = 'coeg kwxk ckjv rrtj'; // Reemplaza por tu app password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('tucorreo@gmail.com', 'Sistema de Asistencia');
            $mail->addAddress($para);

            $mail->Subject = 'Recuperación de contraseña';
            $link = "http://192.168.1.13/admin/restablecer?token=$token";
            $mail->Body = "Hola, haz clic en el siguiente enlace para restablecer tu contraseña:\n\n$link";

            $mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
