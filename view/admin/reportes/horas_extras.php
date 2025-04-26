<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin'])) {
    header('Location: /admin/login');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Horas Extras</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="/css/horas_extras.css">
</head>
<body>
<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Reporte de Horas Extras</h1>
        <a href="?export=excel" class="btn btn-success">
            <i class="fas fa-file-excel me-1"></i> Exportar a Excel
        </a>
    </div>

    <div class="report-filter mb-4">
        <form method="POST">
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Buscar por:</label>
                        <select class="form-select" name="search_type">
                            <option value="nombre">Nombre</option>
                            <option value="apellido">Apellido</option>
                            <option value="codigo_empleado">Código Empleado</option>
                            <option value="dni">DNI</option>
                            <option value="token">Token</option>
                            <option value="area">Área</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Término de búsqueda:</label>
                        <input type="text" class="form-control" name="search_term" placeholder="Ingrese término..." value="<?= $_POST['search_term'] ?? '' ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="mb-3">
                        <label class="form-label">Fecha Inicio:</label>
                        <input type="date" class="form-control" name="fecha_inicio" value="<?= $_POST['fecha_inicio'] ?? '' ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="mb-3">
                        <label class="form-label">Fecha Fin:</label>
                        <input type="date" class="form-control" name="fecha_fin" value="<?= $_POST['fecha_fin'] ?? '' ?>">
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Filtrar</button>
        </form>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Área</th>
                            <th>DNI</th>
                            <th>Horas Extras</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($data as $index => $row): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($row['codigo_empleado'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($row['nombre'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($row['apellido'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($row['area'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($row['dni'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($row['horas_extras'] ?? '0') ?></td>
                            <td><?= htmlspecialchars($row['fecha'] ?? '-') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>