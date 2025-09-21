<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "enviosdb";

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

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Procesar eliminación de proveedor
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $sql = "DELETE FROM proveedores WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['mensaje'] = "Proveedor eliminado correctamente";
        $_SESSION['tipo_mensaje'] = "success";
    } else {
        $_SESSION['mensaje'] = "Error al eliminar el proveedor";
        $_SESSION['tipo_mensaje'] = "danger";
    }
    header("Location: proveedores.php");
    exit();
}

// Procesar edición de proveedor
if (isset($_POST['editar'])) {
    $id       = $_POST['id'];
    $nombre   = $_POST['nombre'];
    $direccion = $_POST['direccion'];
    $telefono = $_POST['telefono'];
    $correo   = $_POST['correo'];

    $sql = "UPDATE proveedores SET nombre = ?, direccion = ?, telefono = ?, correo = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $nombre, $direccion, $telefono, $correo, $id);
    if ($stmt->execute()) {
        $_SESSION['mensaje'] = "Proveedor actualizado correctamente";
        $_SESSION['tipo_mensaje'] = "success";
    } else {
        $_SESSION['mensaje'] = "Error al actualizar el proveedor";
        $_SESSION['tipo_mensaje'] = "danger";
    }
    header("Location: proveedores.php");
    exit();
}

// Procesar agregar proveedor
if (isset($_POST['agregar'])) {
    $nombre   = $_POST['nombre'];
    $direccion = $_POST['direccion'];
    $telefono = $_POST['telefono'];
    $correo   = $_POST['correo'];

    $sql = "INSERT INTO proveedores (nombre, direccion, telefono, correo, fecha_registro) VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $nombre, $direccion, $telefono, $correo);
    if ($stmt->execute()) {
        $_SESSION['mensaje'] = "Proveedor agregado correctamente";
        $_SESSION['tipo_mensaje'] = "success";
    } else {
        $_SESSION['mensaje'] = "Error al agregar el proveedor";
        $_SESSION['tipo_mensaje'] = "danger";
    }
    header("Location: proveedores.php");
    exit();
}

// Obtener todos los proveedores
$sql = "SELECT * FROM proveedores ORDER BY fecha_registro DESC";
$result = $conn->query($sql);
$proveedores = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $proveedores[] = $row;
    }
}

// Obtener estadísticas para las tarjetas
$total_proveedores = is_array($proveedores) ? count($proveedores) : 0;
$activos_mes = $total_proveedores > 0 ? round($total_proveedores * 0.8) : 0;
$nuevos_mes = $total_proveedores > 0 ? round($total_proveedores * 0.15) : 0;
$premium = $total_proveedores > 0 ? round($total_proveedores * 0.25) : 0;


// consulta: contar proveedores por mes
$sql = "SELECT MONTH(fecha_registro) AS mes, COUNT(*) AS total 
        FROM proveedores 
        GROUP BY MONTH(fecha_registro)";
$result = $conn->query($sql);

// arrays para JS
$meses = [];
$totales = [];

$nombreMes = [
    1 => "Enero",
    2 => "Febrero",
    3 => "Marzo",
    4 => "Abril",
    5 => "Mayo",
    6 => "Junio",
    7 => "Julio",
    8 => "Agosto",
    9 => "Septiembre",
    10 => "Octubre",
    11 => "Noviembre",
    12 => "Diciembre"
];

while ($row = $result->fetch_assoc()) {
    $meses[] = $nombreMes[$row["mes"]];
    $totales[] = $row["total"];
}


?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>SOTRA Magdalena - Proveedores</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../../css/pages/proveedores/styleProveedores.css">
</head>

