<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Usuarios</title>
    <style>
        /* Estilos igual que tu código original */
        /* ... (todo tu CSS aquí sin cambios) ... */
    </style>
</head>
<body>

<div class="container">
    <h1>Buscar Usuario</h1>
    
    <form method="post">
        <input type="text" name="criterio_busqueda" placeholder="Ingrese código, DNI o nombre..." required>
        <button type="submit" name="buscar">Buscar</button>
    </form>

    <?php if (!empty($mensaje)): ?>
        <div class="message <?php echo $tipo_mensaje; ?>">
            <?php echo $mensaje; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($usuarios)): ?>
        <table>
            <thead>
                <tr>
                    <th>Código Usuario</th>
                    <th>Nombre Completo</th>
                    <th>DNI</th>
                    <th>Hora Ingreso</th>
                    <th>Hora Salida</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><?= $usuario['cod_usuario']; ?></td>
                        <td><?= $usuario['vendedor']; ?></td>
                        <td><?= $usuario['dni']; ?></td>
                        <td><?= $usuario['hora_entrada'] ?? 'No registrada'; ?></td>
                        <td><?= $usuario['hora_salida'] ?? 'No registrada'; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Botones flotantes -->
<button class="btn-circular" onclick="window.location.href='marcacion';">
    ir a Marcación
</button>
<button class="btn-circular1" onclick="window.location.href='registrar_empleado';">
    ir a Registrar Empleado
</button>

</body>
</html>
