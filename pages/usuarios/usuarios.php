<?php 
include("conexion.php"); 

session_start();

// Verificar que haya sesión iniciada
if (!isset($_SESSION['id']) || !isset($_SESSION['rol'])) {
    header("Location: ../index.php"); 
    exit();
}

// Verificar rol (solo administrador puede acceder)
if ($_SESSION['rol'] !== 'administrador') {
    header("Location: ../index.php");
    exit();
}

// Procesar cambios de estado
if (isset($_GET['cambiar_estado'])) {
    $id = intval($_GET['id']);
    $nuevo_estado = intval($_GET['cambiar_estado']);
    
    $sql = "UPDATE usuarios SET estado = $nuevo_estado WHERE id = $id";
    if ($conn->query($sql)) {
        $mensaje = $nuevo_estado ? "Usuario activado correctamente" : "Usuario desactivado correctamente";
        $tipo_mensaje = "success";
    } else {
        $mensaje = "Error al cambiar estado: " . $conn->error;
        $tipo_mensaje = "danger";
    }
}

// Búsqueda y filtrado
$filtro = "";
$where = "";

if (isset($_GET['buscar']) && !empty($_GET['buscar'])) {
    $busqueda = $conn->real_escape_string($_GET['buscar']);
    $where = "WHERE (nombre LIKE '%$busqueda%' OR username LIKE '%$busqueda%' OR rol LIKE '%$busqueda%')";
    $filtro = $_GET['buscar'];
}

if (isset($_GET['rol']) && $_GET['rol'] != 'todos') {
    $rol = $conn->real_escape_string($_GET['rol']);
    $where .= $where ? " AND rol = '$rol'" : "WHERE rol = '$rol'";
}

if (isset($_GET['estado']) && $_GET['estado'] != 'todos') {
    $estado = intval($_GET['estado']);
    $where .= $where ? " AND estado = $estado" : "WHERE estado = $estado";
}

// Obtener usuarios
$sql = "SELECT * FROM usuarios $where ORDER BY nombre ASC";
$result = $conn->query($sql);
$total_usuarios = $result->num_rows;

// Estadísticas
$stats_sql = "SELECT 
    COUNT(*) as total,
    SUM(estado = 1) as activos,
    SUM(estado = 0) as inactivos,
    SUM(rol = 'administrador') as admins,
    SUM(rol = 'usuario') as usuarios
    FROM usuarios";
$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>SOTRA Magdalena - Usuarios</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../../css/pages/usuarios/styleUsuarios.css">
</head>
<body>

