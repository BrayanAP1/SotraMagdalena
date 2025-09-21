<?php
session_start();

// Verificar si el usuario ha iniciado sesión y es administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'administrador') {
  header("Location: /Sotramagdalena/index.php");
  exit();
}

// Conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "enviosdb";

$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
  die("Conexión fallida: " . $conn->connect_error);
}

date_default_timezone_set('America/Bogota');

// Consultar total de registros en enviosxdimensiones
$sqlDim = "SELECT COUNT(*) as totalDim FROM enviosxdimensiones";
$resultDim = $conn->query($sqlDim);
$rowDim = $resultDim->fetch_assoc();
$totalDim = $rowDim['totalDim'];

// Consultar total de registros en enviosxpeso
$sqlPeso = "SELECT COUNT(*) as totalPeso FROM enviosxpeso";
$resultPeso = $conn->query($sqlPeso);
$rowPeso = $resultPeso->fetch_assoc();
$totalPeso = $rowPeso['totalPeso'];

// Consultar total de usuarios
$sqlUsuarios = "SELECT COUNT(*) as totalUsuarios FROM usuarios";
$resultUsuarios = $conn->query($sqlUsuarios);
$rowUsuarios = $resultUsuarios->fetch_assoc();
$totalUsuarios = $rowUsuarios['totalUsuarios'];

// Consultar total de proveedores
$sqlProveedores = "SELECT COUNT(*) as totalProveedores FROM proveedores";
$resultProveedores = $conn->query($sqlProveedores);
$rowProveedores = $resultProveedores->fetch_assoc();
$totalProveedores = $rowProveedores['totalProveedores'];

// Paquetes por dimensiones
$sqlEnviosMensuales = "SELECT 
    DATE_FORMAT(fecha_registro, '%M') as mes,
    COUNT(*) as total
FROM enviosxdimensiones
WHERE fecha_registro >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
GROUP BY YEAR(fecha_registro), MONTH(fecha_registro)
ORDER BY fecha_registro ASC";
$resultEnviosMensuales = $conn->query($sqlEnviosMensuales);

$enviosPorMesDim = [];
while ($row = $resultEnviosMensuales->fetch_assoc()) {
  $enviosPorMesDim[$row['mes']] = $row['total'];
}

// Paquetes por peso
$sqlEnviosMensualesPeso = "SELECT 
    DATE_FORMAT(fecha_registro, '%M') as mes,
    COUNT(*) as total
FROM enviosxpeso
WHERE fecha_registro >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
GROUP BY YEAR(fecha_registro), MONTH(fecha_registro)
ORDER BY fecha_registro ASC";
$resultEnviosMensualesPeso = $conn->query($sqlEnviosMensualesPeso);

$enviosPorMesPeso = [];
while ($row = $resultEnviosMensualesPeso->fetch_assoc()) {
  $enviosPorMesPeso[$row['mes']] = $row['total'];
}

// Combinar meses
$meses = array_unique(array_merge(array_keys($enviosPorMesDim), array_keys($enviosPorMesPeso)));

// Consultar usuarios activos vs inactivos
$sqlEstadoUsuarios = "SELECT 
    SUM(estado = 1) as activos,
    SUM(estado = 0) as inactivos 
    FROM usuarios";
$resultEstadoUsuarios = $conn->query($sqlEstadoUsuarios);
$estadoUsuarios = $resultEstadoUsuarios->fetch_assoc();

// Consultar últimos paquetes registrados - CORREGIDO
$sqlUltimosEnvios = "(SELECT 
    id, nombre_cliente, direccion_destino, fecha_registro, 'Dimensiones' as tipo 
    FROM enviosxdimensiones 
    ORDER BY fecha_registro DESC 
    LIMIT 5)
    UNION ALL
    (SELECT 
    id, nombre_cliente, direccion_destino, fecha_registro, 'Peso' as tipo 
    FROM enviosxpeso 
    ORDER BY fecha_registro DESC 
    LIMIT 5)
    ORDER BY fecha_registro DESC 
    LIMIT 5";
