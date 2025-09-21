<?php

session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit();
}
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "enviosdb";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Procesar actualización de precios
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $minimo = $_POST['minimo'];
    $maximo = $_POST['maximo'];
    $precio = $_POST['precio'];

    $sql = "UPDATE rangoxdimen SET nombre='$nombre', minimo='$minimo', maximo='$maximo', precio_por_unidad='$precio' WHERE id=$id";
    if ($conn->query($sql)) {
        $mensaje_exito = "¡Los cambios se guardaron correctamente!";
    } else {
        $mensaje_error = "Error al guardar los cambios: " . $conn->error;
    }
}

// Obtener datos de la base de datos
$result = $conn->query("SELECT * FROM rangoxdimen ORDER BY minimo ASC");
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>SOTRA Magdalena - Administración de Precios</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/pages/paquetes/styleAdmPreciosXDimen.css">
</head>

<body>
    <nav class="navbar navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                Sotra Magdalena - Gestion de precios por Dimensiones
            </a>
            <a href="../login/logout.php" class="btn btn-light">
                <i class="fas fa-sign-out-alt me-1"></i>Cerrar sesión
            </a>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <div class="list-group">
                    <a href="../dashboardAdmin.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                    <a href="../usuarios/usuarios.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-users me-2"></i>Usuarios
                    </a>
                    <a href="precios.php" class="list-group-item list-group-item-action active">
                        <i class="fas fa-box me-2"></i>Paquetes
                    </a>
                    <a href="../proveedores/proveedores.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-truck me-2"></i>Proveedores
                    </a>
                    <a href="../reportes/reportes.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-chart-bar me-2"></i>Reportes
                    </a>
                </div>
            </div>

            <!-- Contenido principal -->
            <div class="col-md-9">
                <div class="dashboard-header">
                    <h1>
                        <i class="fas fa-money-bill-wave me-2"></i>Administración de Precios
                    </h1>
                </div>

                <!-- Alertas de éxito/error -->
                <?php if (isset($mensaje_exito)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo $mensaje_exito; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($mensaje_error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $mensaje_error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Tarjeta informativa -->
                <div class="card info-card mb-4 card-hover">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-info-circle me-2"></i>¿Cómo funcionan los precios por dimensiones?
                        </h5>
                        <p class="card-text">
                            Establece rangos de dimensiones (en centímetros) y el precio correspondiente por unidad.
                            El sistema calculará automáticamente el costo según las dimensiones del paquete.
                        </p>
                    </div>
                </div>

                <!-- Selector de tipo de precio -->
                <div class="card mb-4 card-hover">
                    <div class="card-body text-center">
                        <h4 class="card-title mb-3">Selecciona el tipo de precios a administrar</h4>
                        <div class="d-flex justify-content-center flex-wrap">
                            <a href="admPreciosXDimen.php" class="btn btn-primary btn-tab mb-2">
                                <i class="fas fa-ruler-combined me-2"></i>Por Dimensiones
                            </a>
                            <a href="admPreciosXPeso.php" class="btn btn-outline-primary btn-tab mb-2">
                                <i class="fas fa-weight-hanging me-2"></i>Por Peso
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Tabla de precios -->
                <div class="card price-table card-hover">
                    <div class="card-header">
                        <h4 class="mb-0">
                            <i class="fas fa-table me-2"></i>Rangos de Precios por Dimensiones (cm)
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="25%">Nombre del Rango</th>
                                        <th width="15%">Mínimo (cm)</th>
                                        <th width="15%">Máximo (cm)</th>
                                        <th width="20%">Precio por Unidad ($)</th>
                                        <th width="25%">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result->num_rows > 0): ?>
                                        <?php while ($row = $result->fetch_assoc()): ?>
                                            <form method="POST">
                                                <tr>
                                                    <td>
                                                        <input type="text" name="nombre" value="<?php echo htmlspecialchars($row['nombre']); ?>"
                                                            placeholder="Ej: Pequeño, Mediano..." required>
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.01" name="minimo"
                                                            value="<?php echo htmlspecialchars($row['minimo']); ?>" min="0" required>
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.01" name="maximo"
                                                            value="<?php echo htmlspecialchars($row['maximo']); ?>" min="0" required>
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.01" name="precio"
                                                            value="<?php echo htmlspecialchars($row['precio_por_unidad']); ?>" min="0" required>
                                                    </td>
                                                    <td>
                                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                        <button type="submit" class="btn btn-save">
                                                            <i class="fas fa-save me-1"></i>Guardar
                                                        </button>
                                                    </td>
                                                </tr>
                                            </form>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4">
                                                <i class="fas fa-exclamation-circle fa-2x text-muted mb-3"></i>
                                                <h5 class="text-muted">No hay rangos de precios configurados</h5>
                                                <p class="text-muted">Comienza agregando nuevos rangos de precios.</p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <small class="text-muted">
                            <i class="fas fa-lightbulb me-1"></i>Tip: Asegúrate de que los rangos no se superpongan para un cálculo preciso.
                        </small>
                    </div>
                </div>

                <!-- Información adicional -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card card-hover">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-question-circle me-2"></i>¿Necesitas ayuda?
                                </h5>
                                <p class="card-text">
                                    Si tienes dudas sobre cómo configurar los precios, contacta al administrador del sistema.
                                </p>
                                <a href="#" class="btn btn-outline-primary">
                                    <i class="fas fa-envelope me-1"></i>Contactar soporte
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../js/pages/paquetes/scriptAdmPreciosXDimen.js"></script>
</body>

</html>
<?php $conn->close(); ?>