<nav class="navbar navbar-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">
      Sotra Magdalena - Usuarios
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
        <a href="/Sotramagdalena/pages/dashboardAdmin.php" class="list-group-item list-group-item-action">
          <i class="fas fa-tachometer-alt me-2"></i>Dashboard
        </a>
        <a href="/Sotramagdalena/pages/usuarios/usuarios.php" class="list-group-item list-group-item-action active">
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
      
      <!-- Tarjeta de información rápida -->
      <div class="card mt-4">
        <div class="card-body">
          <h6 class="card-title"><i class="fas fa-info-circle me-2"></i>Información Rápida</h6>
          <p class="card-text small">
            Gestiona todos los usuarios del sistema desde esta sección. Puedes crear, editar, activar/desactivar y eliminar usuarios.
          </p>
        </div>
      </div>
    </div>

    <!-- Contenido principal -->
    <div class="col-md-9">
      <!-- Alertas -->
      <?php if (isset($mensaje)): ?>
      <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
        <i class="fas <?php echo $tipo_mensaje == 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?> me-2"></i>
        <?php echo $mensaje; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      <?php endif; ?>
      
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="text-primary">
          <i class="fas fa-users me-2"></i>Administración de Usuarios
        </h1>
        <a href="crearUsu.php" class="btn btn-primary">
          <i class="fas fa-plus-circle me-1"></i> Crear Usuario
        </a>
      </div>
      
      <!-- Tarjetas de estadísticas de Usuarios -->
      <div class="row mb-4">
        <!-- Total Usuarios -->
        <div class="col-md-3 col-6 mb-4">
          <div class="card stats-card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="card-title text-muted">Total Usuarios</h6>
                  <h3 class="card-text"><?php echo $stats['total']; ?></h3>
                </div>
                <div class="display-5 opacity-20" style="color: var(--primary-color);">
                  <i class="fas fa-users"></i>
                </div>
              </div>
              <div class="progress mt-2">
                <div class="progress-bar" role="progressbar" 
                     style="width: 100%; background-color: var(--primary-color);" 
                     aria-valuenow="<?php echo $stats['total']; ?>" 
                     aria-valuemin="0" 
                     aria-valuemax="<?php echo $stats['total']; ?>"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Usuarios Normales -->
        <div class="col-md-3 col-6 mb-4">
          <div class="card stats-card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="card-title text-muted">Usuarios Normales</h6>
                  <h3 class="card-text"><?php echo $stats['usuarios']; ?></h3>
                </div>
                <div class="display-5 opacity-30" style="color: var(--secondary-color);">
                  <i class="fas fa-user"></i>
                </div>
              </div>
              <div class="progress mt-2">
                <div class="progress-bar" role="progressbar" 
                     style="width: <?php echo $stats['total'] > 0 ? ($stats['usuarios'] / $stats['total']) * 100 : 0; ?>%; background-color: var(--secondary-color);" 
                     aria-valuenow="<?php echo $stats['usuarios']; ?>" 
                     aria-valuemin="0" aria-valuemax="<?php echo $stats['total']; ?>"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Administradores -->
        <div class="col-md-3 col-6 mb-4">
          <div class="card stats-card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="card-title text-muted">Administradores</h6>
                  <h3 class="card-text"><?php echo $stats['admins']; ?></h3>
                </div>
                <div class="display-5 opacity-30" style="color: var(--accent-color);">
                  <i class="fas fa-user-shield"></i>
                </div>
              </div>
              <div class="progress mt-2">
                <div class="progress-bar" role="progressbar" 
                     style="width: <?php echo $stats['total'] > 0 ? ($stats['admins'] / $stats['total']) * 100 : 0; ?>%; background-color: var(--accent-color);" 
                     aria-valuenow="<?php echo $stats['admins']; ?>" 
                     aria-valuemin="0" aria-valuemax="<?php echo $stats['total']; ?>"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Inactivos -->
        <div class="col-md-3 col-6 mb-4">
          <div class="card stats-card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="card-title text-muted">Inactivos</h6>
                  <h3 class="card-text"><?php echo $stats['inactivos']; ?></h3>
                </div>
                <div class="display-5 opacity-30" style="color: var(--primary-dark);">
                  <i class="fas fa-user-times"></i>
                </div>
              </div>
              <div class="progress mt-2">
                <div class="progress-bar" role="progressbar" 
                     style="width: <?php echo $stats['total'] > 0 ? ($stats['inactivos'] / $stats['total']) * 100 : 0; ?>%; background-color: var(--primary-dark);" 
                     aria-valuenow="<?php echo $stats['inactivos']; ?>" 
                     aria-valuemin="0" aria-valuemax="<?php echo $stats['total']; ?>"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Filtros y búsqueda -->
      <div class="filter-section card-hover">
        <h5 class="mb-3"><i class="fas fa-filter me-2"></i>Filtrar Usuarios</h5>
        <form method="GET" class="row g-3">
          <div class="col-md-5 search-box">
            <i class="fas fa-search"></i>
            <input type="text" class="form-control" name="buscar" placeholder="Buscar por nombre, usuario o rol..." value="<?php echo htmlspecialchars($filtro); ?>">
          </div>
          <div class="col-md-3">
            <select class="form-select" name="rol">
              <option value="todos">Todos los roles</option>
              <option value="administrador" <?php echo (isset($_GET['rol']) && $_GET['rol'] == 'administrador') ? 'selected' : ''; ?>>Administrador</option>
              <option value="usuario" <?php echo (isset($_GET['rol']) && $_GET['rol'] == 'usuario') ? 'selected' : ''; ?>>Usuario</option>
            </select>
          </div>
          <div class="col-md-2">
            <select class="form-select" name="estado">
              <option value="todos">Todos los estados</option>
              <option value="1" <?php echo (isset($_GET['estado']) && $_GET['estado'] == '1') ? 'selected' : ''; ?>>Activo</option>
              <option value="0" <?php echo (isset($_GET['estado']) && $_GET['estado'] == '0') ? 'selected' : ''; ?>>Inactivo</option>
            </select>
          </div>
          <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">
              <i class="fas fa-filter me-1"></i>Filtrar
            </button>
          </div>
        </form>
        <?php if ($filtro || isset($_GET['rol']) || isset($_GET['estado'])): ?>
        <div class="mt-3">
          <a href="usuarios.php" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-times me-1"></i>Limpiar filtros
          </a>
          <span class="ms-2 text-muted"><?php echo $total_usuarios; ?> resultados encontrados</span>
        </div>
        <?php endif; ?>
      </div>
      
      <!-- Tabla de usuarios -->
      <div class="card card-hover">
        <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
  <h5 class="mb-0"><i class="fas fa-list me-2"></i>Lista de Usuarios</h5>
  <span class="badge badge-custom"><?php echo $total_usuarios; ?> usuarios</span>