$resultUltimosEnvios = $conn->query($sqlUltimosEnvios);

// Total de paquetes (suma de ambos tipos)
$totalPaquetes = $totalDim + $totalPeso;

// Obtener datos para las gráficas
$mesesData = [];
for ($i = 1; $i <= 12; $i++) {
  $mesesData[$i] = [
    'Dimensiones' => $enviosPorMesDim[$i] ?? 0,
    'Peso' => $enviosPorMesPeso[$i] ?? 0
  ];
}

// Obtener los últimos 6 meses con datos
$ultimosMeses = [];
$nombresMeses = [
  1 => 'Enero',
  2 => 'Febrero',
  3 => 'Marzo',
  4 => 'Abril',
  5 => 'Mayo',
  6 => 'Junio',
  7 => 'Julio',
  8 => 'Agosto',
  9 => 'Septiembre',
  10 => 'Octubre',
  11 => 'Noviembre',
  12 => 'Diciembre'
];

$mesActual = (int)date('n');
for ($i = 0; $i < 6; $i++) {
  $mes = $mesActual - $i;
  if ($mes < 1) $mes += 12;
  $ultimosMeses[$mes] = $nombresMeses[$mes];
}

$ultimosMeses = array_reverse($ultimosMeses, true);

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>SOTRA Magdalena - Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="../css/pages/styleDashboardAdmin.css">
</head>

