<?php
// Eliminamos la lógica de negocio, ahora está en el controlador y modelo
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticación
if (!isset($_SESSION['admin'])) {
    header('Location: /admin/login');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TEAM TIME - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="/css/dashboard.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>TEAM TIME</h3>
        </div>
        
        <div class="sidebar-user">
            <p class="mb-1">Bienvenido, <strong><?= htmlspecialchars($_SESSION['admin']) ?></strong></p>
            <small class="text-muted">Administrador</small>
        </div>
        
        <div class="sidebar-menu">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link active" href="/admin/dashboard">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" data-bs-toggle="collapse" data-bs-target="#reportCollapse">
                        <i class="fas fa-file-alt"></i> Reportes <i class="fas fa-angle-down ms-auto"></i>
                    </a>
                    <div class="collapse" id="reportCollapse">
                        <ul class="nav flex-column ps-4">
                            <li class="nav-item">
                                <a class="nav-link" href="/admin/reportes/asistencias">Asistencias</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/admin/reportes/horas-extras">Horas Extras</a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/admin/crear_usuario">
                        <i class="fas fa-user-plus"></i> Crear Usuario
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/admin/registrar_empleado">
                        <i class="fas fa-user-edit"></i> Registrar Empleado
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/admin/logout">
                        <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Main content -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="mb-0">Dashboard de Asistencia</h1>
                <p class="text-muted mb-0">Resumen de la asistencia del personal</p>
            </div>
            <div>
                <span class="badge bg-light text-dark"><?= date('d M Y') ?></span>
            </div>
        </div>

        <!-- Tarjetas de indicadores -->
        <div class="row mb-4">
            <!-- Empleados activos -->
            <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
                <div class="card card-dashboard h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Empleados</h6>
                                <h3 class="mb-0"><?= htmlspecialchars($empleadosActivos ?? 0) ?></h3>
                                <small class="text-success">
                                    <?= ($totalEmpleados ?? 0) > 0 ? round((($empleadosActivos ?? 0)/($totalEmpleados ?? 1))*100).'% del total' : 'Sin datos' ?>
                                </small>
                            </div>
                            <div class="indicator-icon bg-primary bg-opacity-10 text-primary">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resto de las tarjetas (similar a las originales) -->
            <!-- ... -->
        </div>

        <!-- Gráficos y tablas -->
        <div class="row">
            <!-- Gráfico de asistencia mensual -->
            <div class="col-lg-8 mb-4">
                <div class="card card-dashboard h-100">
                    <div class="card-header bg-transparent border-bottom">
                        <h5 class="mb-0">Tendencia de Asistencia Mensual</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="asistenciaMensualChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Últimos registros -->
            <div class="col-lg-4 mb-4">
                <div class="card card-dashboard h-100">
                    <div class="card-header bg-transparent border-bottom">
                        <h5 class="mb-0">Últimos Registros</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php while($registro = $ultimosRegistros->fetch_assoc()): ?>
                            <div class="list-group-item border-0 py-3 px-4">
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar me-3 bg-primary">
                                        <?= substr($registro['nombres'], 0, 1) . substr($registro['apellidos'], 0, 1) ?>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0"><?= htmlspecialchars($registro['nombres']) ?> <?= htmlspecialchars($registro['apellidos']) ?></h6>
                                        <small class="text-muted">
                                            <?= date('d M', strtotime($registro['fecha'])) ?> · 
                                            <?= !empty($registro['hora_entrada']) ? date('H:i', strtotime($registro['hora_entrada'])) : '--:--' ?> - 
                                            <?= !empty($registro['hora_salida']) ? date('H:i', strtotime($registro['hora_salida'])) : '--:--' ?>
                                        </small>
                                    </div>
                                    <?php 
                                    $badgeType = [
                                        1 => ['bg-success', 'Puntual'],
                                        11 => ['bg-warning', 'Tardanza'],
                                        2 => ['bg-danger', 'Falta'],
                                        8 => ['bg-secondary', 'DM'],
                                        9 => ['bg-info', 'Licencia'],
                                        10 => ['bg-dark', 'Otros']
                                    ][$registro['id_tipo_registro']] ?? ['bg-light text-dark', 'Otro'];
                                    ?>
                                    <span class="badge-status <?= $badgeType[0] ?>"><?= $badgeType[1] ?></span>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rankings -->
        <div class="row">
            <!-- Top tardanzas -->
            <div class="col-lg-6 mb-4">
                <div class="card card-dashboard h-100">
                    <div class="card-header bg-transparent border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Top 5 Tardanzas</h5>
                            <span class="badge bg-warning">Este mes</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-middle table-hover">
                                <tbody>
                                    <?php while($tardanza = $topTardanzas->fetch_assoc()): ?>
                                    <tr>
                                        <td width="50">
                                            <div class="user-avatar bg-warning">
                                                <?= substr($tardanza['nombres'], 0, 1) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <h6 class="mb-0"><?= htmlspecialchars($tardanza['nombres']) ?> <?= htmlspecialchars($tardanza['apellidos']) ?></h6>
                                            <small class="text-muted">Departamento</small>
                                        </td>
                                        <td class="text-end">
                                            <span class="badge bg-warning rounded-pill px-3 py-1"><?= $tardanza['total'] ?> tardanzas</span>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top horas extras -->
            <div class="col-lg-6 mb-4">
                <div class="card card-dashboard h-100">
                    <div class="card-header bg-transparent border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Top 5 Horas Extras</h5>
                            <span class="badge bg-success">Este mes</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-middle table-hover">
                                <tbody>
                                    <?php while($extra = $topHorasExtras->fetch_assoc()): ?>
                                    <tr>
                                        <td width="50">
                                            <div class="user-avatar bg-success">
                                                <?= substr($extra['nombres'], 0, 1) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <h6 class="mb-0"><?= htmlspecialchars($extra['nombres']) ?> <?= htmlspecialchars($extra['apellidos']) ?></h6>
                                            <small class="text-muted">Departamento</small>
                                        </td>
                                        <td class="text-end">
                                            <span class="badge bg-success rounded-pill px-3 py-1"><?= $extra['total'] ?> horas</span>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // Gráfico de asistencia mensual
        const ctx = document.getElementById('asistenciaMensualChart').getContext('2d');
        const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        
        // Preparar datos para el gráfico
        const datosMensuales = Array(12).fill({ presentes: 0, faltas: 0, tardanzas: 0 });
        
        <?php foreach($asistenciaMensual as $mes): ?>
        datosMensuales[<?= $mes['mes']-1 ?>] = {
            presentes: <?= $mes['presentes'] ?>,
            faltas: <?= $mes['faltas'] ?>,
            tardanzas: <?= $mes['tardanzas'] ?>
        };
        <?php endforeach; ?>
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: meses,
                datasets: [
                    {
                        label: 'Presentes',
                        data: datosMensuales.map(m => m.presentes),
                        backgroundColor: 'rgba(40, 167, 69, 0.8)',
                        borderRadius: 4
                    },
                    {
                        label: 'Faltas',
                        data: datosMensuales.map(m => m.faltas),
                        backgroundColor: 'rgba(220, 53, 69, 0.8)',
                        borderRadius: 4
                    },
                    {
                        label: 'Tardanzas',
                        data: datosMensuales.map(m => m.tardanzas),
                        backgroundColor: 'rgba(255, 193, 7, 0.8)',
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    }
                },
                scales: {
                    x: {
                        stacked: false,
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        stacked: false,
                        beginAtZero: true,
                        ticks: {
                            stepSize: 20
                        }
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                }
            }
        });
    </script>
</body>
</html>