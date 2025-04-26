<?php
// Eliminamos session_start() ya que ya se inició en AdminController.php
// Verificamos si la sesión está activa antes de acceder a $_SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verificar autenticación
if (!isset($_SESSION['admin'])) {
    header('Location: /admin/login');
    exit;
}

// Conexión a la base de datos
require_once(__DIR__ . '/../../Database.php');
$database = new Database();
$conn = $database->connect();

// Inicializamos todas las variables con valores por defecto
$totalEmpleados = 0;
$empleadosActivos = 0;
$faltasMes = 0;
$horasExtras = 0;
$tardanzas = 0;
$promedioAsistencia = 0;
$asistenciaMensual = [];
$ultimosRegistros = [];
$topTardanzas = [];
$topHorasExtras = [];

// Consultas optimizadas con manejo de errores
try {
    // 1. Total de empleados activos
    $result = $conn->query("SELECT COUNT(*) as total FROM empleados WHERE estado = 1");
    if ($result) {
        $totalEmpleados = $result->fetch_assoc()['total'] ?? 0;
    }

    // 2. Asistencia este mes
    $result = $conn->query(
        "SELECT COUNT(DISTINCT id_empleado) as total 
         FROM registro_asistencia 
         WHERE MONTH(fecha) = MONTH(CURRENT_DATE()) 
         AND YEAR(fecha) = YEAR(CURRENT_DATE())"
    );
    if ($result) {
        $empleadosActivos = $result->fetch_assoc()['total'] ?? 0;
    }

    // 3. Faltas este mes
    $result = $conn->query(
        "SELECT COUNT(*) as total 
         FROM registro_asistencia 
         WHERE id_tipo_registro IN (2,8,9,10) 
         AND MONTH(fecha) = MONTH(CURRENT_DATE())"
    );
    if ($result) {
        $faltasMes = $result->fetch_assoc()['total'] ?? 0;
    }

    // 4. Horas extras acumuladas
    $result = $conn->query(
        "SELECT SUM(TIMESTAMPDIFF(HOUR, e.hora_salida, r.hora_salida)) as total 
         FROM registro_asistencia r 
         JOIN empleados e ON r.id_empleado = e.id 
         WHERE r.hora_salida > e.hora_salida 
         AND MONTH(fecha) = MONTH(CURRENT_DATE())"
    );
    if ($result) {
        $horasExtras = $result->fetch_assoc()['total'] ?? 0;
    }

    // 5. Tardanzas este mes
    $result = $conn->query(
        "SELECT COUNT(*) as total 
         FROM registro_asistencia 
         WHERE TIME(hora_entrada) > '08:05:00' 
         AND id_tipo_registro = 11 
         AND MONTH(fecha) = MONTH(CURRENT_DATE())"
    );
    if ($result) {
        $tardanzas = $result->fetch_assoc()['total'] ?? 0;
    }

    // 6. Porcentaje de asistencia
    $result = $conn->query(
        "SELECT COUNT(DISTINCT fecha) as total 
         FROM registro_asistencia 
         WHERE MONTH(fecha) = MONTH(CURRENT_DATE())"
    );
    $totalDiasLaborales = $result ? $result->fetch_assoc()['total'] ?? 0 : 0;

    $result = $conn->query(
        "SELECT COUNT(*) as total 
         FROM registro_asistencia 
         WHERE id_tipo_registro = 1 
         AND MONTH(fecha) = MONTH(CURRENT_DATE())"
    );
    $asistencias = $result ? $result->fetch_assoc()['total'] ?? 0 : 0;

    // Evitamos división por cero
    if ($totalDiasLaborales > 0 && $totalEmpleados > 0) {
        $promedioAsistencia = round(($asistencias / ($totalEmpleados * $totalDiasLaborales)) * 100);
    } else {
        $promedioAsistencia = 0;
    }

    // Datos para gráficos
    $result = $conn->query(
        "SELECT 
            MONTH(fecha) as mes,
            COUNT(CASE WHEN id_tipo_registro = 1 THEN 1 END) as presentes,
            COUNT(CASE WHEN id_tipo_registro IN (2,8,9,10) THEN 1 END) as faltas,
            COUNT(CASE WHEN id_tipo_registro = 11 THEN 1 END) as tardanzas
         FROM registro_asistencia 
         WHERE YEAR(fecha) = YEAR(CURRENT_DATE())
         GROUP BY MONTH(fecha)
         ORDER BY mes"
    );
    if ($result) {
        $asistenciaMensual = $result->fetch_all(MYSQLI_ASSOC);
    }

    // Últimos registros
    $ultimosRegistros = $conn->query(
        "SELECT e.nombres, e.apellidos, r.fecha, r.hora_entrada, r.hora_salida, r.id_tipo_registro 
         FROM registro_asistencia r 
         JOIN empleados e ON r.id_empleado = e.id 
         ORDER BY r.fecha DESC, r.id DESC 
         LIMIT 5"
    ) ?: [];

    // Top tardanzas
    $topTardanzas = $conn->query(
        "SELECT e.nombres, e.apellidos, COUNT(*) as total 
         FROM registro_asistencia r 
         JOIN empleados e ON r.id_empleado = e.id 
         WHERE r.id_tipo_registro = 11 
         GROUP BY r.id_empleado 
         ORDER BY total DESC 
         LIMIT 5"
    ) ?: [];

    // Top horas extras
    $topHorasExtras = $conn->query(
        "SELECT e.nombres, e.apellidos, SUM(TIMESTAMPDIFF(HOUR, e.hora_salida, r.hora_salida)) as total 
         FROM registro_asistencia r 
         JOIN empleados e ON r.id_empleado = e.id 
         WHERE r.hora_salida > e.hora_salida 
         GROUP BY r.id_empleado 
         ORDER BY total DESC 
         LIMIT 5"
    ) ?: [];

} catch (Exception $e) {
    // Podrías registrar el error en un log aquí
    error_log("Error en el dashboard: " . $e->getMessage());
} finally {
    $database->close();
}

