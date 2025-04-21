<!-- view/admin/editar_usuario.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">

<h2 class="mb-4">Editar Usuario</h2>

<form method="POST" action="/admin/actualizar_usuario">
    <input type="hidden" name="id" value="<?= $usuario['id'] ?>">

    <div class="mb-3">
        <label for="nombre" class="form-label">Nombre</label>
        <input type="text" class="form-control" name="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>">
    </div>

    <div class="mb-3">
        <label for="usuario" class="form-label">Usuario</label>
        <input type="text" class="form-control" name="usuario" value="<?= htmlspecialchars($usuario['usuario']) ?>" required>
    </div>

    <div class="mb-3">
        <label for="correo" class="form-label">Correo</label>
        <input type="email" class="form-control" name="correo" value="<?= htmlspecialchars($usuario['correo']) ?>">
    </div>

    <div class="mb-3">
        <label for="rol" class="form-label">Rol</label>
        <select class="form-select" name="rol" required>
            <option value="admin" <?= $usuario['rol'] == 'admin' ? 'selected' : '' ?>>Admin</option>
            <option value="editor" <?= $usuario['rol'] == 'editor' ? 'selected' : '' ?>>Editor</option>
            <option value="viewer" <?= $usuario['rol'] == 'viewer' ? 'selected' : '' ?>>Viewer</option>
        </select>
    </div>

    <button type="submit" class="btn btn-success">Actualizar</button>
    <a href="/admin/dashboard" class="btn btn-secondary">Cancelar</a>
</form>

</body>
</html>
