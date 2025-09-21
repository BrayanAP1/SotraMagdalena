<?php include("conexion.php"); ?>
<?php
// Verificar si el usuario tiene permisos de administrador
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'administrador') {
    header("Location: ../login.php");
    exit();
}

$id = $_GET['id'];
$sql = "SELECT * FROM usuarios WHERE id=$id";
$result = $conn->query($sql);

// Verificar si el usuario existe
if ($result->num_rows == 0) {
    header("Location: usuarios.php?error=Usuario no encontrado");
    exit();
}

$usuario = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $username = $_POST['username'];
    $rol = $_POST['rol'];
    $estado = isset($_POST['estado']) ? 1 : 0;

    // Validación básica
    if (empty($nombre) || empty($username)) {
        $error = "Todos los campos obligatorios deben ser completados";
    } else {
        $sql = "UPDATE usuarios SET nombre='$nombre', username='$username', rol='$rol', estado=$estado WHERE id=$id";
        if ($conn->query($sql)) {
            header("Location: usuarios.php?success=Usuario actualizado correctamente");
            exit();
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SOTRA Magdalena - Editar Usuario</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../../css/pages/usuarios/styleEditarUsu.css">
</head>
<body>

<nav class="navbar navbar-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">
      Sotra Magdalena - Editar Usuario
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
    <div class="col-md-3 col-lg-2">
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
            Aquí puedes editar la información del usuario. Asegúrate de asignar roles adecuados para mantener la seguridad del sistema.
          </p>
        </div>
      </div>
    </div>

    <!-- Contenido principal -->
    <div class="col-md-9 col-lg-10">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="usuarios.php" class="btn btn-outline-primary back-button">
          <i class="fas fa-arrow-left me-1"></i> Volver a usuarios
        </a>
        <h2 class="mb-0">Editar Usuario</h2>
        <div></div>
      </div>
      
      <!-- Alertas -->
      <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>
      
      <div class="form-container">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title mb-0"><i class="fas fa-user-edit me-2"></i>Información del usuario</h5>
          </div>
          <div class="card-body">
            <div class="user-avatar">
              <i class="fas fa-user"></i>
            </div>
            
            <form method="POST">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="nombre" class="form-label">Nombre completo <span class="text-danger">*</span></label>
                  <input type="text" id="nombre" name="nombre" class="form-control" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
                  <div class="form-text">Nombre real del usuario</div>
                </div>
                
                <div class="col-md-6 mb-3">
                  <label for="username" class="form-label">Nombre de usuario <span class="text-danger">*</span></label>
                  <input type="text" id="username" name="username" class="form-control" value="<?= htmlspecialchars($usuario['username']) ?>" required>
                  <div class="form-text">Identificador único para iniciar sesión</div>
                </div>
              </div>
              
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="rol" class="form-label">Rol de usuario <span class="text-danger">*</span></label>
                  <select id="rol" name="rol" class="form-select">
                    <option value="usuario" <?= $usuario['rol']=='usuario'?'selected':'' ?>>Usuario</option>
                    <option value="administrador" <?= $usuario['rol']=='administrador'?'selected':'' ?>>Administrador</option>
                  </select>
                  <div class="form-text">Define los permisos del usuario en el sistema</div>
                </div>
                
                <div class="col-md-6 mb-3">
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="estado" name="estado" <?= $usuario['estado']?'checked':'' ?>>
                    <label class="form-check-label" for="estado">Usuario activo</label>
                  </div>
                  <div class="form-text">Desactiva para restringir el acceso</div>
                </div>
              </div>
              
              <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                <a href="usuarios.php" class="btn btn-outline-secondary me-md-2">
                  <i class="fas fa-times me-1"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-save me-1"></i> Actualizar usuario
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
      
      <!-- Información adicional -->
      <div class="row mt-4">
        <div class="col-md-6">
          <div class="card">
            <div class="card-header bg-info text-white">
              <h6 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Consejos</h6>
            </div>
            <div class="card-body">
              <ul class="mb-0">
                <li>Asigna el rol de administrador solo a usuarios de confianza</li>
                <li>Mantén desactivados los usuarios que ya no necesiten acceso</li>
                <li>Verifica que el nombre de usuario sea único</li>
              </ul>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card">
            <div class="card-header bg-secondary text-white">
              <h6 class="mb-0"><i class="fas fa-history me-2"></i>Información del sistema</h6>
            </div>
            <div class="card-body">
              <p class="mb-1"><strong>ID de usuario:</strong> <?= $usuario['id'] ?></p>
              <p class="mb-1"><strong>Última actualización:</strong> <?= date('d/m/Y H:i') ?></p>
              <p class="mb-0"><strong>Editado por:</strong> <?= $_SESSION['nombre'] ?? 'Administrador' ?></p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>