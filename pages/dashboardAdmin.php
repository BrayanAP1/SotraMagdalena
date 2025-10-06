<?php
session_start();

// Verificar si el usuario ha iniciado sesión y es administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'administrador') {
  header("Location: /Sotramagdalena/index.php");
  exit();
}

// Conexión PostgreSQL
$host = "dpg-d3he09ali9vc73e2a6o0-a";
$dbname = "enviosdb";
$user = "enviosdb_user"; // cámbialo si tu usuario es distinto
$password = "vgVeoNl0vf7WaTNH05FLHlHMAi2xi3uH"; // pon tu contraseña real

$conn = pg_connect("host=$host dbname=$dbname user=$user password=$password");
if (!$conn) {
  die("Error al conectar a PostgreSQL: " . pg_last_error());
}

date_default_timezone_set('America/Bogota');

// Total en enviosxdimensiones
$sqlDim = "SELECT COUNT(*) as totalDim FROM enviosxdimensiones";
$resultDim = pg_query($conn, $sqlDim);
$rowDim = pg_fetch_assoc($resultDim);
$totalDim = $rowDim['totaldim'];

// Total en enviosxpeso
$sqlPeso = "SELECT COUNT(*) as totalPeso FROM enviosxpeso";
$resultPeso = pg_query($conn, $sqlPeso);
$rowPeso = pg_fetch_assoc($resultPeso);
$totalPeso = $rowPeso['totalpeso'];

// Total usuarios
$sqlUsuarios = "SELECT COUNT(*) as totalUsuarios FROM usuarios";
$resultUsuarios = pg_query($conn, $sqlUsuarios);
$rowUsuarios = pg_fetch_assoc($resultUsuarios);
$totalUsuarios = $rowUsuarios['totalusuarios'];

// Total proveedores
$sqlProveedores = "SELECT COUNT(*) as totalProveedores FROM proveedores";
$resultProveedores = pg_query($conn, $sqlProveedores);
$rowProveedores = pg_fetch_assoc($resultProveedores);
$totalProveedores = $rowProveedores['totalproveedores'];

// Paquetes por dimensiones (últimos 6 meses)
$sqlEnviosMensuales = "
SELECT 
  TO_CHAR(fecha_registro, 'Month') as mes,
  COUNT(*) as total
FROM enviosxdimensiones
WHERE fecha_registro >= NOW() - INTERVAL '6 months'
GROUP BY EXTRACT(YEAR FROM fecha_registro), EXTRACT(MONTH FROM fecha_registro), TO_CHAR(fecha_registro, 'Month')
ORDER BY MIN(fecha_registro) ASC";
$resultEnviosMensuales = pg_query($conn, $sqlEnviosMensuales);

$enviosPorMesDim = [];
while ($row = pg_fetch_assoc($resultEnviosMensuales)) {
  $mes = trim($row['mes']);
  $enviosPorMesDim[$mes] = $row['total'];
}

// Paquetes por peso
$sqlEnviosMensualesPeso = "
SELECT 
  TO_CHAR(fecha_registro, 'Month') as mes,
  COUNT(*) as total
FROM enviosxpeso
WHERE fecha_registro >= NOW() - INTERVAL '6 months'
GROUP BY EXTRACT(YEAR FROM fecha_registro), EXTRACT(MONTH FROM fecha_registro), TO_CHAR(fecha_registro, 'Month')
ORDER BY MIN(fecha_registro) ASC";
$resultEnviosMensualesPeso = pg_query($conn, $sqlEnviosMensualesPeso);

$enviosPorMesPeso = [];
while ($row = pg_fetch_assoc($resultEnviosMensualesPeso)) {
  $mes = trim($row['mes']);
  $enviosPorMesPeso[$mes] = $row['total'];
}

// Combinar meses
$meses = array_unique(array_merge(array_keys($enviosPorMesDim), array_keys($enviosPorMesPeso)));

// Usuarios activos e inactivos
$sqlEstadoUsuarios = "
SELECT 
  SUM(CASE WHEN estado = TRUE THEN 1 ELSE 0 END) AS activos,
  SUM(CASE WHEN estado = FALSE THEN 1 ELSE 0 END) AS inactivos
FROM usuarios";
$resultEstadoUsuarios = pg_query($conn, $sqlEstadoUsuarios);

if (!$resultEstadoUsuarios) {
    die("Error en la consulta de usuarios: " . pg_last_error($conn));
}

$estadoUsuarios = pg_fetch_assoc($resultEstadoUsuarios);


// Últimos envíos
$sqlUltimosEnvios = "
(SELECT 
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
$resultUltimosEnvios = pg_query($conn, $sqlUltimosEnvios);

// Total paquetes
$totalPaquetes = $totalDim + $totalPeso;

pg_close($conn);
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
              <?php if ($resultUltimosEnvios && pg_num_rows($resultUltimosEnvios) > 0): ?>
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
                      <?php while ($envio = pg_fetch_assoc($resultUltimosEnvios)): ?>
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