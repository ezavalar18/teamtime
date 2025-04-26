<?php
require_once __DIR__ . '/../model/DashboardModel.php';

class DashboardController {
    private $model;
    private $database;

    public function __construct($database) {
        $this->database = $database;
        $this->model = new DashboardModel($database);
        
        // Iniciar sesión si no está iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function index() {
        // Verificar autenticación
        if (!isset($_SESSION['admin'])) {
            header('Location: /admin/login');
            exit;
        }

        try {
            // Obtener todos los datos necesarios
            $totalEmpleados = $this->model->getTotalEmpleados();
            $empleadosActivos = $this->model->getEmpleadosActivos();
            $faltasMes = $this->model->getFaltasMes();
            $horasExtras = $this->model->getHorasExtras();
            $tardanzas = $this->model->getTardanzas();
            $promedioAsistencia = $this->model->getPromedioAsistencia($totalEmpleados);
            $asistenciaMensual = $this->model->getAsistenciaMensual();
            $ultimosRegistros = $this->model->getUltimosRegistros();
            $topTardanzas = $this->model->getTopTardanzas();
            $topHorasExtras = $this->model->getTopHorasExtras();

            // Preparar datos para la vista con valores por defecto
            $viewData = [
                'totalEmpleados' => $totalEmpleados ?? 0,
                'empleadosActivos' => $empleadosActivos ?? 0,
                'faltasMes' => $faltasMes ?? 0,
                'horasExtras' => $horasExtras ?? 0,
                'tardanzas' => $tardanzas ?? 0,
                'promedioAsistencia' => $promedioAsistencia ?? 0,
                'asistenciaMensual' => $asistenciaMensual ?? [],
                'ultimosRegistros' => $ultimosRegistros ?? [],
                'topTardanzas' => $topTardanzas ?? [],
                'topHorasExtras' => $topHorasExtras ?? [],
                'porcentajeComparacion' => 2.5 // Valor de ejemplo
            ];

            // Pasar datos a la vista
            extract($viewData);
            require __DIR__ . '/../view/admin/dashboard.php';

        } catch (Exception $e) {
            error_log("Error en DashboardController: " . $e->getMessage());
            $_SESSION['error'] = "Error al cargar el dashboard";
            header('Location: /admin/dashboard');
            exit;
        }
    }
}