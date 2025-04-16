<!-- /view/admin/restablecer.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Restablecer Contraseña</title>
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body>
    <div class="reset-container">
        <h1>Restablecer Contraseña</h1>

        <form action="/admin/restablecer" method="POST">
            <div class="form-group">
                <label for="nueva_contrasena">Nueva Contraseña</label>
                <input type="password" id="nueva_contrasena" name="nueva_contrasena" required placeholder="Ingrese nueva contraseña">
            </div>

            <button type="submit" class="btn-login">Restablecer Contraseña</button>
        </form>
    </div>
</body>
</html>
