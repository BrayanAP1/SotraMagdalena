<?php
session_start();

//PROTECCIÓN DE PÁGINA
if (!isset($_SESSION['id'])) {
    header("Location: /Sotramagdalena/index.php");
    exit();
}

//CONEXIÓN
$host = "localhost";
$user = "root";
$pass = "";
$db = "enviosdb";
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

//AGREGAR PROVEEDOR
if (isset($_POST['guardar'])) {
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $direccion = $conn->real_escape_string($_POST['direccion']);
    $telefono = $conn->real_escape_string($_POST['telefono']);
    $correo = $conn->real_escape_string($_POST['correo']);

    $sql = "INSERT INTO proveedores (nombre, direccion, telefono, correo, fecha_registro) 
            VALUES ('$nombre','$direccion','$telefono','$correo', NOW())";

    if ($conn->query($sql)) {
        $_SESSION['mensaje'] = "Proveedor agregado correctamente";
        $_SESSION['tipo_mensaje'] = "success";
    } else {
        $_SESSION['mensaje'] = "Error al agregar proveedor: " . $conn->error;
        $_SESSION['tipo_mensaje'] = "error";
    }

    header("Location: dashboardProveedores.php");
    exit();
}

//ELIMINAR PROVEEDOR
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);

    if ($conn->query("DELETE FROM proveedores WHERE id=$id")) {
        $_SESSION['mensaje'] = "Proveedor eliminado correctamente";
        $_SESSION['tipo_mensaje'] = "success";
    } else {
        $_SESSION['mensaje'] = "Error al eliminar proveedor: " . $conn->error;
        $_SESSION['tipo_mensaje'] = "error";
    }

    header("Location: dashboardProveedores.php");
    exit();
}

//EDITAR PROVEEDOR
if (isset($_POST['actualizar'])) {
    $id = intval($_POST['id']);
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $direccion = $conn->real_escape_string($_POST['direccion']);
    $telefono = $conn->real_escape_string($_POST['telefono']);
    $correo = $conn->real_escape_string($_POST['correo']);

    $sql = "UPDATE proveedores SET 
            nombre='$nombre', direccion='$direccion', telefono='$telefono', correo='$correo'
            WHERE id=$id";

    if ($conn->query($sql)) {
        $_SESSION['mensaje'] = "Proveedor actualizado correctamente";
        $_SESSION['tipo_mensaje'] = "success";
    } else {
        $_SESSION['mensaje'] = "Error al actualizar proveedor: " . $conn->error;
        $_SESSION['tipo_mensaje'] = "error";
    }

    header("Location: dashboardProveedores.php");
    exit();
}

//OBTENER LISTADO DE PROVEEDORES
$result = $conn->query("SELECT * FROM proveedores ORDER BY nombre ASC");

//PARA EDITAR PROVEEDOR
$proveedorEditar = null;
if (isset($_GET['editar'])) {
    $id = intval($_GET['editar']);
    $res = $conn->query("SELECT * FROM proveedores WHERE id=$id");

    if ($res && $res->num_rows > 0) {
        $proveedorEditar = $res->fetch_assoc();
    }
}

