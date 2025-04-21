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
            $_SESSION['rol'] = $result['rol'];  // Guardamos el rol del usuario
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
        
        $rol = $_SESSION['rol'];
    
        // Obtener estadísticas
        $asistenciasHoy = $this->modelo->obtenerAsistenciasHoy();
        $faltasHoy = $this->modelo->obtenerFaltasHoy();  // Asegúrate de que esta línea esté aquí
        $cantidadUsuarios = $this->modelo->obtenerUsuariosRegistrados();
        $usuariosRegistrados = $this->modelo->contarUsuariosRegistrados();
    
        // Para los admins, puedes obtener más datos, por ejemplo, todos los usuarios registrados.
        $usuarios = [];
        if ($rol == 'admin') {
            $usuarios = $this->modelo->obtenerUsuarios();
        }
    
        // Pasar las variables necesarias a la vista
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
        $rol = $_POST['rol'] ?? '';  // Obtenemos el rol del formulario
    
        if (empty($usuario) || empty($contrasena) || empty($rol)) {
            echo "Usuario, contraseña y rol no pueden estar vacíos.";
            return;
        }
    
        // Creamos el nuevo usuario
        $resultado = $this->modelo->crearUsuario($nombre, $usuario, $contrasena, $correo, $rol);
    
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
                $tokenr = bin2hex(random_bytes(16));
                $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));
                $this->modelo->guardarTokenRecuperacion($correo, $tokenr, $expira);

                if ($this->enviarCorreoRecuperacion($correo, $tokenr)) {
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
        $tokenr = $_GET['tokenr'] ?? '';
    
        if (empty($tokenr)) {
            echo "Token vacío.";
            return;
        }
    
        $usuario = $this->modelo->obtenerPorToken($tokenr);
    
        if (!$usuario) {
            echo "Token inválido o expirado.";
            return;
        }
    
        // 🔥 PASA el token como variable para la vista
        $tokenActual = $tokenr;
    
        require __DIR__ . '/../view/admin/restablecer.php';
    }

    public function restablecerContrasena() {
        $tokenr = $_POST['tokenr'] ?? '';
        $nuevaContrasena = $_POST['nueva_contrasena'] ?? '';
        
        if (empty($tokenr) || empty($nuevaContrasena)) {
            echo "Faltan datos.";
            return;
        }

        $usuario = $this->modelo->obtenerPorToken($tokenr);
        if (!$usuario) {
            echo "Token inválido o expirado.";
            return;
        }

        $resultado = $this->modelo->actualizarContrasena($usuario['correo'], $nuevaContrasena);

        if ($resultado) {
            // Limpiar el token después de actualizar la contraseña
            $this->modelo->limpiarTokenRecuperacion($usuario['correo']);
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

    private function enviarCorreoRecuperacion($para, $tokenr) {
        $mail = new PHPMailer(true);

        try {
            $mail->CharSet = 'UTF-8';
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'samu1588@gmail.com';
            $mail->Password = 'coeg kwxk ckjv rrtj'; //app password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('tucorreo@gmail.com', 'Sistema de Asistencia');
            $mail->addAddress($para);

            $mail->Subject = 'Recuperación de contraseña';
            $link = "http://192.168.1.11/admin/restablecer?tokenr=$tokenr"; // ✅
            $mail->Body = "Hola, haz clic en el siguiente enlace para restablecer tu contraseña:\n\n$link";

            $mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function editarUsuario() {
        $this->verificarSesion();
    
        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo "ID de usuario no proporcionado.";
            return;
        }
    
        $usuario = $this->modelo->obtenerUsuarioPorId($id);
        if (!$usuario) {
            echo "Usuario no encontrado.";
            return;
        }
    
        require __DIR__ . '/../view/admin/editar_usuario.php';
    }

    public function actualizar_usuario() {
        $this->verificarSesion();
    
        $id = $_POST['id'] ?? null;
        $nombre = $_POST['nombre'] ?? '';
        $usuario = $_POST['usuario'] ?? '';
        $correo = $_POST['correo'] ?? '';
        $rol = $_POST['rol'] ?? '';
    
        if ($id) {
            $this->modelo->actualizarUsuario($id, $nombre, $usuario, $correo, $rol);
            header("Location: /admin/usuarios");
            exit;
        } else {
            echo "Error: ID de usuario no proporcionado.";
        }
    }
    
    public function eliminarUsuario($id) {
        if ($_SESSION['rol'] !== 'admin') {
            header('Location: /admin/dashboard');
            exit;
        }
    
        // Usamos el modelo que ya está inicializado en el constructor
        $resultado = $this->modelo->eliminarUsuarioPorId($id);
    
        if ($resultado) {
            header('Location: /admin/dashboard');
            exit;
        } else {
            echo "Error al eliminar el usuario.";
        }
    }
    
    public function editarMarcaciones() {
        $this->verificarSesion();
        
        // Solo los editores pueden ver esta página
        $rol = $_SESSION['rol'];
        if ($rol !== 'editor') {
            header('Location: /admin/dashboard');
            exit;
        }
    
        // Obtener las marcaciones del día
        $marcaciones = $this->modelo->obtenerMarcacionesHoy();
    
        require __DIR__ . '/../view/admin/editar_marcaciones.php';
    }
    
    public function actualizarMarcacion() {
        $this->verificarSesion();
        
        $rol = $_SESSION['rol'];
        if ($rol !== 'editor') {
            header('Location: /admin/dashboard');
            exit;
        }
    
        $id = $_POST['id'] ?? '';
        $horaEntrada = $_POST['hora_entrada'] ?? '';
        $horaSalida = $_POST['hora_salida'] ?? '';
    
        if (empty($id) || empty($horaEntrada) || empty($horaSalida)) {
            echo "Datos incompletos.";
            return;
        }
    
        // Actualizar la marcación
        $resultado = $this->modelo->editarMarcacion($id, $horaEntrada, $horaSalida);
    
        if ($resultado) {
            header("Location: /admin/editarMarcaciones");
            exit;
        } else {
            echo "Hubo un problema al actualizar la marcación.";
        }
    }
    
    public function descargarReporte() {
        $this->verificarSesion();
        
        $rol = $_SESSION['rol'];
        if ($rol !== 'editor') {
            header('Location: /admin/dashboard');
            exit;
        }
    
        // Llamar al modelo para generar el reporte CSV
        $this->modelo->generarReporteCSV();
        exit;
    }
    public function usuarios() {
        $this->verificarSesion();  // Verifica que el usuario esté logueado
    
        $usuarios = $this->modelo->obtenerUsuarios();  // O lo que sea necesario para obtener los usuarios
    
        require __DIR__ . '/../view/admin/usuarios.php';  // La vista de los usuarios
    }

    // En AdminController.php
public function asistencias_hoy() {
    $this->verificarSesion();
    
    // Llamamos al modelo para obtener las asistencias del día
    $asistencias = $this->modelo->getAsistenciasHoy();
    
    // Pasamos las asistencias a la vista
    require_once '../view/admin/asistencias.php';
}
public function actualizarAsistencia() {
    // Verificar que se haya enviado el formulario
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Recoger los datos del formulario
        $id = $_POST['id'];
        $fecha = $_POST['fecha'];
        $hora_entrada = $_POST['hora_entrada'];
        $hora_salida = $_POST['hora_salida'];

        // Llamar al modelo para realizar la actualización
        $this->adminModel->actualizarAsistencia($id, $fecha, $hora_entrada, $hora_salida);

        // Redirigir después de la actualización
        header("Location: /admin/asistencias_hoy");
        exit;
    } else {
        // Si no es un POST, retornar un error
        http_response_code(405);
        echo "Método no permitido.";
    }
}


    
}
