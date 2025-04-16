<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Contraseña</title>
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body>
    <div class="login-container">
        <h1>Recuperar Contraseña</h1>

        <?php if (isset($data['mensaje'])): ?>
            <div class="success-message"><?= $data['mensaje'] ?></div>
        <?php elseif (isset($data['error'])): ?>
            <div class="error-message"><?= $data['error'] ?></div>
        <?php endif; ?>

        <form action="/admin/recuperar" method="POST">
            <div class="form-group">
                <label for="correo">Correo electrónico</label>
                <input type="email" id="correo" name="correo" required placeholder="Ingrese su correo">
            </div>

            <button type="submit" class="btn-login">Enviar enlace</button>
        </form>
    </div>
</body>
</html>

