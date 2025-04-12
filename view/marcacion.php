<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sistema de Marcación</title>
    <?= $stylesheets ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div class="container">
        <h1>Sistema de Marcación</h1>

        <div class="time-display">
            <div class="time" id="horaActual"><?= $hora ?></div>
            <div class="date" id="fechaActual"><?= $fecha ?></div>
            <div class="timezone">Hora de Lima, Perú</div>
        </div>

        <!-- Input para el código del trabajador -->
        <input type="text" id="codigoTrabajador" name="cod_usuario" maxlength="5" pattern="\d{5}" placeholder="Ingrese código del trabajador" autofocus>

        <div class="button-group">
            <button class="btn">Presione tecla 'i' para Ingreso</button>
            <button class="btn">Presione tecla 's' para Salida</button>
        </div>
    </div>

    <script>
        // Actualizar hora y fecha cada segundo
        setInterval(() => {
            const now = new Date();
            document.getElementById('horaActual').innerText = now.toLocaleTimeString('es-PE', { hour12: false });
        }, 1000);

        // Función que envía la solicitud POST al backend para registrar la marcación
        function enviarMarcacion(tipo) {
            const codigo = document.getElementById('codigoTrabajador').value;

            // Validar que el código sea un número de 5 dígitos
            if (!/^\d{5}$/.test(codigo)) {
                alert("El código ingresado no es válido.");
                return;
            }

            // Crear el formulario para enviar los datos por POST
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/marcacion'; // La acción que maneja la marcación

            // Crear los campos ocultos para enviar los datos
            const inputCod = document.createElement('input');
            inputCod.type = 'hidden';
            inputCod.name = 'cod_usuario';
            inputCod.value = codigo;

            const inputTipo = document.createElement('input');
            inputTipo.type = 'hidden';
            inputTipo.name = 'tipo';
            inputTipo.value = tipo;

            // Agregar los campos al formulario y enviarlo
            form.appendChild(inputCod);
            form.appendChild(inputTipo);
            document.body.appendChild(form);
            form.submit(); // Enviar el formulario
        }

        // Captura de teclas para entrada (i) o salida (s)
        document.addEventListener('keydown', function(event) {
            const codigo = document.getElementById('codigoTrabajador').value;

            // Asegúrate de que el código sea de 5 dígitos antes de enviar la solicitud
            if (/^\d{5}$/.test(codigo)) {
                if (event.key === 'i') {
                    // Enviar la marcación de entrada
                    enviarMarcacion('entrada');
                } else if (event.key === 's') {
                    // Enviar la marcación de salida
                    enviarMarcacion('salida');
                }
            }
        });

        // Asegura que el campo de código esté siempre con el foco
        const input = document.getElementById('codigoTrabajador');
        setInterval(() => {
            if (document.activeElement !== input) {
                input.focus();
            }
        }, 500);

        // Bloquea todo lo que no sea número en el campo de entrada
        input.addEventListener('input', () => {
            input.value = input.value.replace(/\D/g, '').slice(0, 5); // solo números, máx 5
        });
    </script>
</body>
</html>



