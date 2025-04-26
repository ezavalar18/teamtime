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
    <title>Reporte de Asistencias</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="/css/asistencias.css">
</head>
<body>
<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Reporte de Asistencias</h1>
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
                            <option value="nombre" <?= isset($_POST['search_type']) && $_POST['search_type'] == 'nombre' ? 'selected' : '' ?>>Nombre</option>
                            <option value="apellido" <?= isset($_POST['search_type']) && $_POST['search_type'] == 'apellido' ? 'selected' : '' ?>>Apellido</option>
                            <option value="codigo_empleado" <?= isset($_POST['search_type']) && $_POST['search_type'] == 'codigo_empleado' ? 'selected' : '' ?>>Código Empleado</option>
                            <option value="dni" <?= isset($_POST['search_type']) && $_POST['search_type'] == 'dni' ? 'selected' : '' ?>>DNI</option>
                            <option value="token" <?= isset($_POST['search_type']) && $_POST['search_type'] == 'token' ? 'selected' : '' ?>>Token</option>
                            <option value="area" <?= isset($_POST['search_type']) && $_POST['search_type'] == 'area' ? 'selected' : '' ?>>Área</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Término de búsqueda:</label>
                        <input type="text" class="form-control" name="search_term" placeholder="Ingrese término..." value="<?= htmlspecialchars($_POST['search_term'] ?? '') ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="mb-3">
                        <label class="form-label">Fecha Inicio:</label>
                        <input type="date" class="form-control" name="fecha_inicio" value="<?= htmlspecialchars($_POST['fecha_inicio'] ?? '') ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="mb-3">
                        <label class="form-label">Fecha Fin:</label>
                        <input type="date" class="form-control" name="fecha_fin" value="<?= htmlspecialchars($_POST['fecha_fin'] ?? '') ?>">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                    <a href="/admin/reportes/asistencias" class="btn btn-secondary">Limpiar filtros</a>
                </div>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-body">
            <?php if (empty($data)): ?>
                <div class="alert alert-info">No se encontraron registros con los filtros aplicados.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Apellido</th>
                                <th>Área</th>
                                <th>DNI</th>
                                <th>Token</th>
                                <th>Fecha</th>
                                <th>Hora Entrada</th>
                                <th>Hora Salida</th>
                                <th>Tipo Registro</th>
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
                                <td><?= htmlspecialchars($row['token'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['fecha'] ?? '-') ?></td>
                                <td><?= !empty($row['hora_entrada']) ? date('H:i', strtotime($row['hora_entrada'])) : '-' ?></td>
                                <td><?= !empty($row['hora_salida']) ? date('H:i', strtotime($row['hora_salida'])) : '-' ?></td>
                                <td>
                                    <?php 
                                    $badgeClass = [
                                        'Puntual' => 'bg-success',
                                        'Tardanza' => 'bg-warning',
                                        'Falta' => 'bg-danger',
                                        'DM' => 'bg-secondary',
                                        'Licencia' => 'bg-info',
                                        'Otros' => 'bg-dark'
                                    ][$row['tipo_registro']] ?? 'bg-light text-dark';
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($row['tipo_registro'] ?? '-') ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Paginación (opcional) -->
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center mt-4">
                        <li class="page-item disabled">
                            <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Anterior</a>
                        </li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item">
                            <a class="page-link" href="#">Siguiente</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Script para mejorar la experiencia de usuario -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Establecer fechas por defecto (últimos 30 días)
    const fechaFin = document.querySelector('input[name="fecha_fin"]');
    const fechaInicio = document.querySelector('input[name="fecha_inicio"]');
    
    if (!fechaFin.value && !fechaInicio.value) {
        const today = new Date();
        const lastMonth = new Date();
        lastMonth.setDate(lastMonth.getDate() - 30);
        
        fechaFin.valueAsDate = today;
        fechaInicio.valueAsDate = lastMonth;
    }
    
    // Confirmación antes de exportar
    const exportBtn = document.querySelector('a[href*="export=excel"]');
    if (exportBtn) {
        exportBtn.addEventListener('click', function(e) {
            if (<?= empty($data) ? 'true' : 'false' ?>) {
                e.preventDefault();
                alert('No hay datos para exportar. Aplique filtros diferentes.');
            }
        });
    }
});
</script>