<?php
// public/index.php

ini_set('display_errors', 1);
error_reporting(E_ALL);

define('APP_ROOT', dirname(__DIR__) . '/');

// Asegúrate de que la ruta del CSS esté bien

$stylesheets = '<link rel="stylesheet" href="/styles.css?v=<?= time() ?>">';

require_once APP_ROOT . 'autoload.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/');

// Aquí manejamos el ruteo de las vistas
switch ($uri) {
    case '':
    case '/marcacion':
        $controller = new AsistenciaController();
        // Pasamos la variable $stylesheets para que se agregue en la vista
        $controller->mostrarMarcacion($stylesheets);
        break;

    case '/registro':
        $db = new Database();
        $model = new RegistroModel($db->connect());
        $controller = new RegistroController($model);
        $controller->mostrarRegistro($stylesheets);
        break;

    case '/registrar_empleado':
        $controller = new EmpleadoController();
        $controller->registrarEmpleado($stylesheets);
        break;

    default:
        http_response_code(404);
        echo "Página no encontrada: $uri";
        break;
}

