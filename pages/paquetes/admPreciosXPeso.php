<?php

session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../index.php"); // Redirige al login si no hay sesión
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

// Procesar formularios
$mensaje_exito = "";
$mensaje_error = "";

// Guardar nuevo rango
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nuevo_rango'])) {
    $nombre = $_POST['rango_nombre'];
    $min = $_POST['peso_min'];
    $max = $_POST['peso_max'];
    $precio = $_POST['precio_kg'];

    // Validar que el mínimo sea menor que el máximo
    if ($min >= $max) {
        $mensaje_error = "El peso mínimo debe ser menor que el peso máximo";
    } else {
        $sql = "INSERT INTO rangoxpeso (rango_nombre, peso_min, peso_max, precio_kg)
                VALUES ('$nombre', '$min', '$max', '$precio')";
        if ($conn->query($sql)) {
            $mensaje_exito = "¡Nuevo rango de peso agregado correctamente!";
        } else {
            $mensaje_error = "Error al agregar el rango: " . $conn->error;
        }
    }
}

// Actualizar rango existente
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['actualizar_rango'])) {
    $id = $_POST['id'];
    $nombre = $_POST['rango_nombre'];
    $min = $_POST['peso_min'];
    $max = $_POST['peso_max'];
    $precio = $_POST['precio_kg'];

    if ($min >= $max) {
        $mensaje_error = "El peso mínimo debe ser menor que el peso máximo";
    } else {
        $sql = "UPDATE rangoxpeso SET rango_nombre='$nombre', peso_min='$min', 
                peso_max='$max', precio_kg='$precio' WHERE id=$id";
        if ($conn->query($sql)) {
            $mensaje_exito = "¡Rango de peso actualizado correctamente!";
        } else {
            $mensaje_error = "Error al actualizar el rango: " . $conn->error;
        }
    }
}

// Eliminar rango
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $sql = "DELETE FROM rangoxpeso WHERE id=$id";
    if ($conn->query($sql)) {
        $mensaje_exito = "¡Rango de peso eliminado correctamente!";
    } else {
        $mensaje_error = "Error al eliminar el rango: " . $conn->error;
    }
}

// Obtener rangos existentes
$result = $conn->query("SELECT * FROM rangoxpeso ORDER BY peso_min ASC");
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>SOTRA Magdalena - Precios por Peso</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/pages/paquetes/styleAdmPreciosXPeso.css">
</head>

<body>
    <nav class="navbar navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                Sotra Magdalena - Gestion de precios por Peso
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
                <?php if (!empty($mensaje_exito)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo $mensaje_exito; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($mensaje_error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $mensaje_error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Tarjeta informativa -->
                <div class="card info-card mb-4 card-hover">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-info-circle me-2"></i>¿Cómo funcionan los precios por peso?
                        </h5>
                        <p class="card-text">
                            Establece rangos de peso (en kilogramos) y el precio correspondiente por kilogramo.
                            El sistema calculará automáticamente el costo según el peso del paquete.
                        </p>
                    </div>
                </div>

                <!-- Selector de tipo de precio -->
                <div class="card mb-4 card-hover">
                    <div class="card-body text-center">
                        <h4 class="card-title mb-3">Selecciona el tipo de precios a administrar</h4>
                        <div class="d-flex justify-content-center flex-wrap">
                            <a href="admPreciosXDimen.php" class="btn btn-outline-primary btn-tab mb-2">
                                <i class="fas fa-ruler-combined me-2"></i>Por Dimensiones
                            </a>
                            <a href="admPreciosXPeso.php" class="btn btn-primary btn-tab mb-2">
                                <i class="fas fa-weight-hanging me-2"></i>Por Peso
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Formulario para agregar nuevo rango -->
                <div class="card form-container card-hover">
                    <h4 class="mb-3">
                        <i class="fas fa-plus-circle me-2"></i>Agregar Nuevo Rango de Peso
                    </h4>
                    <form method="POST">
                        <input type="hidden" name="nuevo_rango" value="1">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="rango_nombre" class="form-label">Nombre del Rango</label>
                                <input type="text" class="form-control" id="rango_nombre" name="rango_nombre"
                                    placeholder="Ej: Liviano, Medio, Pesado" required>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="peso_min" class="form-label">Peso Mínimo (kg)</label>
                                <input type="number" step="0.01" class="form-control" id="peso_min" name="peso_min"
                                    min="0" placeholder="0.00" required>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="peso_max" class="form-label">Peso Máximo (kg)</label>
                                <input type="number" step="0.01" class="form-control" id="peso_max" name="peso_max"
                                    min="0" placeholder="0.00" required>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="precio_kg" class="form-label">Precio por kg ($)</label>
                                <input type="number" step="0.01" class="form-control" id="precio_kg" name="precio_kg"
                                    min="0" placeholder="0.00" required>
                            </div>
                            <div class="col-md-3 mb-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fas fa-save me-1"></i>Agregar Rango
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Tabla de precios -->
                <div class="card price-table card-hover">
                    <div class="card-header">
                        <h4 class="mb-0">
                            <i class="fas fa-table me-2"></i>Rangos de Precios por Peso (kg)
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php if ($result->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th width="25%">Nombre del Rango</th>
                                            <th width="15%">Peso Mínimo (kg)</th>
                                            <th width="15%">Peso Máximo (kg)</th>
                                            <th width="20%">Precio por kg ($)</th>
                                            <th width="25%">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $result->fetch_assoc()): ?>
                                            <form method="POST">
                                                <input type="hidden" name="actualizar_rango" value="1">
                                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                <tr>
                                                    <td>
                                                        <input type="text" class="form-control" name="rango_nombre"
                                                            value="<?php echo htmlspecialchars($row['rango_nombre']); ?>" required>
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.01" class="form-control" name="peso_min"
                                                            value="<?php echo htmlspecialchars($row['peso_min']); ?>" min="0" required>
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.01" class="form-control" name="peso_max"
                                                            value="<?php echo htmlspecialchars($row['peso_max']); ?>" min="0" required>
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.01" class="form-control" name="precio_kg"
                                                            value="<?php echo htmlspecialchars($row['precio_kg']); ?>" min="0" required>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex">
                                                            <button type="submit" class="btn btn-save me-2">
                                                                <i class="fas fa-save me-1"></i>Actualizar
                                                            </button>
                                                            <a href="?eliminar=<?php echo $row['id']; ?>"
                                                                class="btn btn-danger"
                                                                onclick="return confirm('¿Estás seguro de que deseas eliminar este rango?')">
                                                                <i class="fas fa-trash me-1"></i>Eliminar
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </form>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-exclamation-circle fa-2x text-muted mb-3"></i>
                                <h5 class="text-muted">No hay rangos de precios configurados</h5>
                                <p class="text-muted">Utiliza el formulario superior para agregar nuevos rangos de precios por peso.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-light">
                        <small class="text-muted">
                            <i class="fas fa-lightbulb me-1"></i>Tip: Asegúrate de que los rangos de peso no se superpongan para un cálculo preciso.
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
                                    Si tienes dudas sobre cómo configurar los precios por peso, contacta al administrador del sistema.
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
    <script src="../../js/pages/paquetes/scriptAdmPreciosXPeso.js"></script>
</body>

</html>
<?php $conn->close(); ?>