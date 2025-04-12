<?php

class RegistroController {
    private $model;

    public function __construct($model) {
        $this->model = $model;
    }

    public function mostrarRegistro() {
        $mensaje = '';
        $usuarios = [];

        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['buscar'])) {
            $criterio = $_POST['criterio_busqueda'];
            $usuarios = $this->model->buscarUsuarios($criterio);
        }

        require_once APP_ROOT . 'view/registro.php';
    }
}

?>
