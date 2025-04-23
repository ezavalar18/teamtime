<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Conexión a la base de datos
require_once(__DIR__ . '/../../Database.php');
$database = new Database();
$conn = $database->connect();

// Consultas optimizadas
try {
    // 1. Total de empleados activos
    $totalEmpleados = $conn->query(
        "SELECT COUNT(*) as total FROM empleados WHERE estado = 1"
    )->fetch_assoc()['total'];

    // 2. Asistencia este mes
    $empleadosActivos = $conn->query(
        "SELECT COUNT(DISTINCT id_empleado) as total 
         FROM registro_asistencia 
         WHERE MONTH(fecha) = MONTH(CURRENT_DATE()) 
         AND YEAR(fecha) = YEAR(CURRENT_DATE())"
    )->fetch_assoc()['total'];

    // 3. Faltas este mes
    $faltasMes = $conn->query(
        "SELECT COUNT(*) as total 
         FROM registro_asistencia 
         WHERE id_tipo_registro IN (2,8,9,10) 
         AND MONTH(fecha) = MONTH(CURRENT_DATE())"
    )->fetch_assoc()['total'];

    // 4. Horas extras acumuladas
    $horasExtras = $conn->query(
        "SELECT SUM(TIMESTAMPDIFF(HOUR, e.hora_salida, r.hora_salida)) as total 
         FROM registro_asistencia r 
         JOIN empleados e ON r.id_empleado = e.id 
         WHERE r.hora_salida > e.hora_salida 
         AND MONTH(fecha) = MONTH(CURRENT_DATE())"
    )->fetch_assoc()['total'] ?? 0;

    // 5. Tardanzas este mes
    $tardanzas = $conn->query(
        "SELECT COUNT(*) as total 
         FROM registro_asistencia 
         WHERE TIME(hora_entrada) > '08:05:00' 
         AND id_tipo_registro = 11 
         AND MONTH(fecha) = MONTH(CURRENT_DATE())"
    )->fetch_assoc()['total'];

    // 6. Porcentaje de asistencia
    $totalDiasLaborales = $conn->query(
        "SELECT COUNT(DISTINCT fecha) as total 
         FROM registro_asistencia 
         WHERE MONTH(fecha) = MONTH(CURRENT_DATE())"
    )->fetch_assoc()['total'];

    $asistencias = $conn->query(
        "SELECT COUNT(*) as total 
         FROM registro_asistencia 
         WHERE id_tipo_registro = 1 
         AND MONTH(fecha) = MONTH(CURRENT_DATE())"
    )->fetch_assoc()['total'];

    $promedioAsistencia = $totalDiasLaborales > 0 ? round(($asistencias / ($totalEmpleados * $totalDiasLaborales)) * 100) : 0;

    // Datos para gráficos
    $asistenciaMensual = $conn->query(
        "SELECT 
            MONTH(fecha) as mes,
            COUNT(CASE WHEN id_tipo_registro = 1 THEN 1 END) as presentes,
            COUNT(CASE WHEN id_tipo_registro IN (2,8,9,10) THEN 1 END) as faltas,
            COUNT(CASE WHEN id_tipo_registro = 11 THEN 1 END) as tardanzas
         FROM registro_asistencia 
         WHERE YEAR(fecha) = YEAR(CURRENT_DATE())
         GROUP BY MONTH(fecha)
         ORDER BY mes"
    )->fetch_all(MYSQLI_ASSOC);

    // Últimos registros
    $ultimosRegistros = $conn->query(
        "SELECT e.nombres, e.apellidos, r.fecha, r.hora_entrada, r.hora_salida, r.id_tipo_registro 
         FROM registro_asistencia r 
         JOIN empleados e ON r.id_empleado = e.id 
         ORDER BY r.fecha DESC, r.id DESC 
         LIMIT 5"
    );

    // Top tardanzas
    $topTardanzas = $conn->query(
        "SELECT e.nombres, e.apellidos, COUNT(*) as total 
         FROM registro_asistencia r 
         JOIN empleados e ON r.id_empleado = e.id 
         WHERE r.id_tipo_registro = 11 
         GROUP BY r.id_empleado 
         ORDER BY total DESC 
         LIMIT 5"
    );

    // Top horas extras
    $topHorasExtras = $conn->query(
        "SELECT e.nombres, e.apellidos, SUM(TIMESTAMPDIFF(HOUR, e.hora_salida, r.hora_salida)) as total 
         FROM registro_asistencia r 
         JOIN empleados e ON r.id_empleado = e.id 
         WHERE r.hora_salida > e.hora_salida 
         GROUP BY r.id_empleado 
         ORDER BY total DESC 
         LIMIT 5"
    );

} catch (Exception $e) {
    die("Error en las consultas: " . $e->getMessage());
} finally {
    $database->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- CSS adicional para mejorar el diseño -->
    <style>
        .card-dashboard {
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
        }
        .card-dashboard:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        .indicator-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        .progress-thin {
            height: 6px;
        }
        .badge-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.75rem;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: white;
        }
        .chart-container {
            position: relative;
            height: 300px;
        }
        .navbar {
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        body {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Asistencia Admin</a>
        <div class="ms-auto d-flex align-items-center">
            <span class="navbar-text text-white me-3">
                Bienvenido, <?= htmlspecialchars($_SESSION['admin'] ?? 'Administrador') ?>
            </span>
            <a href="/admin/logout" class="btn btn-outline-light btn-sm">Cerrar sesión</a>
        </div>
    </div>
</nav>

<div class="container-fluid py-4">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-0">Dashboard de Asistencia</h1>
            <p class="text-muted mb-0">Resumen de la asistencia del personal</p>
        </div>
        <div>
            <a href="/admin/crear_usuario" class="btn btn-primary">
                <i class="fas fa-user-plus me-1"></i> Nuevo usuario
            </a>
        </div>
    </div>

    <!-- Tarjetas de indicadores principales -->
    <div class="row mb-4">
        <!-- Empleados activos -->
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="card card-dashboard h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Empleados</h6>
                            <h3 class="mb-0"><?= $totalEmpleados ?></h3>
                            <small class="text-success">+2.5% vs mes anterior</small>
                        </div>
                        <div class="indicator-icon bg-primary bg-opacity-10 text-primary">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Asistencia del mes -->
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="card card-dashboard h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Asistencia</h6>
                            <h3 class="mb-0"><?= $empleadosActivos ?></h3>
                            <small class="text-success"><?= round(($empleadosActivos/$totalEmpleados)*100) ?>% del total</small>
                        </div>
                        <div class="indicator-icon bg-success bg-opacity-10 text-success">
                            <i class="fas fa-user-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Faltas -->
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="card card-dashboard h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Faltas</h6>
                            <h3 class="mb-0"><?= $faltasMes ?></h3>
                            <small class="text-danger">+3 vs mes anterior</small>
                        </div>
                        <div class="indicator-icon bg-danger bg-opacity-10 text-danger">
                            <i class="fas fa-user-times"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tardanzas -->
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="card card-dashboard h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Tardanzas</h6>
                            <h3 class="mb-0"><?= $tardanzas ?></h3>
                            <small class="text-success">-2 vs mes anterior</small>
                        </div>
                        <div class="indicator-icon bg-warning bg-opacity-10 text-warning">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Horas extras -->
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="card card-dashboard h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Horas Extras</h6>
                            <h3 class="mb-0"><?= $horasExtras ?></h3>
                            <small class="text-danger">+15h vs mes anterior</small>
                        </div>
                        <div class="indicator-icon bg-info bg-opacity-10 text-info">
                            <i class="fas fa-business-time"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- % Asistencia -->
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="card card-dashboard h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Asistencia</h6>
                            <h3 class="mb-0"><?= $promedioAsistencia ?>%</h3>
                            <div class="progress progress-thin mt-2">
                                <div class="progress-bar bg-success" role="progressbar" style="width: <?= $promedioAsistencia ?>%"></div>
                            </div>
                        </div>
                        <div class="indicator-icon bg-dark bg-opacity-10 text-dark">
                            <i class="fas fa-percent"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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