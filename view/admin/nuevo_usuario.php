<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear nuevo usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2>Crear nuevo usuario admin</h2>
    <form method="POST" action="/admin/guardar_usuario" class="mt-4">
    <div class="mb-3">
            <label for="usuario" class="form-label">Nombre</label>
            <input type="text" class="form-control" id="nombre" name="nombre" required>
        </div>
        <div class="mb-3">
            <label for="usuario" class="form-label">usuario</label>
            <input type="text" class="form-control" id="usuario" name="usuario" required>
        </div>
        <div class="mb-3">
            <label for="contrasena" class="form-label">Contraseña</label>
            <input type="password" class="form-control" id="contrasena" name="contrasena" required>
        </div>
        <div class="mb-3">
            <label for="correo" class="form-label">correo</label>
            <input type="text" class="form-control" id="correo" name="correo" required>
        </div>
        <!-- Campo para seleccionar rol -->
        <div class="mb-3">
            <label for="rol" class="form-label">Rol</label>
            <select class="form-select" id="rol" name="rol" required>
                <option value="admin">Administrador</option>
                <option value="editor">Editor</option>
                <option value="viewer">Espectador</option>
            </select>
        </div>


        <button type="submit" class="btn btn-primary">Guardar</button>
        <a href="/admin/dashboard" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

</body>
</html>

