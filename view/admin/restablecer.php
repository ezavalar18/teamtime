    <!-- /view/admin/restablecer.php -->
    <!DOCTYPE html>
    <html lang="es">
    <head>
        
        <meta charset="UTF-8">
        <title>Restablecer Contraseña</title>
        <link rel="stylesheet" href="/css/styles.css">
        <!-- Incluir Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

        <script>
            // Validar que las contraseñas coincidan
            function validarContraseñas() {
                var nuevaContrasena = document.getElementById('nueva_contrasena').value;
                var confirmarContrasena = document.getElementById('confirmar_contrasena').value;
                var botonRestablecer = document.getElementById('boton-restablecer');
                var mensajeError = document.getElementById('mensaje-error');

                // Compara las contraseñas
                if (nuevaContrasena === confirmarContrasena) {
                    botonRestablecer.disabled = false; // Activa el botón
                    mensajeError.style.display = 'none'; // Oculta el mensaje de error
                } else {
                    botonRestablecer.disabled = true; // Desactiva el botón
                    mensajeError.style.display = 'block'; // Muestra el mensaje de error
                }
            }   
        </script>
    </head>
    <body>
        <div class="reset-container">
            <i class="fas fa-key"></i>

            <h1>Restablecer Contraseña</h1>

            <!-- Mostrar errores o mensajes de éxito -->
            <?php if (isset($data['error'])): ?>
                <div class="error"><?= htmlspecialchars($data['error']) ?></div>
            <?php elseif (isset($data['mensaje'])): ?>
                <div class="success"><?= htmlspecialchars($data['mensaje']) ?></div>
            <?php endif; ?>

            <form action="/admin/restablecer" method="POST">
                <input type="hidden" name="tokenr" value="<?= htmlspecialchars($tokenActual) ?>">

                <label for="nueva_contrasena">Nueva contraseña:</label>
                <input type="password" name="nueva_contrasena" id="nueva_contrasena" placeholder="Introduce tu nueva contraseña" required oninput="validarContraseñas()">

                <label for="confirmar_contrasena">Confirmar nueva contraseña:</label>
                <input type="password" name="confirmar_contrasena" id="confirmar_contrasena" placeholder="Confirma tu nueva contraseña" required oninput="validarContraseñas()">

                <!-- Mensaje de error si las contraseñas no coinciden -->
                <div id="mensaje-error" style="color: red; display: none; font-size: 14px; margin-bottom: 10px;">
                    Las contraseñas no coinciden.
                </div>

                <!-- Botón desactivado por defecto -->
                <button type="submit" id="boton-restablecer" disabled>Restablecer</button>
            </form>
        </div>
    </body>
    </html>


