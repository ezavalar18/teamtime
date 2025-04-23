<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Empleado</title>
    <?php echo $stylesheets; ?>  <!-- Aquí incluimos los estilos -->
</head>
<body>

    <h1>Registrar Empleado</h1>
    
    <form action="" method="POST">
        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" required>
        <br>

        <label for="dni">DNI:</label>
        <input type="text" id="dni" name="dni" required>
        <br>

        <button type="submit">Registrar</button>
    </form>

</body>
</html>


