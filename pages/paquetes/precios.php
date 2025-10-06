<?php
include("../usuarios/conexion.php");
session_start();

// 🔒 Verificar sesión
if (!isset($_SESSION['id']) || !isset($_SESSION['rol'])) {
    header("Location: ../index.php");
    exit();
}

// 🔒 Rol permitido
if ($_SESSION['rol'] !== 'usuario' && $_SESSION['rol'] !== 'administrador') {
    header("Location: ../index.php");
    exit();
}

// 🚀 Eliminar paquete
if (isset($_GET['eliminar']) && isset($_GET['tabla'])) {
    $id = intval($_GET['eliminar']);
    $tabla = ($_GET['tabla'] === 'dim') ? 'enviosxdimensiones' : 'enviosxpeso';

    try {
        $stmt = $conn->prepare("DELETE FROM $tabla WHERE id = :id");
        $stmt->execute([':id' => $id]);
        header("Location: precios.php?success=Paquete eliminado");
        exit();
    } catch (PDOException $e) {
        die("Error al eliminar: " . $e->getMessage());
    }
}

// 🔧 Helper: obtener columnas de una tabla (PostgreSQL usa INFORMATION_SCHEMA)
function getCols(PDO $conn, string $table): array {
    $cols = [];
    $stmt = $conn->prepare("
        SELECT column_name 
        FROM information_schema.columns 
        WHERE table_name = :table
    ");
    $stmt->execute([':table' => $table]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $cols[] = $row['column_name'];
    }
    return $cols;
}

// Helper: arma una lista "campo" o "NULL AS campo" si no existe
function pick(array $cols, array $wanted): string {
    $parts = [];
    foreach ($wanted as $w) {
        $parts[] = in_array($w, $cols) ? "\"$w\"" : "NULL AS \"$w\"";
    }
    return implode(", ", $parts);
}

// 🧮 Filtros
$filtros = [];
$where_conditions = [];

// Filtro por tipo
$tipoFiltro = $_GET['tipo'] ?? 'todos';

// Filtro por cliente
if (!empty($_GET['cliente'])) {
    $cliente = trim($_GET['cliente']);
    $where_conditions[] = "(nombre_cliente ILIKE :cliente)";
    $filtros['cliente'] = "%$cliente%";
}

// Filtro por fechas
if (!empty($_GET['fecha_inicio'])) {
    $where_conditions[] = "(fecha_registro >= :fecha_inicio)";
    $filtros['fecha_inicio'] = $_GET['fecha_inicio'] . " 00:00:00";
}

if (!empty($_GET['fecha_fin'])) {
    $where_conditions[] = "(fecha_registro <= :fecha_fin)";
    $filtros['fecha_fin'] = $_GET['fecha_fin'] . " 23:59:59";
}

// Filtro por precios
if (isset($_GET['precio_min']) && is_numeric($_GET['precio_min'])) {
    $where_conditions[] = "(precio >= :precio_min)";
    $filtros['precio_min'] = floatval($_GET['precio_min']);
}

if (isset($_GET['precio_max']) && is_numeric($_GET['precio_max'])) {
    $where_conditions[] = "(precio <= :precio_max)";
    $filtros['precio_max'] = floatval($_GET['precio_max']);
}

// Construir WHERE dinámico
$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// 🧱 Columnas
$baseFields = ['id', 'nombre_cliente', 'direccion_origen', 'direccion_destino', 'contenido', 'precio', 'rango', 'fecha_registro'];
$dimFields  = ['alto', 'largo', 'ancho'];
$pesoField  = ['peso'];

// 🔍 Obtener columnas reales de cada tabla
$colsDim  = getCols($conn, 'enviosxdimensiones');
$colsPeso = getCols($conn, 'enviosxpeso');

$selDim  = pick($colsDim, $baseFields) . ", " . pick($colsDim, $dimFields) . ", " . pick($colsDim, $pesoField);
$selPeso = pick($colsPeso, $baseFields) . ", " . pick($colsPeso, $dimFields) . ", " . pick($colsPeso, $pesoField);

$paquetes = [];

// 🔄 Consultar tablas según filtros
try {
    if ($tipoFiltro === 'todos') {
        $sql1 = "SELECT $selDim, 'Dimensiones' AS tipo, 'dim' AS tabla FROM enviosxdimensiones $where_clause";
        $sql2 = "SELECT $selPeso, 'Peso' AS tipo, 'peso' AS tabla FROM enviosxpeso $where_clause";

        $stmt1 = $conn->prepare($sql1);
        $stmt1->execute($filtros);
        $paquetes = array_merge($paquetes, $stmt1->fetchAll(PDO::FETCH_ASSOC));

        $stmt2 = $conn->prepare($sql2);
        $stmt2->execute($filtros);
        $paquetes = array_merge($paquetes, $stmt2->fetchAll(PDO::FETCH_ASSOC));

    } elseif ($_GET['tipo'] === 'dimensiones') {
        $sql = "SELECT $selDim, 'Dimensiones' AS tipo, 'dim' AS tabla FROM enviosxdimensiones $where_clause";
        $stmt = $conn->prepare($sql);
        $stmt->execute($filtros);
        $paquetes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } elseif ($_GET['tipo'] === 'peso') {
        $sql = "SELECT $selPeso, 'Peso' AS tipo, 'peso' AS tabla FROM enviosxpeso $where_clause";
        $stmt = $conn->prepare($sql);
        $stmt->execute($filtros);
        $paquetes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    echo "⚠️ Error en la consulta: " . $e->getMessage();
}

// 📊 Ordenar por fecha
usort($paquetes, function ($a, $b) {
    $ta = isset($a['fecha_registro']) ? strtotime($a['fecha_registro']) : 0;
    $tb = isset($b['fecha_registro']) ? strtotime($b['fecha_registro']) : 0;
    return $tb <=> $ta;
});

// 📈 Estadísticas
$total_paquetes = count($paquetes);
$total_dimensiones = count(array_filter($paquetes, fn($p) => ($p['tipo'] ?? '') === 'Dimensiones'));
$total_peso = count(array_filter($paquetes, fn($p) => ($p['tipo'] ?? '') === 'Peso'));
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>SOTRA Magdalena - Paquetes</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/pages/paquetes/stylePrecios.css">
</head>

<body>
    <nav class="navbar navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                Sotra Magdalena - Paquetes
            </a>
            <div>
                <span class="navbar-text me-3">Hola, <?php echo $_SESSION['nombre'] ?? 'Administrador'; ?></span>
                <a href="../../login/logout.php" class="btn btn-light">
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
                    <a href="../dashboardAdmin.php" class="list-group-item list-group-item-action"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a>
                    <a href="../usuarios/usuarios.php" class="list-group-item list-group-item-action"><i class="fas fa-users me-2"></i>Usuarios</a>
                    <a href="../paquetes/precios.php" class="list-group-item list-group-item-action active"><i class="fas fa-box me-2"></i>Paquetes</a>
                    <a href="../proveedores/proveedores.php" class="list-group-item list-group-item-action"><i class="fas fa-truck me-2"></i>Proveedores</a>
                    <a href="../reportes/reportes.php" class="list-group-item list-group-item-action"><i class="fas fa-chart-bar me-2"></i>Reportes</a>
                </div>
            </div>

            <!-- Contenido principal -->
            <div class="col-md-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="title-gradient"><i class="fas fa-box me-2"></i>Gestión de Paquetes</h1>
                </div>

                <!-- Resumen estadístico de Paquetes -->
                <div class="row mb-4">
                    <!-- Total de Paquetes -->
                    <div class="col-md-4 mb-4">
                        <div class="card stats-card h-100">
                            <div class="card-body d-flex flex-column justify-content-between">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title text-muted">Total de Paquetes</h5>
                                        <h3 class="card-text"><?= $total_paquetes ?></h3>
                                        <p class="text-muted">Resultados de búsqueda</p>
                                    </div>
                                    <div class="display-5 opacity-30" style="color: var(--primary-color);">
                                        <i class="fas fa-boxes"></i>
                                    </div>
                                </div>
                                <div class="progress mt-2">
                                    <div class="progress-bar" role="progressbar"
                                        style="width: 100%; background-color: var(--primary-color);"
                                        aria-valuenow="<?= $total_paquetes ?>"
                                        aria-valuemin="0"
                                        aria-valuemax="<?= $total_paquetes ?>"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Paquetes por Dimensiones -->
                    <div class="col-md-4 mb-4">
                        <div class="card stats-card h-100">
                            <div class="card-body d-flex flex-column justify-content-between">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title text-muted">Por Dimensiones</h5>
                                        <h3 class="card-text"><?= $total_dimensiones ?></h3>
                                        <p class="text-muted">Paquetes calculados por dimensiones</p>
                                    </div>
                                    <div class="display-5 opacity-30" style="color: var(--secondary-color);">
                                        <i class="fas fa-ruler-combined"></i>
                                    </div>
                                </div>
                                <div class="progress mt-2">
                                    <div class="progress-bar" role="progressbar"
                                        style="width: <?= $total_paquetes > 0 ? ($total_dimensiones / $total_paquetes) * 100 : 0; ?>%; background-color: var(--secondary-color);"
                                        aria-valuenow="<?= $total_dimensiones ?>"
                                        aria-valuemin="0"
                                        aria-valuemax="<?= $total_paquetes ?>"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Paquetes por Peso -->
                    <div class="col-md-4 mb-4">
                        <div class="card stats-card h-100">
                            <div class="card-body d-flex flex-column justify-content-between">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title text-muted">Por Peso</h5>
                                        <h3 class="card-text"><?= $total_peso ?></h3>
                                        <p class="text-muted">Paquetes calculados por peso</p>
                                    </div>
                                    <div class="display-5 opacity-30" style="color: var(--accent-color);">
                                        <i class="fas fa-weight-hanging"></i>
                                    </div>
                                </div>
                                <div class="progress mt-2">
                                    <div class="progress-bar" role="progressbar"
                                        style="width: <?= $total_paquetes > 0 ? ($total_peso / $total_paquetes) * 100 : 0; ?>%; background-color: var(--accent-color);"
                                        aria-valuenow="<?= $total_peso ?>"
                                        aria-valuemin="0"
                                        aria-valuemax="<?= $total_paquetes ?>"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Panel de administración -->
                <div class="card mb-4 card-hover">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="fas fa-cog me-2"></i>Panel de Administración</h4>
                    </div>
                    <div class="card-body">
                        <p class="card-text">Selecciona qué tipo de precios deseas configurar:</p>
                        <div class="d-grid gap-2 d-md-block">
                            <a href="admPreciosXDimen.php" class="btn btn-primary btn-lg me-2 mb-2">
                                <i class="fas fa-ruler-combined me-2"></i>Gestionar Precios por Dimensiones
                            </a>
                            <a href="admPreciosXPeso.php" class="btn btn-primary btn-lg mb-2">
                                <i class="fas fa-weight-hanging me-2"></i>Gestionar Precios por Peso
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Filtros de búsqueda -->
                <div class="filter-section card-hover">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="mb-0"><i class="fas fa-filter me-2"></i>Filtros de Búsqueda</h4>
                        <span class="filter-toggle" data-bs-toggle="collapse" data-bs-target="#filtrosCollapse">
                            <i class="fas fa-chevron-down"></i>
                        </span>
                    </div>

                    <!-- Filtros aplicados -->
                    <?php if (!empty($filtros)): ?>
                        <div class="applied-filters">
                            <h6 class="mb-2">Filtros aplicados:</h6>
                            <?php foreach ($filtros as $key => $value): ?>
                                <?php if (!empty($value) && $value !== 'todos'): ?>
                                    <span class="filter-badge">
                                        <?php
                                        $labels = [
                                            'tipo' => 'Tipo',
                                            'cliente' => 'Cliente',
                                            'fecha_inicio' => 'Desde',
                                            'fecha_fin' => 'Hasta',
                                            'precio_min' => 'Precio Mín',
                                            'precio_max' => 'Precio Máx'
                                        ];
                                        $displayValue = $value;
                                        if ($key === 'tipo') {
                                            $displayValue = $value === 'dimensiones' ? 'Dimensiones' : 'Peso';
                                        }
                                        echo $labels[$key] . ': ' . $displayValue;
                                        ?>
                                        <a href="?<?php echo http_build_query(array_diff_key($_GET, [$key => ''])); ?>" class="text-white ms-1">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    </span>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <a href="precios.php" class="btn btn-sm btn-outline-danger ms-2">
                                <i class="fas fa-times me-1"></i>Limpiar todos
                            </a>
                        </div>
                    <?php endif; ?>

                    <!-- Formulario de filtros -->
                    <div class="collapse show" id="filtrosCollapse">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Tipo de Paquete</label>
                                <select class="form-select" name="tipo">
                                    <option value="todos" <?php echo (!isset($filtros['tipo']) || $filtros['tipo'] === 'todos') ? 'selected' : ''; ?>>Todos los tipos</option>
                                    <option value="dimensiones" <?php echo (isset($filtros['tipo']) && $filtros['tipo'] === 'dimensiones') ? 'selected' : ''; ?>>Por Dimensiones</option>
                                    <option value="peso" <?php echo (isset($filtros['tipo']) && $filtros['tipo'] === 'peso') ? 'selected' : ''; ?>>Por Peso</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Cliente</label>
                                <input type="text" class="form-control" name="cliente" placeholder="Buscar cliente..." value="<?php echo $filtros['cliente'] ?? ''; ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Fecha Inicio</label>
                                <input type="date" class="form-control" name="fecha_inicio" value="<?php echo $filtros['fecha_inicio'] ?? ''; ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Fecha Fin</label>
                                <input type="date" class="form-control" name="fecha_fin" value="<?php echo $filtros['fecha_fin'] ?? ''; ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Precio Mínimo</label>
                                <input type="number" step="0.01" class="form-control" name="precio_min" placeholder="0.00" value="<?php echo $filtros['precio_min'] ?? ''; ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Precio Máximo</label>
                                <input type="number" step="0.01" class="form-control" name="precio_max" placeholder="0.00" value="<?php echo $filtros['precio_max'] ?? ''; ?>">
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-search me-1"></i> Buscar
                                </button>
                                <a href="precios.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-sync me-1"></i> Limpiar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Lista de paquetes enviados -->
                <div class="card card-hover">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="fas fa-list me-2"></i>Registro de Paquetes Enviados</h4>
                        <span class="badge bg-light text-primary"><?= $total_paquetes ?> registros</span>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($paquetes)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Cliente</th>
                                            <th>Destino</th>
                                            <th>Dimensiones/Peso</th>
                                            <th>Precio</th>
                                            <th>Fecha</th>
                                            <th>Tipo</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($paquetes as $p):
                                            $alto  = $p['alto']  ?? null;
                                            $largo = $p['largo'] ?? null;
                                            $ancho = $p['ancho'] ?? null;
                                            $peso  = $p['peso']  ?? null;

                                            $dims = ($alto || $largo || $ancho) ? ($alto . " × " . $largo . " × " . $ancho) : "-";
                                            $pesoTxt = $peso !== null && $peso !== '' ? $peso . " kg" : "-";
                                            $precio  = isset($p['precio']) ? number_format((float)$p['precio'], 2) : "0.00";
                                            $fecha   = $p['fecha_registro'] ?? "-";

                                            // Formatear fecha si existe
                                            if ($fecha !== "-" && $fecha !== "") {
                                                $fechaFormateada = date("d/m/Y", strtotime($fecha));
                                            } else {
                                                $fechaFormateada = "-";
                                            }
                                        ?>
                                            <tr>
                                                <td><strong>#<?= htmlspecialchars((string)$p['id']) ?></strong></td>
                                                <td><?= htmlspecialchars((string)($p['nombre_cliente'] ?? '-')) ?></td>
                                                <td><?= htmlspecialchars((string)($p['direccion_destino'] ?? '-')) ?></td>
                                                <td>
                                                    <?php if (($p['tipo'] ?? '') === 'Dimensiones'): ?>
                                                        <i class="fas fa-ruler-combined text-success me-1" title="Dimensiones"></i>
                                                        <?= htmlspecialchars($dims) ?>
                                                    <?php else: ?>
                                                        <i class="fas fa-weight-hanging text-info me-1" title="Peso"></i>
                                                        <?= htmlspecialchars($pesoTxt) ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="fw-bold">$<?= $precio ?></td>
                                                <td><?= htmlspecialchars((string)$fechaFormateada) ?></td>
                                                <td>
                                                    <span class="badge <?= ($p['tipo'] ?? '') === 'Dimensiones' ? 'badge-dimensiones' : 'badge-peso' ?>">
                                                        <?= htmlspecialchars((string)($p['tipo'] ?? '-')) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="d-flex">
                                                        <button class="btn btn-sm btn-info action-btn" onclick="mostrarDetalles(<?= htmlspecialchars(json_encode($p)) ?>, '<?= $precio ?>', '<?= $fechaFormateada ?>', '<?= $dims ?>', '<?= $pesoTxt ?>')">
                                                            <i class="fas fa-eye"></i>
                                                        </button>

                                                        <?php if (($p['tipo'] ?? '') === 'Dimensiones'): ?>
                                                            <a href="factDimensionesAdm.php?id=<?= $p['id'] ?>" target="_blank">
                                                                <button class="btn btn-sm print-btn action-btn" title="Imprimir Factura">
                                                                    <i class="fas fa-print"></i>
                                                                </button>
                                                            </a>
                                                        <?php else: ?>
                                                            <a href="factPesoAdm.php?id=<?= $p['id'] ?>" target="_blank">
                                                                <button class="btn btn-sm print-btn action-btn" title="Imprimir Factura">
                                                                    <i class="fas fa-print"></i>
                                                                </button>
                                                            </a>
                                                        <?php endif; ?>


                                                        <!-- Botón Eliminar -->
                                                        <a href="?eliminar=<?= $p['id'] ?>&tabla=<?= $p['tabla'] ?>"
                                                            class="btn btn-sm btn-danger action-btn"
                                                            onclick="return confirm('¿Estás seguro de que deseas eliminar este paquete? Esta acción no se puede deshacer.')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                                <h4 class="text-muted">No se encontraron paquetes</h4>
                                <p class="text-muted">
                                    <?php echo empty($filtros) ?
                                        'No hay paquetes registrados en el sistema.' :
                                        'No hay resultados que coincidan con los filtros aplicados.'; ?>
                                </p>
                                <?php if (!empty($filtros)): ?>
                                    <a href="precios.php" class="btn btn-primary mt-2">
                                        <i class="fas fa-sync me-1"></i> Limpiar filtros
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal único para detalles -->
    <div class="modal fade" id="detalleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalles del Paquete #<span id="modalId"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Cliente:</strong> <span id="modalCliente"></span></p>
                            <p><strong>Origen:</strong> <span id="modalOrigen"></span></p>
                            <p><strong>Destino:</strong> <span id="modalDestino"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Contenido:</strong> <span id="modalContenido"></span></p>
                            <p><strong>Precio:</strong> $<span id="modalPrecio"></span></p>
                            <p><strong>Fecha:</strong> <span id="modalFecha"></span></p>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6>Especificaciones:</h6>
                            <p id="modalDimensiones"><strong>Dimensiones:</strong> <span id="modalDimsVal"></span></p>
                            <p id="modalPeso"><strong>Peso:</strong> <span id="modalPesoVal"></span></p>
                            <p><strong>Rango:</strong> <span id="modalRango"></span></p>
                            <p><strong>Tipo de cálculo:</strong> <span id="modalTipo"></span></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" onclick="imprimirDetalles()">Imprimir</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script src="../../js/pages/paquetes/scriptPrecios.js"></script>
</body>

</html>