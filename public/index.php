<?php
date_default_timezone_set('America/Lima');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir todos los controladores necesarios
require_once __DIR__ . '/../controller/AsistenciaController.php';
require_once __DIR__ . '/../controller/AdminController.php';
require_once __DIR__ . '/../controller/DashboardController.php';
require_once __DIR__ . '/../controller/ReportesController.php';

// Inicializar la base de datos
require_once __DIR__ . '/../Database.php';
$database = new Database();

// Inicializar controladores
$asistenciaController = new AsistenciaController();
$adminController = new AdminController();
$dashboardController = new DashboardController($database);
$reportesController = new ReportesController($database);

// Obtener la URI solicitada
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/');

// Manejar las rutas
switch ($uri) {
    case '':
    case '/marcacion':
        $asistenciaController->mostrarMarcacion();
        break;

    case '/registrar':
        $asistenciaController->registrarMarcacion();
        break;

    // Rutas del admin
    case '/admin/login':
        $adminController->login();
        break;

    case '/admin/autenticar':
        $adminController->autenticar();
        break;

    case '/admin/logout':
        $adminController->logout();
        break;

    case '/admin/dashboard':
        $dashboardController->index();
        break;

    // Reportes
    case '/admin/reportes/asistencias':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $reportesController->asistencias();
        } else {
            $reportesController->asistencias();
        }
        break;

    case '/admin/reportes/horas-extras':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $reportesController->horasExtras();
        } else {
            $reportesController->horasExtras();
        }
        break;

    case '/admin/crear_usuario':
        $adminController->crearUsuario();
        break;

    case '/admin/guardar_usuario':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $adminController->guardarUsuario();
        } else {
            http_response_code(405);
            echo "Método no permitido.";
        }
        break;
    
    case '/admin/recuperar':
        $adminController->recuperar();
        break;  
            
    case '/admin/restablecer':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $adminController->restablecerContrasena();
        } else {
            $adminController->mostrarFormularioRestablecer();
        }
        break;            
            
    case '/admin/procesar_restablecer':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $adminController->procesarRestablecer();
        } else {
            http_response_code(405);
            echo "Método no permitido.";
        }
        break;

    case '/admin/actualizar_usuario':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $adminController->actualizar_usuario();
        } else {
            http_response_code(405);
            echo "Método no permitido.";
        }
        break;

    case (preg_match('#^/admin/eliminar_usuario/(\d+)$#', $uri, $matches) ? true : false):
        $adminController->eliminarUsuario($matches[1]);
        break;

    case '/admin/usuarios':
        $adminController->usuarios();
        break;
                                                           
    case '/admin/asistencias':
        $adminController->asistencias_hoy();
        break;
            
    case '/admin/actualizar_asistencia':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $adminController->actualizar_asistencia();
        } else {
            http_response_code(405);
            echo "Método no permitido.";
        }
        break;
                            
    default:
        http_response_code(404);
        echo "Página no encontrada.";
        break;
}