<body>

  <nav class="navbar navbar-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">
        Sotra Magdalena - Dashboard
      </a>
      <div>
        <span class="navbar-text me-3">Hola, <?php echo $_SESSION['nombre'] ?? 'Administrador'; ?></span>
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
          <a href="dashboardAdmin.php" class="list-group-item list-group-item-action active">
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
          <a href="/Sotramagdalena/pages/reportes/reportes.php" class="list-group-item list-group-item-action">
            <i class="fas fa-chart-bar me-2"></i>Reportes
          </a>
        </div>

        <!-- Resumen rápido -->
        <div class="quick-stats mt-4">
          <h6 class="mb-3"><i class="fas fa-chart-pie me-2"></i>Resumen Rápido</h6>
          <div class="d-flex justify-content-between mb-2">
            <span>Total Paquetes:</span>
            <strong><?php echo $totalPaquetes; ?></strong>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <span>Por Dimensiones:</span>
            <strong><?php echo $totalDim; ?></strong>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <span>Por Peso:</span>
            <strong><?php echo $totalPeso; ?></strong>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <span>Usuarios Activos:</span>
            <strong><?php echo $estadoUsuarios['activos']; ?></strong>
          </div>
          <div class="d-flex justify-content-between">
            <span>Usuarios Inactivos:</span>
            <strong><?php echo $estadoUsuarios['inactivos']; ?></strong>
          </div>
        </div>
      </div>

      <!-- Contenido principal -->
      <div class="col-md-9">
        <!-- Encabezado del Dashboard -->
        <div class="dashboard-header">
          <div class="row align-items-center">
            <div class="col-md-8">
              <h2><i class="fas fa-tachometer-alt me-2"></i>Panel de Control</h2>
              <p class="welcome-text mb-0">Bienvenido al sistema de administración de SOTRA Magdalena</p>
            </div>
            <div class="col-md-4 text-end">
              <div class="rounded-pill px-3 py-1 d-inline-block">
                <i class="fas fa-calendar me-1"></i>
                <?php echo date('d/m/Y'); ?>
              </div>
            </div>
          </div>
        </div>

        <!-- Tarjetas con estadísticas -->
        <div class="row mb-4">
          <div class="col-md-3 col-6 mb-3">
            <div class="card stat-card primary card-hover">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <h6 class="card-title text-muted">Total Usuarios</h6>
                    <h3 class="card-text"><?php echo $totalUsuarios; ?></h3>
                  </div>
                  <div class="display-4" style="color: var(--primary-color);">
                    <i class="fas fa-users"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-3 col-6 mb-3">
            <div class="card stat-card success card-hover">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <h6 class="card-title text-muted">Paquetes Total</h6>
                    <h3 class="card-text"><?php echo $totalPaquetes; ?></h3>
                  </div>
                  <div class="display-4" style="color: var(--secondary-color);">
                    <i class="fas fa-box"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-3 col-6 mb-3">
            <div class="card stat-card info card-hover">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <h6 class="card-title text-muted">Por Dimensiones</h6>
                    <h3 class="card-text"><?php echo $totalDim; ?></h3>
                  </div>
                  <div class="display-4" style="color: var(--accent-color);">
                    <i class="fas fa-ruler-combined"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-3 col-6 mb-3">
            <div class="card stat-card warning card-hover">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <h6 class="card-title text-muted">Por Peso</h6>
                    <h3 class="card-text"><?php echo $totalPeso; ?></strong></h3>
                  </div>
                  <div class="display-4 text-warning">
                    <i class="fas fa-weight-hanging"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Gráficas -->
        <div class="row mb-4">
          <div class="col-md-8">
            <div class="chart-container">
              <h5 class="mb-3"><i class="fas fa-chart-bar me-2"></i>Envíos por Mes </h5>
              <canvas id="graficaEnviosMensuales" height="250"></canvas>
            </div>
          </div>
          <div class="col-md-4">
            <div class="chart-container">
              <h5 class="mb-3"><i class="fas fa-chart-pie me-2"></i>Distribución de Paquetes</h5>
              <canvas id="graficaDistribucion" height="250"></canvas>
            </div>
          </div>
        </div>

        <!-- Actividad Reciente y Estado de Usuarios -->
        <div class="row">
          <div class="col-md-8">
            <div class="recent-activity">
              <h5 class="mb-3"><i class="fas fa-clock me-2"></i>Envíos Recientes</h5>
              <?php if ($resultUltimosEnvios && $resultUltimosEnvios->num_rows > 0): ?>
                <div class="table-responsive">
                  <table class="table table-hover">
                    <thead>
                      <tr>
                        <th>Cliente</th>
                        <th>Destino</th>
                        <th>Fecha</th>
                        <th>Tipo</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php while ($envio = $resultUltimosEnvios->fetch_assoc()): ?>
                        <tr>
                          <td><?php echo htmlspecialchars($envio['nombre_cliente']); ?></td>
                          <td><?php echo htmlspecialchars($envio['direccion_destino']); ?></td>
                          <td><?php echo date('d/m/Y', strtotime($envio['fecha_registro'])); ?></td>
                          <td>
                            <span class="badge <?php echo $envio['tipo'] == 'Dimensiones' ? 'badge-dimensiones' : 'badge-peso'; ?>">
                              <?php echo $envio['tipo']; ?>
                            </span>
                          </td>
                        </tr>
                      <?php endwhile; ?>
                    </tbody>
                  </table>
                </div>
              <?php else: ?>
                <p class="text-muted">No hay envíos recientes</p>
              <?php endif; ?>
            </div>
          </div>
          <div class="col-md-4">
            <div class="chart-container">
              <h5 class="mb-3"><i class="fas fa-user-check me-2"></i>Estado de Usuarios</h5>
              <canvas id="graficaEstadoUsuarios" height="250"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    const meses = <?php echo json_encode(array_values($meses)); ?>;
    const datosDimensiones = <?php echo json_encode(array_values($enviosPorMesDim)); ?>;
    const datosPeso = <?php echo json_encode(array_values($enviosPorMesPeso)); ?>;
    const totalDim = <?php echo $totalDim; ?>;
    const totalPeso = <?php echo $totalPeso; ?>;
    const usuariosActivos = <?php echo $estadoUsuarios['activos']; ?>;
    const usuariosInactivos = <?php echo $estadoUsuarios['inactivos']; ?>;
  </script>
  <script src="../js/pages/scriptDashboardAdmin.js"></script>
</body>

</html>