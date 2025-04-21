<!-- En dashboard.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand" href="/admin/dashboard"><i class="fas fa-home me-2"></i>Panel de Administración</a>
        <div class="ms-auto d-flex align-items-center">
            <span class="navbar-text text-white me-3">
                Bienvenido, <?= htmlspecialchars($_SESSION['admin']) ?>
            </span>
            <a href="/admin/logout" class="btn btn-outline-light btn-sm">Cerrar sesión</a>
        </div>
    </div>
</nav>

<div class="container py-5">
    <h2 class="fw-bold mb-4">Dashboard</h2>
    
    <div class="row">
        <!-- Carta de Asistencias Hoy -->
        <div class="col-md-4">
            <a href="/admin/asistencias" style="text-decoration: none;">
                <div class="card text-white bg-success shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-calendar-check me-2"></i>Asistencias Hoy</h5>
                        <p class="display-6"><?= $asistenciasHoy ?></p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Carta de Estadísticas Generales -->
        <div class="col-md-4">
            <div class="card text-white bg-primary shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-users me-2"></i>Estadísticas</h5>
                    <ul class="list-unstyled">
                        <li><strong>Empleados:</strong> <?= $empleados ?></li>
                        <li><strong>Usuarios:</strong> <?= $usuarios ?></li>
                        <li><strong>Asistencias del día:</strong> <?= $asistenciasHoy ?></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Carta de Usuarios Registrados -->
        <div class="col-md-4">
            <a href="/admin/usuarios" style="text-decoration: none;">
                <div class="card text-white bg-warning shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-user-circle me-2"></i>Usuarios Registrados</h5>
                        <p class="display-6"><?= $usuariosRegistrados ?></p>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Gráficos y más estadísticas aquí (opcional) -->

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