<body class="bg-light">
    <nav class="navbar navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                Sotra Magdalena - Proveedores
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
                <div class="list-group animate-fadeIn">
                    <a href="/Sotramagdalena/pages/dashboardAdmin.php" class="list-group-item list-group-item-action"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a>
                    <a href="/Sotramagdalena/pages/usuarios/usuarios.php" class="list-group-item list-group-item-action"><i class="fas fa-users me-2"></i>Usuarios</a>
                    <a href="/Sotramagdalena/pages/paquetes/precios.php" class="list-group-item list-group-item-action"><i class="fas fa-box me-2"></i>Paquetes</a>
                    <a href="/Sotramagdalena/pages/proveedores/proveedores.php" class="list-group-item list-group-item-action active"><i class="fas fa-truck me-2"></i>Proveedores</a>
                    <a href="/Sotramagdalena/pages/reportes/reportes.php" class="list-group-item list-group-item-action"><i class="fas fa-chart-bar me-2"></i>Reportes</a>
                </div>

                <!-- Tarjeta de acciones rápidas -->
                <div class="card mt-4">
                    <div class="card-body">
                        <h6 class="card-title text-proveedores">
                            <i class="fas fa-bolt me-2"></i>Acciones Rápidas
                        </h6>
                        <div class="d-grid gap-2">
                            <button class="btn btn-agregar-proveedor btn-sm" data-bs-toggle="modal" data-bs-target="#modalAgregar">
                                <i class="fas fa-plus-circle me-1"></i> Agregar Proveedor
                            </button>
                            <button class="btn btn-outline-secondary btn-sm" onclick="exportToPDF()">
                                <i class="fas fa-download me-1"></i> Exportar Lista
                            </button>
                        </div>
                    </div>
                </div>


            </div>

            <!-- Contenido principal -->
            <div class="col-md-9 content-load" id="mainContent">
                <!-- Mostrar mensajes de alerta -->
                <?php if (isset($_SESSION['mensaje'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['tipo_mensaje']; ?> alert-dismissible fade show mb-4" role="alert">
                        <i class="fas <?php echo $_SESSION['tipo_mensaje'] == 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?> me-2"></i>
                        <?php echo $_SESSION['mensaje']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php
                    unset($_SESSION['mensaje']);
                    unset($_SESSION['tipo_mensaje']);
                endif; ?>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><i class="fas fa-truck-loading me-2"></i>Administración de Proveedores</h1>
                    <div class="btn-group btn-group-custom" role="group">
                        <button type="button" class="btn active" onclick="mostrarVistaTabla()">
                            <i class="fas fa-table me-1"></i> Vista Tabla
                        </button>
                        <button type="button" class="btn" onclick="mostrarVistaTarjetas()">
                            <i class="fas fa-th me-1"></i> Vista Tarjetas
                        </button>
                    </div>

                </div>

                <!-- Tarjetas de estadísticas -->
                <div class="row mb-4">
                    <!-- Total Proveedores -->
                    <div class="col-md-4 mb-4">
                        <div class="card stats-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title text-muted">Total Proveedores</h6>
                                        <h3 class="card-text"><?php echo $total_proveedores; ?></h3>
                                    </div>
                                    <div class="display-5 text-primary-custom opacity-20">
                                        <i class="fas fa-truck"></i>
                                    </div>
                                </div>
                                <div class="progress mt-2">
                                    <div class="progress-bar bg-primary-custom" role="progressbar"
                                        style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>


                            </div>
                        </div>
                    </div>


                    <!-- Nuevos este mes -->
                    <div class="col-md-4 mb-4">
                        <div class="card stats-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title text-muted">Nuevos este mes</h6>
                                        <h3 class="card-text"><?php echo $nuevos_mes; ?></h3>
                                    </div>
                                    <div class="display-5 text-accent-custom opacity-30">
                                        <i class="fas fa-plus-circle"></i>
                                    </div>
                                </div>
                                <div class="progress mt-2">
                                    <div class="progress-bar bg-accent-custom" role="progressbar"
                                        style="width: <?php echo $total_proveedores > 0 ? ($nuevos_mes / $total_proveedores) * 100 : 0; ?>%"
                                        aria-valuenow="<?php echo $nuevos_mes; ?>" aria-valuemin="0" aria-valuemax="<?php echo $total_proveedores; ?>"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Nuevo sistema de filtrado avanzado -->
                <div class="card mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filtros Avanzados</h5>
                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#filtrosAvanzados">
                            <i class="fas fa-sliders-h me-1"></i> Mostrar/Ocultar
                        </button>
                    </div>
                    <div class="collapse show" id="filtrosAvanzados">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="filtroNombre" class="form-label">Nombre</label>
                                        <input type="text" class="form-control" id="filtroNombre" placeholder="Filtrar por nombre">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="filtroDireccion" class="form-label">Dirección</label>
                                        <input type="text" class="form-control" id="filtroDireccion" placeholder="Filtrar por dirección">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="filtroTelefono" class="form-label">Teléfono</label>
                                        <input type="text" class="form-control" id="filtroTelefono" placeholder="Filtrar por teléfono">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="filtroCorreo" class="form-label">Correo</label>
                                        <input type="text" class="form-control" id="filtroCorreo" placeholder="Filtrar por correo">
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="filtroFechaDesde" class="form-label">Fecha desde</label>
                                        <input type="date" class="form-control" id="filtroFechaDesde">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="filtroFechaHasta" class="form-label">Fecha hasta</label>
                                        <input type="date" class="form-control" id="filtroFechaHasta">
                                    </div>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button id="btnLimpiarFiltros" class="btn btn-secondary me-2">
                                        <i class="fas fa-times me-1"></i> Limpiar
                                    </button>
                                    <button id="btnAplicarFiltros" class="btn btn-primary">
                                        <i class="fas fa-filter me-1"></i> Aplicar Filtros
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vista de tabla (por defecto) -->
                <div class="card" id="vistaTabla">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Lista de Proveedores</h5>
                        <div class="d-flex align-items-center">
                            <span class="contador-resultados" id="contadorResultados">
                                Mostrando <?php echo $total_proveedores; ?> resultados
                            </span>
                            <div class="search-box" style="width: 250px;">
                                <i class="fas fa-search"></i>
                                <input type="text" class="form-control" id="busquedaRapida" placeholder="Búsqueda rápida...">
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="tablaProveedores">
                                <thead>
                                    <tr>
                                        <th data-col="0" class="sortable">ID <i class="fas fa-sort"></i></th>
                                        <th data-col="1" class="sortable">Nombre <i class="fas fa-sort"></i></th>
                                        <th data-col="2" class="sortable">Dirección <i class="fas fa-sort"></i></th>
                                        <th data-col="3" class="sortable">Teléfono <i class="fas fa-sort"></i></th>
                                        <th data-col="4" class="sortable">Correo <i class="fas fa-sort"></i></th>
                                        <th data-col="5" class="sortable">Fecha Registro <i class="fas fa-sort"></i></th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($total_proveedores > 0): ?>
                                        <?php foreach ($proveedores as $proveedor): ?>
                                            <tr class="animate-fadeIn">
                                                <td><strong>#<?= $proveedor["id"] ?></strong></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm bg-primary-custom rounded-circle d-flex align-items-center justify-content-center me-2"
                                                            style="width: 32px; height: 32px;">
                                                            <?= strtoupper(substr($proveedor["nombre"], 0, 1)) ?>
                                                        </div>

                                                        <?= htmlspecialchars($proveedor["nombre"]) ?>
                                                    </div>
                                                </td>
                                                <td><?= htmlspecialchars($proveedor["direccion"]) ?></td>
                                                <td><?= htmlspecialchars($proveedor["telefono"]) ?></td>
                                                <td><?= htmlspecialchars($proveedor["correo"]) ?></td>
                                                <td><?= date('d/m/Y', strtotime($proveedor["fecha_registro"])) ?></td>
                                                <td>
                                                    <span class="badge badge-active">Activo</span>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button class="btn btn-warning btn-sm btn-action"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#modalEditar"
                                                            data-id="<?= $proveedor['id'] ?>"
                                                            data-nombre="<?= htmlspecialchars($proveedor['nombre']) ?>"
                                                            data-direccion="<?= htmlspecialchars($proveedor['direccion']) ?>"
                                                            data-telefono="<?= htmlspecialchars($proveedor['telefono']) ?>"
                                                            data-correo="<?= htmlspecialchars($proveedor['correo']) ?>"
                                                            title="Editar proveedor">
                                                            <i class="fas fa-edit"></i>
                                                        </button>

                                                        <a href="proveedores.php?eliminar=<?= $proveedor['id'] ?>"
                                                            class="btn btn-danger btn-sm btn-action"
                                                            onclick="return confirm('¿Seguro que deseas eliminar este proveedor?');"
                                                            title="Eliminar proveedor">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center py-4">
                                                <i class="fas fa-truck-loading fa-3x text-muted mb-3"></i>
                                                <h5 class="text-muted">No hay proveedores registrados</h5>
                                                <p class="text-muted">Comienza agregando tu primer proveedor</p>
                                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregar">
                                                    <i class="fas fa-plus me-1"></i> Agregar Proveedor
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Vista de tarjetas (oculta por defecto) -->
                <div class="card d-none" id="vistaTarjetas">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-th me-2"></i>Vista de Tarjetas</h5>
                        <div class="d-flex align-items-center">
                            <span class="contador-resultados" id="contadorResultadosTarjetas">
                                Mostrando <?php echo $total_proveedores; ?> resultados
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row" id="contenedorTarjetas">
                            <?php if ($total_proveedores > 0): ?>
                                <?php foreach ($proveedores as $proveedor): ?>
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card provider-card h-100">
                                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                                <div class="avatar-lg bg-primary-custom rounded-circle d-flex align-items-center justify-content-center"
                                                    style="width: 50px; height: 50px;">
                                                    <?= strtoupper(substr($proveedor["nombre"], 0, 1)) ?>
                                                </div>


                                            </div>
                                            <div class="card-body">
                                                <h5 class="card-title"><?= htmlspecialchars($proveedor["nombre"]) ?></h5>
                                                <div class="mb-2">
                                                    <i class="fas fa-map-marker-alt text-muted me-2"></i>
                                                    <span class="text-muted"><?= htmlspecialchars($proveedor["direccion"]) ?></span>
                                                </div>
                                                <div class="mb-2">
                                                    <i class="fas fa-phone text-muted me-2"></i>
                                                    <span class="text-muted"><?= htmlspecialchars($proveedor["telefono"]) ?></span>
                                                </div>
                                                <div class="mb-3">
                                                    <i class="fas fa-envelope text-muted me-2"></i>
                                                    <span class="text-muted"><?= htmlspecialchars($proveedor["correo"]) ?></span>
                                                </div>
                                                <div class="d-flex justify-content-between text-muted small">
                                                    <span>Registrado: <?= date('d/m/Y', strtotime($proveedor["fecha_registro"])) ?></span>
                                                    <span>ID: #<?= $proveedor["id"] ?></span>
                                                </div>
                                            </div>
                                            <div class="card-footer bg-white">
                                                <div class="btn-group w-100" role="group">
                                                    <button class="btn btn-warning btn-sm btn-action"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#modalEditar"
                                                        data-id="<?= $proveedor['id'] ?>"
                                                        data-nombre="<?= htmlspecialchars($proveedor['nombre']) ?>"
                                                        data-direccion="<?= htmlspecialchars($proveedor['direccion']) ?>"
                                                        data-telefono="<?= htmlspecialchars($proveedor['telefono']) ?>"
                                                        data-correo="<?= htmlspecialchars($proveedor['correo']) ?>"
                                                        title="Editar proveedor">
                                                        <i class="fas fa-edit me-1"></i> Editar
                                                    </button>

                                                    <a href="proveedores.php?eliminar=<?= $proveedor['id'] ?>"
                                                        class="btn btn-danger btn-sm btn-action"
                                                        onclick="return confirm('¿Seguro que deseas eliminar este proveedor?');"
                                                        title="Eliminar proveedor">
                                                        <i class="fas fa-trash me-1"></i> Eliminar
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-12 text-center py-4">
                                    <i class="fas fa-truck-loading fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No hay proveedores registrados</h5>
                                    <p class="text-muted">Comienza agregando tu primer proveedor</p>
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregar">
                                        <i class="fas fa-plus me-1"></i> Agregar Proveedor
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta del gráfico -->
                <div class="card mt-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Registro de Proveedores por Mes</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="proveedoresChart"
                            data-meses='<?php echo json_encode($meses); ?>'
                            data-totales='<?php echo json_encode($totales); ?>'>
                        </canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Editar -->
    <div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Editar Proveedor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit-id">
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" class="form-control" name="nombre" id="edit-nombre" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Dirección</label>
                        <textarea class="form-control" name="direccion" id="edit-direccion" rows="2" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Teléfono</label>
                        <input type="text" class="form-control" name="telefono" id="edit-telefono" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Correo</label>
                        <input type="email" class="form-control" name="correo" id="edit-correo" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="editar" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Agregar -->
    <div class="modal fade" id="modalAgregar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Agregar Proveedor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" class="form-control" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Dirección</label>
                        <textarea class="form-control" name="direccion" rows="2" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Teléfono</label>
                        <input type="text" class="form-control" name="telefono" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Correo</label>
                        <input type="email" class="form-control" name="correo" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="agregar" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Agregar Proveedor
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toast para notificaciones -->
    <div class="custom-toast alert alert-success alert-dismissible fade" role="alert">
        <div class="d-flex">
            <div class="toast-body"></div>
            <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>

    <script>
        // Pasar datos PHP a JavaScript
        var mesesData = <?php echo json_encode($meses); ?>;
        var totalesData = <?php echo json_encode($totales); ?>;
    </script>
    <script src="../../js/pages/proveedores/scriptProveedores.js"></script>
</body>

</html>