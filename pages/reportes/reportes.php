<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "enviosdb";
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
  die("Conexión fallida: " . $conn->connect_error);
}

session_start();

// Verificar que haya sesión iniciada
if (!isset($_SESSION['id']) || !isset($_SESSION['rol'])) {
  header("Location: ../index.php");
  exit();
}

// Verificar rol (admin puede entrar a todo, usuario solo a lo suyo)
if ($_SESSION['rol'] !== 'usuario' && $_SESSION['rol'] !== 'administrador') {
  header("Location: ../index.php");
  exit();
}

// Obtener parámetros de filtro
date_default_timezone_set('America/Bogota');
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-m-01');
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-d');
$tipo_reporte = isset($_GET['tipo_reporte']) ? $_GET['tipo_reporte'] : 'resumen';


// Consultas para métricas principales
$total_dimensiones = $conn->query("SELECT COUNT(*) as total FROM enviosxdimensiones WHERE fecha_registro BETWEEN '$fecha_inicio' AND '$fecha_fin 23:59:59'")->fetch_assoc()['total'];
$total_peso = $conn->query("SELECT COUNT(*) as total FROM enviosxpeso WHERE fecha_registro BETWEEN '$fecha_inicio' AND '$fecha_fin 23:59:59'")->fetch_assoc()['total'];
$total_envios = $total_dimensiones + $total_peso;

// Ingresos totales
$ingresos_dimensiones = $conn->query("SELECT SUM(precio) as total FROM enviosxdimensiones WHERE fecha_registro BETWEEN '$fecha_inicio' AND '$fecha_fin 23:59:59'")->fetch_assoc()['total'] ?? 0;
$ingresos_peso = $conn->query("SELECT SUM(precio) as total FROM enviosxpeso WHERE fecha_registro BETWEEN '$fecha_inicio' AND '$fecha_fin 23:59:59'")->fetch_assoc()['total'] ?? 0;
$ingresos_totales = $ingresos_dimensiones + $ingresos_peso;

// Envíos por mes (para gráfica)
$envios_mensuales = [];
for ($i = 5; $i >= 0; $i--) {
  $mes = date('Y-m', strtotime("-$i months"));
  $mes_nombre = date('M Y', strtotime("-$i months"));

  $dim_mes = $conn->query("SELECT COUNT(*) as total FROM enviosxdimensiones WHERE DATE_FORMAT(fecha_registro, '%Y-%m') = '$mes'")->fetch_assoc()['total'];
  $peso_mes = $conn->query("SELECT COUNT(*) as total FROM enviosxpeso WHERE DATE_FORMAT(fecha_registro, '%Y-%m') = '$mes'")->fetch_assoc()['total'];

  $envios_mensuales[$mes_nombre] = [
    'dimensiones' => $dim_mes,
    'peso' => $peso_mes,
    'total' => $dim_mes + $peso_mes
  ];
}

