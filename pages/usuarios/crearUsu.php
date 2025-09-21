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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $rol = $_POST['rol'];
    
    // Validaciones
    $errores = [];
    
    if (empty($nombre)) {
        $errores['nombre'] = "El nombre es obligatorio";
    }
    
    if (empty($username)) {
        $errores['username'] = "El usuario es obligatorio";
    } else {
        // Verificar si el usuario ya existe
        $check_user = "SELECT id FROM usuarios WHERE username = '$username'";
        $result = $conn->query($check_user);
        if ($result->num_rows > 0) {
            $errores['username'] = "Este nombre de usuario ya está en uso";
        }
    }
    
    if (empty($password)) {
        $errores['password'] = "La contraseña es obligatoria";
    } elseif (strlen($password) < 6) {
        $errores['password'] = "La contraseña debe tener al menos 6 caracteres";
    }
    
    if ($password !== $confirm_password) {
        $errores['confirm_password'] = "Las contraseñas no coinciden";
    }
    
    // Si no hay errores, proceder con el registro
    if (empty($errores)) {
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        $sql = "INSERT INTO usuarios (nombre, username, password, rol) VALUES ('$nombre', '$username', '$password_hash', '$rol')";
        
        if ($conn->query($sql)) {
            $_SESSION['mensaje_exito'] = "Usuario creado exitosamente";
            header("Location: usuarios.php");
            exit();
        } else {
            $error_general = "Error al crear el usuario: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SOTRA Magdalena - Crear Usuario</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../../css/pages/usuarios/styleCrearUsu.css">
</head>
<body>

<nav class="navbar navbar-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">
      Sotra Magdalena - Crear Usuario
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
            Crea nuevos usuarios para gestionar el sistema. Los administradores tienen acceso completo, mientras que los usuarios tienen permisos limitados.
          </p>
          <div class="alert alert-warning small mb-0">
            <i class="fas fa-exclamation-triangle me-1"></i> Las contraseñas deben tener al menos 6 caracteres.
          </div>
        </div>
      </div>
    </div>

    <!-- Contenido principal -->
    <div class="col-md-9">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-user-plus me-2"></i>Crear Nuevo Usuario</h2>
        <a href="usuarios.php" class="btn btn-secondary">
          <i class="fas fa-arrow-left me-1"></i> Volver a Usuarios
        </a>
      </div>

      <?php if (isset($error_general)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <i class="fas fa-exclamation-circle me-2"></i><?php echo $error_general; ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <div class="form-container">
        <form method="POST" id="formUsuario">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="nombre" class="form-label">Nombre Completo</label>
              <input type="text" name="nombre" id="nombre" class="form-control <?php echo isset($errores['nombre']) ? 'is-invalid' : ''; ?>" 
                     value="<?php echo isset($nombre) ? htmlspecialchars($nombre) : ''; ?>" required>
              <?php if (isset($errores['nombre'])): ?>
                <div class="invalid-feedback"><?php echo $errores['nombre']; ?></div>
              <?php endif; ?>
            </div>
            
            <div class="col-md-6 mb-3">
              <label for="username" class="form-label">Nombre de Usuario</label>
              <input type="text" name="username" id="username" class="form-control <?php echo isset($errores['username']) ? 'is-invalid' : ''; ?>" 
                     value="<?php echo ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($username)) ? htmlspecialchars($username) : ''; ?>" required>
              <?php if (isset($errores['username'])): ?>
                <div class="invalid-feedback"><?php echo $errores['username']; ?></div>
              <?php endif; ?>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="password" class="form-label">Contraseña</label>
              <div class="position-relative">
                <input type="password" name="password" id="password" class="form-control <?php echo isset($errores['password']) ? 'is-invalid' : ''; ?>" required>
                <span class="password-toggle" onclick="togglePassword('password')">
                  <i class="fas fa-eye"></i>
                </span>
              </div>
              <?php if (isset($errores['password'])): ?>
                <div class="invalid-feedback"><?php echo $errores['password']; ?></div>
              <?php endif; ?>
              <div class="form-text">Mínimo 6 caracteres</div>
            </div>
            
            <div class="col-md-6 mb-3">
              <label for="confirm_password" class="form-label">Confirmar Contraseña</label>
              <div class="position-relative">
                <input type="password" name="confirm_password" id="confirm_password" class="form-control <?php echo isset($errores['confirm_password']) ? 'is-invalid' : ''; ?>" required>
                <span class="password-toggle" onclick="togglePassword('confirm_password')">
                  <i class="fas fa-eye"></i>
                </span>
              </div>
              <?php if (isset($errores['confirm_password'])): ?>
                <div class="invalid-feedback"><?php echo $errores['confirm_password']; ?></div>
              <?php endif; ?>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-4">
              <label for="rol" class="form-label">Rol</label>
              <select name="rol" id="rol" class="form-select">
                <option value="usuario" <?php echo (isset($rol) && $rol == 'usuario') ? 'selected' : ''; ?>>Usuario</option>
                <option value="administrador" <?php echo (isset($rol) && $rol == 'administrador') ? 'selected' : ''; ?>>Administrador</option>
              </select>
            </div>
          </div>
          
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-success">
              <i class="fas fa-save me-1"></i> Guardar Usuario
            </button>
            <a href="usuarios.php" class="btn btn-secondary">
              <i class="fas fa-times me-1"></i> Cancelar
            </a>
            <button type="reset" class="btn btn-outline-secondary">
              <i class="fas fa-undo me-1"></i> Limpiar
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../js/pages/usuarios/scriptCrearUsu.js"></script>
</body>
</html>