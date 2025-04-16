<?php

date_default_timezone_set('America/Lima');

require_once __DIR__ . '/../controller/AsistenciaController.php';
require_once __DIR__ . '/../controller/AdminController.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/');

$asistenciaController = new AsistenciaController();
$adminController = new AdminController();

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
        $adminController->dashboard();
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

    default:
        http_response_code(404);
        echo "Página no encontrada.";
        break;
}