// Top 5 clientes
$top_clientes = $conn->query("
    (SELECT nombre_cliente, COUNT(*) as envios, SUM(precio) as total_gastado 
     FROM enviosxdimensiones 
     WHERE fecha_registro BETWEEN '$fecha_inicio' AND '$fecha_fin 23:59:59'
     GROUP BY nombre_cliente)
    UNION ALL
    (SELECT nombre_cliente, COUNT(*) as envios, SUM(precio) as total_gastado 
     FROM enviosxpeso 
     WHERE fecha_registro BETWEEN '$fecha_inicio' AND '$fecha_fin 23:59:59'
     GROUP BY nombre_cliente)
    ORDER BY total_gastado DESC 
    LIMIT 5
");

// Envíos por usuario
$envios_por_usuario = $conn->query("
    SELECT u.username, 
           (SELECT COUNT(*) FROM enviosxdimensiones WHERE usuario_id = u.id AND fecha_registro BETWEEN '$fecha_inicio' AND '$fecha_fin 23:59:59') as dim_count,
           (SELECT COUNT(*) FROM enviosxpeso WHERE usuario_id = u.id AND fecha_registro BETWEEN '$fecha_inicio' AND '$fecha_fin 23:59:59') as peso_count
    FROM usuarios u
    HAVING (dim_count + peso_count) > 0
    ORDER BY (dim_count + peso_count) DESC
");

// Últimos envíos
$ultimos_envios = $conn->query("
    (SELECT id, nombre_cliente, direccion_destino, fecha_registro, 'Dimensiones' as tipo, precio
     FROM enviosxdimensiones 
     WHERE fecha_registro BETWEEN '$fecha_inicio' AND '$fecha_fin 23:59:59'
     ORDER BY fecha_registro DESC 
     LIMIT 5)
    UNION ALL
    (SELECT id, nombre_cliente, direccion_destino, fecha_registro, 'Peso' as tipo, precio
     FROM enviosxpeso 
     WHERE fecha_registro BETWEEN '$fecha_inicio' AND '$fecha_fin 23:59:59'
     ORDER BY fecha_registro DESC 
     LIMIT 5)
    ORDER BY fecha_registro DESC 
    LIMIT 10
");
?>

<script>
    // Pasar datos PHP a JavaScript para las gráficas
    var chartDataDistribucion = {
        totalDimensiones: <?php echo $total_dimensiones; ?>,
        totalPeso: <?php echo $total_peso; ?>
    };
    
    var chartDataTendencia = {
        labels: <?php echo json_encode(array_keys($envios_mensuales)); ?>,
        datosDimensiones: <?php echo json_encode(array_column($envios_mensuales, 'dimensiones')); ?>,
        datosPeso: <?php echo json_encode(array_column($envios_mensuales, 'peso')); ?>
    };
</script>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>SOTRA Magdalena - Reportes Avanzados</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
  <link rel="stylesheet" href="../../css/pages/reportes/styleReportes.css">
</head>

<body>

  <nav class="navbar navbar-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">
        Sotra Magdalena - Reportes
      </a>
      <div>
        <span class="navbar-text me-3">Hola, <?php echo $_SESSION['nombre'] ?? 'Usuario'; ?></span>
        <a href="/Sotramagdalena/login/logout.php" class="btn btn-light">
          <i class="fas fa-sign-out-alt me-1"></i> Cerrar sesión
        </a>
      </div>
    </div>
  </nav>

  <div class="container-fluid mt-4">
    <div class="row">
      <!-- Sidebar -->
      <div class="col-md-3">
        <div class="list-group">
          <a href="/Sotramagdalena/pages/dashboardAdmin.php" class="list-group-item list-group-item-action">
            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
          </a>
          <a href="/Sotramagdalena/pages/usuarios/usuarios.php" class="list-group-item list-group-item-action">
            <i class="fas fa-users me-2"></i>Usuarios
          </a>
          <a href="/Sotramagdalena/pages/paquetes/precios.php" class="list-group-item list-group-item-action">
            <i class="fas fa-box me-2"></i>Paquetes
          </a>
          <a href="/Sotramagdalena/pages/proveedores/proveedores.php" class="list-group-item list-group-item-action">
            <i class="fas fa-truck me-2"></i>Proveedores
          </a>
          <a href="#" class="list-group-item list-group-item-action active">
            <i class="fas fa-chart-bar me-2"></i>Reportes
          </a>
        </div>

        <!-- Filtros Rápidos -->
        <div class="dashboard-card card mt-4">
          <div class="card-body">
            <h6 class="card-title"><i class="fas fa-filter me-2"></i>Filtros Rápidos</h6>
            <div class="d-grid gap-2">
              <a href="?fecha_inicio=<?php echo date('Y-m-01'); ?>&fecha_fin=<?php echo date('Y-m-d'); ?>" class="btn btn-outline-primary btn-sm">Este Mes</a>
              <a href="?fecha_inicio=<?php echo date('Y-m-d', strtotime('-30 days')); ?>&fecha_fin=<?php echo date('Y-m-d'); ?>" class="btn btn-outline-primary btn-sm">Últimos 30 Días</a>
              <a href="?fecha_inicio=<?php echo date('Y-01-01'); ?>&fecha_fin=<?php echo date('Y-m-d'); ?>" class="btn btn-outline-primary btn-sm">Este Año</a>
            </div>
          </div>
        </div>
      </div>

      <!-- Contenido principal -->
      <div class="col-md-9">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h1 class="title-gradient">
            <i class="fas fa-chart-line me-2"></i>Reportes y Análisis
          </h1>
          <div class="date-pill px-3 py-1">
            <i class="fas fa-calendar me-1"></i>
            <?php
            date_default_timezone_set('America/Bogota');
            echo date('d/m/Y');
            ?>
          </div>
        </div>


        <!-- Filtros -->
        <div class="filter-section">
          <form method="GET" class="row g-3">
            <div class="col-md-3">
              <label class="form-label">Fecha Inicio</label>
              <input type="date" class="form-control" name="fecha_inicio" value="<?php echo $fecha_inicio; ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label">Fecha Fin</label>
              <input type="date" class="form-control" name="fecha_fin" value="<?php echo $fecha_fin; ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Tipo de Reporte</label>
              <select class="form-select" name="tipo_reporte">
                <option value="resumen" <?php echo $tipo_reporte == 'resumen' ? 'selected' : ''; ?>>Resumen General</option>
                <option value="detallado" <?php echo $tipo_reporte == 'detallado' ? 'selected' : ''; ?>>Reporte Detallado</option>
                <option value="ingresos" <?php echo $tipo_reporte == 'ingresos' ? 'selected' : ''; ?>>Análisis de Ingresos</option>
              </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
              <button type="submit" class="btn btn-primary w-100">
                <i class="fas fa-filter me-1"></i>Filtrar
              </button>
            </div>
          </form>
        </div>

        <!-- Métricas Principales -->
        <div class="row mb-4">
          <div class="col-md-3 mb-3">
            <div class="dashboard-card card stat-card primary">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <span class="metric-label">TOTAL ENVÍOS</span>
                    <h2 class="metric-number" style="color: var(--accent-color);"><?php echo number_format($total_envios); ?></h2>
                    <span class="metric-label"><?php echo date('d/m/Y', strtotime($fecha_inicio)); ?> - <?php echo date('d/m/Y', strtotime($fecha_fin)); ?></span>
                  </div>
                  <div class="display-5 opacity-30" style="color: var(--accent-color);">
                    <i class="fas fa-box"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-3 mb-3">
            <div class="dashboard-card card stat-card success">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <span class="metric-label">POR DIMENSIONES</span>
                    <h2 class="metric-number" style="color: var(--secondary-color);"><?php echo number_format($total_dimensiones); ?></h2>
                    <span class="metric-label"><?php echo $total_envios > 0 ? round(($total_dimensiones / $total_envios) * 100, 1) : 0; ?>% del total</span>
                  </div>
                  <div class="display-5 opacity-30" style="color: var(--secondary-color);">
                    <i class="fas fa-ruler-combined"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-3 mb-3">
            <div class="dashboard-card card stat-card info">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <span class="metric-label">POR PESO</span>
                    <h2 class="metric-number" style="color: var(--accent-color);"><?php echo number_format($total_peso); ?></h2>
                    <span class="metric-label"><?php echo $total_envios > 0 ? round(($total_peso / $total_envios) * 100, 1) : 0; ?>% del total</span>
                  </div>
                  <div class="display-5 opacity-30" style="color: var(--accent-color);">
                    <i class="fas fa-weight-hanging"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-3 mb-3">
            <div class="dashboard-card card stat-card warning">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <span class="metric-label">INGRESOS TOTALES</span>
                    <h2 class="metric-number text-warning">$<?php echo number_format($ingresos_totales, 2); ?></h2>
                    <span class="metric-label">Periodo seleccionado</span>
                  </div>
                  <div class="display-5 text-warning opacity-30">
                    <i class="fas fa-dollar-sign"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Gráficas y Tablas -->
        <div class="row">
          <!-- Gráfica de distribución -->
          <div class="col-md-6 mb-4">
            <div class="dashboard-card card">
              <div class="card-body">
                <h5 class="card-title"><i class="fas fa-chart-pie me-2"></i>Distribución de Envíos</h5>
                <div class="chart-container">
                  <canvas id="graficaDistribucion"></canvas>
                </div>
              </div>
            </div>
          </div>

          <!-- Gráfica de tendencia mensual -->
          <div class="col-md-6 mb-4">
            <div class="dashboard-card card">
              <div class="card-body">
                <h5 class="card-title"><i class="fas fa-chart-line me-2"></i>Tendencia Mensual</h5>
                <div class="chart-container">
                  <canvas id="graficaTendencia"></canvas>
                </div>
              </div>
            </div>
          </div>

          <!-- Top clientes -->
          <div class="col-md-6 mb-4">
            <div class="dashboard-card card">
              <div class="card-body">
                <h5 class="card-title"><i class="fas fa-star me-2"></i>Top 5 Clientes</h5>
                <div class="table-responsive">
                  <table class="table table-hover">
                    <thead>
                      <tr>
                        <th>Cliente</th>
                        <th>Envíos</th>
                        <th>Total Gastado</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if ($top_clientes && $top_clientes->num_rows > 0): ?>
                        <?php while ($cliente = $top_clientes->fetch_assoc()): ?>
                          <tr>
                            <td><?php echo htmlspecialchars($cliente['nombre_cliente']); ?></td>
                            <td><?php echo $cliente['envios']; ?></td>
                            <td>$<?php echo number_format($cliente['total_gastado'], 2); ?></td>
                          </tr>
                        <?php endwhile; ?>
                      <?php else: ?>
                        <tr>
                          <td colspan="3" class="text-center">No hay datos de clientes</td>
                        </tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <!-- Envíos por usuario -->
          <div class="col-md-6 mb-4">
            <div class="dashboard-card card">
              <div class="card-body">
                <h5 class="card-title"><i class="fas fa-user-check me-2"></i>Envíos por Usuario</h5>
                <div class="table-responsive">
                  <table class="table table-hover">
                    <thead>
                      <tr>
                        <th>Usuario</th>
                        <th>Por Dimensiones</th>
                        <th>Por Peso</th>
                        <th>Total</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if ($envios_por_usuario && $envios_por_usuario->num_rows > 0): ?>
                        <?php while ($usuario = $envios_por_usuario->fetch_assoc()): ?>
                          <tr>
                            <td><?php echo htmlspecialchars($usuario['username']); ?></td>
                            <td><?php echo $usuario['dim_count']; ?></td>
                            <td><?php echo $usuario['peso_count']; ?></td>
                            <td><strong><?php echo $usuario['dim_count'] + $usuario['peso_count']; ?></strong></td>
                          </tr>
                        <?php endwhile; ?>
                      <?php else: ?>
                        <tr>
                          <td colspan="4" class="text-center">No hay envíos por usuarios</td>
                        </tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <!-- Últimos envíos -->
          <div class="col-12 mb-4">
            <div class="dashboard-card card">
              <div class="card-body">
                <h5 class="card-title"><i class="fas fa-history me-2"></i>Últimos Envíos Registrados</h5>
                <div class="table-responsive">
                  <table class="table table-hover">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Destino</th>
                        <th>Tipo</th>
                        <th>Precio</th>
                        <th>Fecha</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if ($ultimos_envios && $ultimos_envios->num_rows > 0): ?>
                        <?php while ($envio = $ultimos_envios->fetch_assoc()): ?>
                          <tr>
                            <td>#<?php echo $envio['id']; ?></td>
                            <td><?php echo htmlspecialchars($envio['nombre_cliente']); ?></td>
                            <td><?php echo htmlspecialchars($envio['direccion_destino']); ?></td>
                            <td>
                              <span class="badge <?php echo $envio['tipo'] == 'Dimensiones' ? 'badge-dimensiones' : 'badge-peso'; ?>">
                                <?php echo $envio['tipo']; ?>
                              </span>
                            </td>
                            <td>$<?php echo number_format($envio['precio'], 2); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($envio['fecha_registro'])); ?></td>
                          </tr>
                        <?php endwhile; ?>
                      <?php else: ?>
                        <tr>
                          <td colspan="6" class="text-center">No hay envíos recientes</td>
                        </tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
<script src="../../js/pages/reportes/scriptReportes.js"></script>
  

</body>

</html>