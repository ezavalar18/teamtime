<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Marcación</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/marcacion.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> 
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>

<body class="d-flex justify-content-center align-items-center" style="min-height: 100vh;">

    <div class="container text-center">
        <h1 class="titulo">
            <img class="logo-reloj" src="/images/reloj1.jpg" alt="">TeamTime
        </h1>


        <!-- Fecha Actual -->
        <div class="fecha">
            <p id="fecha_lima"></p>
        </div>

        <!-- Hora Actual -->
        <div class="hora">
            <p id="hora_lima"></p> <!-- Aquí se mostrará la hora actual de Lima, Perú -->
        </div>

        <!-- Cuadro para ingresar token -->
        <div class="formulario">
            <label for="cod_usuario" class="visually-hidden">Código de empleado</label>
            <div class="input-wrapper">
                <i class="fa-solid fa-user-large"></i>
                <input type="text" id="cod_usuario" name="cod_usuario" pattern="\d{5}" maxlength="5" placeholder="Token de usuario" required>
            </div>
            <input type="hidden" id="hora_marcada" name="hora">
        </div>


        <?php if (isset($mensaje) && !empty($mensaje)): ?>
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    mostrarModal("<?= addslashes($mensaje) ?>", "<?= strpos($mensaje, 'correctamente') !== false ? 'success' : 'error' ?>");
                });
            </script>
        <?php endif; ?>

        <!-- Ingreso con teclado 'i' o 's' -->
        <div class="accion">
            <p>
                Introduce tu Token y luego presiona:<br>
                <strong>i</strong> para ingreso<br>
                <strong>s</strong> para salida
            </p>
        </div>
    

    <script>
        // Función para mostrar la hora y fecha actual de Lima, Perú
        function actualizarHora() {
            const opcionesHora = {
                timeZone: 'America/Lima',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false
            };

            const opcionesFecha = {
                timeZone: 'America/Lima',
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            };

            const ahora = new Date();
            const horaLima = ahora.toLocaleTimeString('es-PE', opcionesHora);
            const fechaLima = ahora.toLocaleDateString('es-PE', opcionesFecha);

            // Formato personalizado: Domingo 13 de abril del 2025
            const fechaFormateada = fechaLima.replace(' de ', ' de ').replace(',', '').replace(/(\d+) de (\w+) de (\d+)/, '$1 de $2 del $3');

            document.getElementById('hora_lima').innerText = horaLima;
            document.getElementById('fecha_lima').innerText = capitalizarPrimeraLetra(fechaFormateada);
        }

        function capitalizarPrimeraLetra(texto) {
            return texto.charAt(0).toUpperCase() + texto.slice(1);
        }

        // Actualizar la hora cada segundo
        setInterval(actualizarHora, 1000);

        // Para evitar la doble marcación, vamos a escuchar las teclas solo una vez cuando se ingrese el token completo
        let keyListenerActivo = false;

        // Inicializamos la fecha al cargar la página
        actualizarHora();

        document.getElementById('cod_usuario').addEventListener('input', function(event) {
    const token = event.target.value;
    if (token.length === 5) {
        const handleKey = async function(e) {
            if (e.key === 'i' || e.key === 's') {
                document.removeEventListener('keydown', handleKey); // Evitar múltiples envíos

                const tipo = e.key === 'i' ? 'entrada' : 'salida';
                const formData = new FormData();
                formData.append('cod_usuario', token);
                formData.append('tipo', tipo);

                try {
                    const response = await fetch('/registrar', {
                        method: 'POST',
                        body: formData
                    });

                    const html = await response.text();

                    // Extraer el mensaje desde la respuesta HTML si viene dentro de mostrarModal(...)
                    const match = html.match(/mostrarModal\("(.+?)",\s?"(success|error)"\)/);
                    if (match) {
                        const mensaje = match[1];
                        const tipo = match[2];
                        mostrarModal(mensaje, tipo);
                    } else {
                        mostrarModal("Marcación enviada, pero no se recibió respuesta clara.", "success");
                    }
                } catch (error) {
                    mostrarModal('Error de conexión con el servidor.', 'error');
                }

                // Limpiar campo y mantener foco
                event.target.value = '';
                event.target.focus();
            }
        };

        // Escuchar una sola vez después de completar 5 dígitos
        document.addEventListener('keydown', handleKey);
    }
});


        // Función para mostrar la ventana modal con el mensaje
        function mostrarModal(mensaje, tipo = 'success') {
            const modal = document.getElementById('modalMensaje');
            const contenido = document.getElementsByClassName('modal-contenido')[0];
            const texto = document.getElementById('mensajeTexto');

            // Eliminar clases previas de tipo success o error
            contenido.classList.remove('success', 'error');
            contenido.classList.add(tipo);

            // Insertar el mensaje dentro del modal
            texto.textContent = mensaje;

            // Mostrar la ventana modal
            modal.style.display = 'flex';

            // Ocultar modal después de 3 segundos
            setTimeout(() => {
                cerrarModal();
            }, 3000);
        }

        // Función para cerrar la ventana modal
        function cerrarModal() {
            const modal = document.getElementById('modalMensaje');
            modal.style.display = 'none';
        }

        // Asegura que el cursor esté siempre en el campo para ingresar el token
        document.addEventListener('DOMContentLoaded', function() {
            const inputToken = document.getElementById('cod_usuario');
            inputToken.focus();
        });

    </script>

    <!-- Modal (solo una vez) -->
    <div id="modalMensaje" class="modal">
        <div class="modal-contenido">
            <span class="cerrar" onclick="cerrarModal()">&times;</span>
            <p id="mensajeTexto"></p>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const inputToken = document.getElementById('cod_usuario');
        inputToken.focus();  // Asegura que el campo de entrada tenga el foco al cargar la página

        // Asegura que el foco se mantenga en el campo, incluso si se hace clic fuera de él
        document.body.addEventListener('click', () => {
            inputToken.focus();  // Regresa el foco al campo de entrada
        });
    });

    const inputToken = document.getElementById('cod_usuario');

    inputToken.addEventListener('input', function(event) {
        // Solo permitir números y máximo 5 dígitos
        event.target.value = event.target.value.replace(/[^0-9]/g, '').slice(0, 5);
    });
</script>
</body>
</html>


