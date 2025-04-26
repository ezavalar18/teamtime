<?php
require_once __DIR__ . '/../model/ReportesModel.php';

class ReportesController {
    private $model;
    private $database;

    public function __construct($database) {
        $this->database = $database;
        $this->model = new ReportesModel($database);
    }

    public function asistencias() {
        if (!isset($_SESSION['admin'])) {
            header('Location: /admin/login');
            exit;
        }

        $filters = $_POST ?? [];
        $data = $this->model->getAsistencias($filters);

        if (isset($_GET['export']) && $_GET['export'] == 'excel') {
            $this->exportToExcel($data, 'reporte_asistencias');
            exit;
        }

        require_once __DIR__ . '/../view/admin/reportes/asistencias.php';
    }

    public function horasExtras() {
        if (!isset($_SESSION['admin'])) {
            header('Location: /admin/login');
            exit;
        }

        $filters = $_POST ?? [];
        $data = $this->model->getHorasExtras($filters);

        if (isset($_GET['export']) && $_GET['export'] == 'excel') {
            $this->exportToExcel($data, 'reporte_horas_extras');
            exit;
        }

        require_once __DIR__ . '/../view/admin/reportes/horas_extras.php';
    }

    private function exportToExcel($data, $filename) {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="'.$filename.'_'.date('Y-m-d').'.xls"');
        
        echo '<table border="1">';
        
        // Encabezados
        if (!empty($data)) {
            echo '<tr>';
            foreach(array_keys($data[0]) as $header) {
                echo '<th>'.ucfirst(str_replace('_', ' ', $header)).'</th>';
            }
            echo '</tr>';
        }
        
        // Datos
        foreach($data as $row) {
            echo '<tr>';
            foreach($row as $cell) {
                echo '<td>'.$cell.'</td>';
            }
            echo '</tr>';
        }
        
        echo '</table>';
    }
}
?>