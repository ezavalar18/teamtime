<!-- En asistencias.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asistencias Hoy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand" href="/admin/dashboard"><i class="fas fa-arrow-left me-2"></i>Volver al Panel</a>
        <div class="ms-auto d-flex align-items-center">
            <span class="navbar-text text-white me-3">
            Bienvenido, <?= htmlspecialchars($_SESSION['admin'] ?? 'Administrador') ?>
            </span>
            <a href="/admin/logout" class="btn btn-outline-light btn-sm">Cerrar sesión</a>
        </div>
    </div>
</nav>

<div class="container py-5">
    <h2 class="fw-bold">Asistencias Registradas Hoy</h2>
    
    <table class="table table-striped">
    <thead>
        <tr>
            <th>ID Empleado</th>
            <th>Nombre del Empleado</th>
            <th>Fecha</th>
            <th>Hora de Entrada</th>
            <th>Hora de Salida</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($asistencias as $asistencia): ?>
            <tr>
                <td><?= htmlspecialchars($asistencia['id_empleado']) ?></td>
                <td><?= htmlspecialchars($asistencia['nombres']) ?></td> <!-- Mostrar nombre del empleado -->
                <td><?= htmlspecialchars($asistencia['fecha']) ?></td>
                <td><?= htmlspecialchars($asistencia['hora_entrada'] ?? '') ?></td>
                <td><?= htmlspecialchars($asistencia['hora_salida'] ?? '') ?></td>
                <td>
                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editarAsistenciaModal<?= $asistencia['id'] ?>">
                <i class="fas fa-edit"></i>
                </button>
                <!-- Modal para editar asistencia -->
<div class="modal fade" id="editarAsistenciaModal<?= $asistencia['id'] ?>" tabindex="-1" aria-labelledby="editarAsistenciaLabel<?= $asistencia['id'] ?>" aria-hidden="true">
    <div class="modal-dialog">
        <form action="/admin/actualizar_asistencia" method="POST">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editarAsistenciaLabel<?= $asistencia['id'] ?>">Editar Asistencia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" value="<?= $asistencia['id'] ?>">
                    <div class="mb-3">
                        <label for="fecha" class="form-label">Fecha</label>
                        <input type="date" name="fecha" class="form-control" value="<?= $asistencia['fecha'] ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="hora_entrada" class="form-label">Hora de Entrada</label>
                        <input type="time" name="hora_entrada" class="form-control" value="<?= $asistencia['hora_entrada'] ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="hora_salida" class="form-label">Hora de Salida</label>
                        <input type="time" name="hora_salida" class="form-control" value="<?= $asistencia['hora_salida'] ?>" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Guardar Cambios</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </form>
    </div>
</div>

</td>

            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