</div>

        <div class="card-body p-0">
          <?php if ($total_usuarios > 0): ?>
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Nombre</th>
                  <th>Usuario</th>
                  <th>Rol</th>
                  <th>Estado</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                  <td><strong>#<?php echo $row['id']; ?></strong></td>
                  <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                  <td><?php echo htmlspecialchars($row['username']); ?></td>
                  <td>
                    <span class="badge <?php echo $row['rol'] == 'administrador' ? 'badge-admin' : 'badge-user'; ?>">
                      <?php echo ucfirst($row['rol']); ?>
                    </span>
                  </td>
                  <td>
                    <span class="badge <?php echo $row['estado'] ? 'badge-active' : 'badge-inactive'; ?>">
                      <?php echo $row['estado'] ? "Activo" : "Inactivo"; ?>
                    </span>
                  </td>
                  <td>
                    <div class="d-flex">
                      <a href="editarUsu.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm action-btn" title="Editar">
                        <i class="fas fa-edit"></i>
                      </a>
                      <?php if ($row['id'] != $_SESSION['id']): ?>
                      <a href="?id=<?php echo $row['id']; ?>&cambiar_estado=<?php echo $row['estado'] ? 0 : 1; ?>" 
                         class="btn btn-<?php echo $row['estado'] ? 'secondary' : 'success'; ?> btn-sm action-btn"
                         title="<?php echo $row['estado'] ? 'Desactivar' : 'Activar'; ?>">
                        <i class="fas fa-<?php echo $row['estado'] ? 'toggle-on' : 'toggle-off'; ?>"></i>
                      </a>
                      <a href="eliminarUsu.php?id=<?php echo $row['id']; ?>" 
                         class="btn btn-danger btn-sm action-btn" 
                         onclick="return confirm('¿Estás seguro de que deseas eliminar permanentemente a <?php echo addslashes($row['nombre']); ?>?');"
                         title="Eliminar">
                        <i class="fas fa-trash"></i>
                      </a>
                      <?php else: ?>
                      <span class="btn btn-outline-secondary btn-sm action-btn disabled" title="No puedes modificar tu propio estado">
                        <i class="fas fa-user-lock"></i>
                      </span>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
          <?php else: ?>
          <div class="text-center py-5">
            <i class="fas fa-user-slash fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No se encontraron usuarios</h5>
            <p class="text-muted"><?php echo ($filtro || isset($_GET['rol']) || isset($_GET['estado'])) ? 
                'Intenta ajustar los filtros de búsqueda.' : 
                'No hay usuarios registrados en el sistema.'; ?>
            </p>
            <?php if (!$filtro && !isset($_GET['rol']) && !isset($_GET['estado'])): ?>
            <a href="crearUsu.php" class="btn btn-primary mt-2">
              <i class="fas fa-plus-circle me-1"></i> Crear primer usuario
            </a>
            <?php endif; ?>
          </div>
          <?php endif; ?>
        </div>
        <?php if ($total_usuarios > 0): ?>
        <div class="card-footer bg-light">
          <small class="text-muted">
            <i class="fas fa-info-circle me-1"></i>Mostrando <?php echo $total_usuarios; ?> usuario(s)
          </small>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../js/pages/usuarios/scriptUsuarios.js"></script>
</body>
</html>