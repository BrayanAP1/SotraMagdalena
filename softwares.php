<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: /Sotramagdalena/index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Selección de Software - SotraMagdalena S.A.</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="css/styleSoftware.css">
</head>
<body>

<div class="container">
  <!-- Sección izquierda (Bienvenida) -->
  <div class="welcome-section">
    <!-- Botón de salida -->
    <a href="login/logout.php" class="exit-button" title="Cerrar sesión">
      <i class="fas fa-sign-out-alt"></i>
    </a>
    
    <div class="welcome-content">
      <img src="img/logo.png" alt="SotraMagdalena S.A." class="logo" onerror="this.src='data:image/svg+xml;charset=utf-8,%3Csvg xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22 width%3D%22180%22 height%3D%22180%22 viewBox%3D%220 0 180 180%22%3E%3Crect width%3D%22180%22 height%3D%22180%22 fill%3D%22%23f0f0f0%22%2F%3E%3Ctext x%3D%2290%22 y%3D%2290%22 font-family%3D%22Arial%22 font-size%3D%2218%22 text-anchor%3D%22middle%22 dominant-baseline%3D%22middle%22%3ELogo%3C%2Ftext%3E%3C%2Fsvg%3E'">
      
      <div class="user-welcome">
        <i class="fas fa-user-circle"></i>
        <span>Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
      </div>
      
      <h2>Digitaliza tu operación con SotraMagdalena S.A.</h2>
      <p>Selecciona el módulo que necesitas para optimizar tus procesos logísticos y de transporte. Nuestras soluciones tecnológicas están diseñadas para brindarte eficiencia y control total.</p>
      
      <div style="margin-top: 30px; animation: fadeInUp 1s ease-out 0.3s both;">
        <span style="color: var(--text-medium);">Selecciona una opción al lado</span>
        <i class="fas fa-arrow-right" style="color: var(--primary-color); margin-left: 5px;"></i>
      </div>
    </div>
  </div>

  <!-- Sección derecha (Selección de software) -->
  <div class="selection-section">
    <div class="selection-box">
      <h2>Selecciona el software a usar</h2>
      
      <div class="software-options">
        <div class="software-card proveedores" onclick="selectSoftware('proveedores')">
          <div class="software-icon">
            <i class="fas fa-users-cog"></i>
          </div>
          <div class="software-info">
            <h3>Gestión de Proveedores</h3>
            <p>Control y optimización de relaciones con proveedores y contratistas</p>
          </div>
        </div>
        
        <div class="software-card envios" onclick="selectSoftware('envios')">
          <div class="software-icon">
            <i class="fas fa-truck-fast"></i>
          </div>
          <div class="software-info">
            <h3>Automatización de Envíos</h3>
            <p>Sistema inteligente para gestión y seguimiento de paquetería</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="js/scriptSoftwares.js"></script>
</body>
</html>