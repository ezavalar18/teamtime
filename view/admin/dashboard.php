<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet"> <!-- Para los iconos -->
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <!-- Título a la izquierda -->
        <a class="navbar-brand" href="#">Asistencia Admin</a>

        <!-- Bienvenida y logout alineados a la derecha -->
        <div class="ms-auto d-flex align-items-center">
            <span class="navbar-text text-white me-3">
                Bienvenido, <?= htmlspecialchars($_SESSION['admin']) ?>
            </span>
            <a href="/admin/logout" class="btn btn-outline-light btn-sm">Cerrar sesión</a>
        </div>
    </div>
</nav>


<div class="container mt-5">
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Dashboard</h1>
    <a href="/admin/crear_usuario" class="btn btn-primary">
        <i class="fas fa-user-plus me-1"></i> Nuevo usuario
    </a>
</div>


    <div class="row g-4">
        <!-- Card de usuarios -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-3x text-primary"></i>
                    <h5 class="card-title mt-3">Usuarios registrados</h5>
                    <p class="card-text">100</p> <!-- Puedes cambiarlo por el número real desde la base de datos -->
                </div>
            </div>
        </div>

        <!-- Card de asistencias -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-3x text-success"></i>
                    <h5 class="card-title mt-3">Asistencias del día</h5>
                    <p class="card-text">80</p> <!-- Aquí también pondrás el dato real -->
                </div>
            </div>
        </div>

        <!-- Card de reportes -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center">
                    <i class="fas fa-file-alt fa-3x text-warning"></i>
                    <h5 class="card-title mt-3">Reportes generados</h5>
                    <p class="card-text">25</p> <!-- Otro dato real -->
                </div>
            </div>
        </div>
    </div>

    <!-- Barra de progreso -->
    <div class="mt-4">
        <h4>Progreso de asistencia del día</h4>
        <div class="progress" style="height: 30px;">
            <div class="progress-bar" role="progressbar" style="width: 80%" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>

</body>
</html>

