<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: /Sotramagdalena/index.php");
    exit();
}

// Conexión
$conn = new mysqli("localhost", "root", "", "enviosdb");
$result = $conn->query("SELECT * FROM rangoxpeso ORDER BY peso_min ASC");
$rangos = [];
while ($row = $result->fetch_assoc()) {
    $rangos[] = $row;
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Registrar Envío por Peso - SOTRA Magdalena</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../../css/pages/calcPrecio/stylePeso.css">
</head>

<body>

  <div class="overlay">
    <div class="top-bar">
      <button onclick="window.location.href='../dashboardPrecios.php'">
        <i class="fas fa-sign-out-alt"></i> SALIR
      </button>
      <div class="title">Registrar Nuevo Envío Por Peso</div>
      <div style="width: 70px;"></div>
    </div>

    <form action="guarPrecioXPeso.php" method="POST" onsubmit="return validarFormulario()">
      <div class="content">
        <!-- Información del Usuario -->
        <div class="form-section">
          <h3><i class="fas fa-user-circle"></i> Información del Usuario</h3>
          <div class="form-group">
            <label>Nombre del cliente:</label>
            <input type="text" name="nombre_cliente" placeholder="Ingrese nombre completo" required>
          </div>
          <div class="form-group">
            <label>Dirección de origen:</label>
            <input type="text" name="direccion_origen" placeholder="Ingrese la dirección de origen" required>
          </div>
          <div class="form-group">
            <label>Dirección de destino:</label>
            <input type="text" name="direccion_destino" placeholder="Ingrese la dirección de destino" required>
          </div>
          <div class="form-group">
            <label>Contenido:</label>
            <input type="text" name="contenido" placeholder="Descripción del contenido" required>
          </div>
          <input type="hidden" name="usuario_id" value="<?php echo $_SESSION['id']; ?>">
        </div>

        <!-- Calcular Precio -->
        <div class="calculate-section">
          <h3><i class="fas fa-weight-hanging"></i> Calcular Precio</h3>
          <div class="form-group">
            <label>Peso (kg):</label>
            <input type="number" id="peso" name="peso" step="0.01" min="0.01" placeholder="Ingrese el peso en kg" required>
          </div>

          <div class="price-box">
            <h4>Precio Calculado</h4>
            <div class="price" id="precio">$0</div>
            <div class="range" id="rango">Rango aplicado: -</div>
          </div>

          <!-- Campo oculto para enviar el precio -->
          <input type="hidden" id="precio_envio" name="precio_envio">
          
          <div class="btn-container">
            <button type="button" class="cancel-btn" onclick="cancelarCalculo()">
              <i class="fas fa-times"></i> Cancelar
            </button>
            <button type="submit">
              <i class="fas fa-save"></i> Guardar Envío
            </button>
          </div>
        </div>
      </div>
    </form>
  </div>

  <script>
    const rangos = <?php echo json_encode($rangos); ?>;
  </script>
  <script src="../../js/pages/calcPrecio/scriptPeso.js"></script>
</body>
</html>
