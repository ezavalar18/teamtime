<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Administrador</title>
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body>
    <div class="login-container">
        <h1>Ingreso al Sistema de Administración</h1>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?= $error ?></div>
        <?php endif; ?>

        <form action="/admin/autenticar" method="POST">
            <div class="form-group">
                <label for="usuario">Usuario</label>
                <input type="text" id="usuario" name="usuario" required placeholder="Ingrese su usuario">
            </div>

            <div class="form-group">
                <label for="contrasena">Contraseña</label>
                <input type="password" id="contrasena" name="contrasena" required placeholder="Ingrese su contraseña">
            </div>

            <button type="submit" class="btn-login">Ingresar</button>
        </form>
    </div>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-container {
            background-color: #fff;
            padding: 2rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            width: 300px;
        }

        h1 {
            text-align: center;
            margin-bottom: 1rem;
        }

        .form-group {
            margin-bottom: 1