// Calculamos el porcentaje de comparación con el mes anterior de forma segura
$porcentajeComparacion = ($totalEmpleados > 0) ? 2.5 : 0; // Valor de ejemplo, deberías calcularlo con datos reales

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TEAM TIME - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --sidebar-width: 250px;
            --sidebar-bg: #212529;
            --sidebar-color: #fff;
            --sidebar-active-bg: #343a40;
        }
        
        body {
            display: flex;
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        
        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background-color: var(--sidebar-bg);
            color: var(--sidebar-color);
            position: fixed;
            height: 100vh;
            transition: all 0.3s;
            z-index: 1000;
        }
        
        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-header h3 {
            color: #fff;
            font-weight: 700;
            margin-bottom: 0;
        }
        
        .sidebar-user {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .sidebar-menu .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 10px 20px;
            margin: 5px 0;
            border-radius: 0;
            display: flex;
            align-items: center;
            transition: all 0.3s;
        }
        
        .sidebar-menu .nav-link:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-menu .nav-link.active {
            color: #fff;
            background-color: var(--sidebar-active-bg);
        }
        
        .sidebar-menu .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        /* Main content */
        .main-content {
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            padding: 20px;
            transition: all 0.3s;
        }
        
        /* Dashboard cards */
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
        
        /* Otros estilos del dashboard */
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
        
        /* Estilos para reportes */
        .report-filter {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .export-btn {
            margin-left: 10px;
        }
    </style>
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
                                <a class="nav-link" href="#reportAsistencia" data-bs-toggle="tab">Asistencias</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#reportHorasExtras" data-bs-toggle="tab">Horas Extras</a>
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
        <!-- Contenido del dashboard -->
        <div class="tab-content">
            <!-- Pestaña Dashboard -->
            <div class="tab-pane fade show active" id="dashboard">
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
                                        <h3 class="mb-0"><?= htmlspecialchars($totalEmpleados) ?></h3>
                                    <small class="text-success">
                                            <?= ($totalEmpleados > 0) ? '+'.$porcentajeComparacion.'% vs mes anterior' : 'Sin datos' ?>
                                        </small>
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
                                        <h3 class="mb-0"><?= htmlspecialchars($empleadosActivos) ?></h3>
                                        <small class="text-success">
                                            <?= ($totalEmpleados > 0) ? round(($empleadosActivos/$totalEmpleados)*100).'% del total' : 'Sin datos' ?>
                                        </small>
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

            <!-- Pestaña Reportes de Asistencia -->
            <div class="tab-pane fade" id="reportAsistencia">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>Reporte de Asistencias</h1>
                    <button class="btn btn-success export-excel" data-report="asistencia">
                        <i class="fas fa-file-excel me-1"></i> Exportar a Excel
                    </button>
                </div>

                <div class="report-filter mb-4">
                    <form id="filterAsistencia">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Buscar por:</label>
                                    <select class="form-select" name="search_type">
                                        <option value="nombre">Nombre</option>
                                        <option value="apellido">Apellido</option>
                                        <option value="codigo_empleado">Código Empleado</option>
                                        <option value="dni">DNI</option>
                                        <option value="token">Token</option>
                                        <option value="area">Área</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Término de búsqueda:</label>
                                    <input type="text" class="form-control" name="search_term" placeholder="Ingrese término...">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label class="form-label">Fecha Inicio:</label>
                                    <input type="date" class="form-control" name="fecha_inicio">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label class="form-label">Fecha Fin:</label>
                                    <input type="date" class="form-control" name="fecha_fin">
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                    </form>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="tableAsistencia">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Código</th>
                                        <th>Nombre</th>
                                        <th>Apellido</th>
                                        <th>Área</th>
                                        <th>DNI</th>
                                        <th>Token</th>
                                        <th>Fecha</th>
                                        <th>Hora Entrada</th>
                                        <th>Hora Salida</th>
                                        <th>Tipo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Los datos se cargarán aquí mediante AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pestaña Reportes de Horas Extras -->
            <div class="tab-pane fade" id="reportHorasExtras">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>Reporte de Horas Extras</h1>
                    <button class="btn btn-success export-excel" data-report="horas-extras">
                        <i class="fas fa-file-excel me-1"></i> Exportar a Excel
                    </button>
                </div>

                <div class="report-filter mb-4">
                    <form id="filterHorasExtras">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Buscar por:</label>
                                    <select class="form-select" name="search_type">
                                        <option value="nombre">Nombre</option>
                                        <option value="apellido">Apellido</option>
                                        <option value="codigo_empleado">Código Empleado</option>
                                        <option value="dni">DNI</option>
                                        <option value="token">Token</option>
                                        <option value="area">Área</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Término de búsqueda:</label>
                                    <input type="text" class="form-control" name="search_term" placeholder="Ingrese término...">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label class="form-label">Fecha Inicio:</label>
                                    <input type="date" class="form-control" name="fecha_inicio">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label class="form-label">Fecha Fin:</label>
                                    <input type="date" class="form-control" name="fecha_fin">
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                    </form>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="tableHorasExtras">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Código</th>
                                        <th>Nombre</th>
                                        <th>Apellido</th>
                                        <th>Área</th>
                                        <th>DNI</th>
                                        <th>Horas Extras</th>
                                        <th>Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Los datos se cargarán aquí mediante AJAX -->
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    
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

        // Funcionalidad para los reportes
        $(document).ready(function() {
            // Cargar reporte de asistencias
            $('#filterAsistencia').submit(function(e) {
                e.preventDefault();
                const formData = $(this).serialize();
                
                $.ajax({
                    url: '/admin/get_asistencias',
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        const data = JSON.parse(response);
                        let html = '';
                        
                        data.forEach((item, index) => {
                            html += `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${item.codigo_empleado || '-'}</td>
                                    <td>${item.nombre || '-'}</td>
                                    <td>${item.apellido || '-'}</td>
                                    <td>${item.area || '-'}</td>
                                    <td>${item.dni || '-'}</td>
                                    <td>${item.token || '-'}</td>
                                    <td>${item.fecha || '-'}</td>
                                    <td>${item.hora_entrada || '-'}</td>
                                    <td>${item.hora_salida || '-'}</td>
                                    <td>${item.tipo_registro || '-'}</td>
                                </tr>
                            `;
                        });
                        
                        $('#tableAsistencia tbody').html(html);
                    }
                });
            });
            
            // Cargar reporte de horas extras
            $('#filterHorasExtras').submit(function(e) {
                e.preventDefault();
                const formData = $(this).serialize();
                
                $.ajax({
                    url: '/admin/get_horas_extras',
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        const data = JSON.parse(response);
                        let html = '';
                        
                        data.forEach((item, index) => {
                            html += `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${item.codigo_empleado || '-'}</td>
                                    <td>${item.nombre || '-'}</td>
                                    <td>${item.apellido || '-'}</td>
                                    <td>${item.area || '-'}</td>
                                    <td>${item.dni || '-'}</td>
                                    <td>${item.horas_extras || '0'}</td>
                                    <td>${item.fecha || '-'}</td>
                                </tr>
                            `;
                        });
                        
                        $('#tableHorasExtras tbody').html(html);
                    }
                });
            });
            
            // Exportar a Excel
            $('.export-excel').click(function() {
                const reportType = $(this).data('report');
                const tableId = reportType === 'asistencia' ? 'tableAsistencia' : 'tableHorasExtras';
                const table = document.getElementById(tableId);
                const wb = XLSX.utils.table_to_book(table);
                XLSX.writeFile(wb, `reporte_${reportType}_${new Date().toISOString().slice(0,10)}.xlsx`);
            });
        });
    </script>
</body>
</html>