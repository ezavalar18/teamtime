<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Usuarios Registrados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand" href="/admin/dashboard"><i class="fas fa-arrow-left me-2"></i>Volver al Panel</a>
        <div class="ms-auto d-flex align-items-center">
            <span class="navbar-text text-white me-3">
                Bienvenido, <?= htmlspecialchars($_SESSION['admin']) ?>
            </span>
            <a href="/admin/logout" class="btn btn-outline-light btn-sm">Cerrar sesión</a>
        </div>
    </div>
</nav>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Usuarios Registrados</h2>
        <a href="/admin/crear_usuario" class="btn btn-primary">
            <i class="fas fa-user-plus me-2"></i>Nuevo usuario
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Nombre</th>
                        <th>Usuario</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <div class="mb-3">
                         <input type="text" class="form-control" id="buscarUsuario" placeholder="Buscar por nombre, usuario, correo o rol...">
                 </div>
                    <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                            <td><?= htmlspecialchars($usuario['usuario']) ?></td>
                            <td><?= htmlspecialchars($usuario['correo']) ?></td>
                            <td><?= htmlspecialchars($usuario['rol']) ?></td>
                            <td>
                                <button class="btn btn-sm btn-warning me-2"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editarUsuarioModal<?= $usuario['id'] ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="/admin/eliminar_usuario/<?= $usuario['id'] ?>" class="btn btn-danger btn-sm"
                                   onclick="return confirm('¿Estás seguro de eliminar este usuario?');">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </td>
                        </tr>
                        
                          <!-- Modal de edición -->
                           <div class="modal fade" id="editarUsuarioModal<?= $usuario['id'] ?>" tabindex="-1" aria-labelledby="editarUsuarioLabel<?= $usuario['id'] ?>" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <form action="/admin/actualizar_usuario" method="POST">
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title" id="editarUsuarioLabel<?= $usuario['id'] ?>">Editar Usuario</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="id" value="<?= $usuario['id'] ?>">
                                            <div class="mb-3">
                                                <label class="form-label">Nombre</label>
                                                <input type="text" class="form-control" name="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Usuario</label>
                                                <input type="text" class="form-control" name="usuario" value="<?= htmlspecialchars($usuario['usuario']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Correo</label>
                                                <input type="email" class="form-control" name="correo" value="<?= htmlspecialchars($usuario['correo']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Rol</label>
                                                <select name="rol" class="form-select" required>
                                                    <option value="admin" <?= $usuario['rol'] === 'admin' ? 'selected' : '' ?>>Administrador</option>
                                                    <option value="editor" <?= $usuario['rol'] === 'editor' ? 'selected' : '' ?>>Editor</option>
                                                    <option value="viewer" <?= $usuario['rol'] === 'viewer' ? 'selected' : '' ?>>Viewer</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-success">Guardar cambios</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.getElementById('buscarUsuario').addEventListener('keyup', function () {
    let valor = this.value.toLowerCase();
    document.querySelectorAll('tbody tr').forEach(function (fila) {
        let texto = fila.textContent.toLowerCase();
        fila.style.display = texto.includes(valor) ? '' : 'none';
    });
});
</script>

</body>
</html>