//DATOS PARA GRÁFICOS
// Gráfico 1: Proveedores por mes
$grafico = $conn->query("
    SELECT MONTH(fecha_registro) as mes, COUNT(*) as total 
    FROM proveedores 
    WHERE fecha_registro IS NOT NULL
    GROUP BY MONTH(fecha_registro)
    ORDER BY mes
");

$meses = [];
$totales = [];
$nombresMeses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

if ($grafico) {
    while ($row = $grafico->fetch_assoc()) {
        $mesNumero = intval($row['mes']);
        if ($mesNumero >= 1 && $mesNumero <= 12) {
            $meses[] = $nombresMeses[$mesNumero - 1];
            $totales[] = $row['total'];
        }
    }
}

// Si no hay datos, mostrar datos de ejemplo para el gráfico
if (empty($meses)) {
    $meses = ['Ene', 'Feb', 'Mar', 'Abr'];
    $totales = [2, 5, 3, 7];
}

// Estadísticas
$totalProveedores = $result->num_rows;
$proveedoresRecientes = $conn->query("
    SELECT COUNT(*) as recientes 
    FROM proveedores 
    WHERE fecha_registro >= DATE_SUB(NOW(), INTERVAL 30 DAY)
")->fetch_assoc();
$proveedoresRecientes = $proveedoresRecientes['recientes'] ?? 0;

// Cerrar conexión
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Proveedores - SOTRA Magdalena</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- SheetJS para Excel -->
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <!-- jsPDF para PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
    <link rel="stylesheet" href="../css/pages/styleDashboardProveedores.css">
</head>

<body>
    <div class="container">
        <div class="header">
            <div style="position: relative;">
                <h1><i class="fas fa-truck"></i> Gestión de Proveedores - SOTRA Magdalena</h1>
                <p>Administración completa del listado de proveedores</p>

                <!-- Botón de Cerrar Sesión -->
                <div style="position: absolute; top: 0; right: 0;">
                    <a href="../softwares.php" class="btn btn-exit">
                        <i class="fas fa-sign-out-alt"></i>
                        Salir
                    </a>
                </div>
            </div>
        </div>

        <!-- Mostrar mensajes -->
        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="alert alert-<?php echo $_SESSION['tipo_mensaje'] === 'success' ? 'success' : 'error'; ?>">
                <i class="fas <?php echo $_SESSION['tipo_mensaje'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                <?php echo $_SESSION['mensaje']; ?>
            </div>
        <?php
            unset($_SESSION['mensaje']);
            unset($_SESSION['tipo_mensaje']);
        endif; ?>

        <!-- ESTADÍSTICAS RÁPIDAS -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= $totalProveedores ?></div>
                <div class="stat-label">Total Proveedores</div>
            </div>
            <div class="stat-card secondary">
                <div class="stat-number"><?= $proveedoresRecientes ?></div>
                <div class="stat-label">Nuevos (30 días)</div>
            </div>
        </div>

        <!-- FORMULARIO -->
        <div class="card">
            <h2 class="card-title">
                <i class="fas fa-<?= $proveedorEditar ? 'edit' : 'plus-circle' ?>"></i>
                <?= $proveedorEditar ? 'Editar Proveedor' : 'Agregar Nuevo Proveedor' ?>
            </h2>

            <form method="POST" action="" id="proveedorForm">
                <?php if ($proveedorEditar) { ?>
                    <input type="hidden" name="id" value="<?= $proveedorEditar['id'] ?>">
                <?php } ?>

                <div class="form-grid">
                    <div class="form-group">
                        <input type="text" name="nombre" class="form-input"
                            value="<?= $proveedorEditar ? htmlspecialchars($proveedorEditar['nombre']) : '' ?>"
                            placeholder="Nombre del proveedor" required>
                        <i class="fas fa-user form-icon"></i>
                    </div>
                    <div class="form-group">
                        <input type="text" name="direccion" class="form-input"
                            value="<?= $proveedorEditar ? htmlspecialchars($proveedorEditar['direccion']) : '' ?>"
                            placeholder="Dirección completa" required>
                        <i class="fas fa-map-marker-alt form-icon"></i>
                    </div>
                    <div class="form-group">
                        <input type="text" name="telefono" class="form-input"
                            value="<?= $proveedorEditar ? htmlspecialchars($proveedorEditar['telefono']) : '' ?>"
                            placeholder="Número de teléfono" required>
                        <i class="fas fa-phone form-icon"></i>
                    </div>
                    <div class="form-group">
                        <input type="email" name="correo" class="form-input"
                            value="<?= $proveedorEditar ? htmlspecialchars($proveedorEditar['correo']) : '' ?>"
                            placeholder="Correo electrónico" required>
                        <i class="fas fa-envelope form-icon"></i>
                    </div>
                </div>

                <div style="display: flex; gap: 0.8rem; flex-wrap: wrap;">
                    <button type="submit" name="<?= $proveedorEditar ? 'actualizar' : 'guardar' ?>" class="btn btn-primary">
                        <i class="fas fa-<?= $proveedorEditar ? 'save' : 'plus' ?>"></i>
                        <?= $proveedorEditar ? 'Actualizar' : 'Guardar' ?>
                    </button>

                    <?php if ($proveedorEditar) { ?>
                        <a href="dashboardProveedores.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i>
                            Cancelar
                        </a>
                    <?php } ?>
                </div>
            </form>
        </div>

        <!-- LISTADO DE PROVEEDORES -->
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap;">
                <h2 class="card-title" style="margin-bottom: 0;">
                    <i class="fas fa-list"></i>
                    Listado de Proveedores
                </h2>
                <div class="export-buttons">
                    <a href="#" class="btn-export" onclick="exportToPDF()">
                        <i class="fas fa-file-pdf"></i> PDF
                    </a>
                    <a href="#" class="btn-export" onclick="exportToExcel()">
                        <i class="fas fa-file-excel"></i> Excel
                    </a>
                </div>
            </div>

            <div class="table-container">
                <table class="table" id="tablaProveedores">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Dirección</th>
                            <th>Teléfono</th>
                            <th>Correo</th>
                            <th>Fecha Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                        ?>
                                <tr class="fade-in-up">
                                    <td><strong>#<?= $row['id'] ?></strong></td>
                                    <td><?= htmlspecialchars($row['nombre']) ?></td>
                                    <td><?= htmlspecialchars($row['direccion']) ?></td>
                                    <td><?= htmlspecialchars($row['telefono']) ?></td>
                                    <td><?= htmlspecialchars($row['correo']) ?></td>
                                    <td><?= $row['fecha_registro'] ? date('d/m/Y', strtotime($row['fecha_registro'])) : 'N/A' ?></td>
                                    <td>
                                        <div class="actions">
                                            <a href="dashboardProveedores.php?editar=<?= $row['id'] ?>"
                                                class="btn btn-secondary btn-sm"
                                                title="Editar proveedor">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="dashboardProveedores.php?eliminar=<?= $row['id'] ?>"
                                                class="btn btn-danger btn-sm"
                                                title="Eliminar proveedor"
                                                onclick="return confirm('¿Estás seguro de eliminar este proveedor?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                                    <i class="fas fa-truck-loading" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                                    No hay proveedores registrados
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- GRÁFICO DE PROVEEDORES POR MES -->
        <div class="chart-wrapper">
            <h3 class="chart-title">
                <i class="fas fa-chart-line"></i>
                Proveedores Registrados por Mes
            </h3>
            <canvas id="graficoProveedoresMes"></canvas>
        </div>
    </div>

    <script>
        const meses = <?= json_encode($meses) ?>;
        const totales = <?= json_encode($totales) ?>;
    </script>
    <script src="../js/pages/scriptDashboardProveedores.js"></script>
</body>

</html>