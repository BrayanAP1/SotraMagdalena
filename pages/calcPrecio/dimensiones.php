<?php
session_start();

if (!isset($_SESSION['id'])) {
  header("Location: /Sotramagdalena/index.php");
  exit();
}

$conn = new mysqli("localhost", "root", "", "enviosdb");
$rangos = $conn->query("SELECT * FROM rangoxdimen ORDER BY minimo ASC");
$data = [];
while ($r = $rangos->fetch_assoc()) {
  $data[] = $r;
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Registrar Envío por Dimensiones - SOTRA Magdalena</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../../css/pages/calcPrecio/styleDimensiones.css">
</head>

<body>
  <div class="overlay">
    <div class="top-bar">
      <button onclick="window.location.href='../dashboardPrecios.php'"><i class="fas fa-sign-out-alt"></i> SALIR</button>
      <div class="title">Registrar Nuevo Envío Por Dimensiones</div>
      <div style="width: 70px;"></div>
    </div>

    <form action="guarPrecioXDimen.php" method="POST" onsubmit="return prepararEnvio();">
      <div class="content">
        <!-- Información del Usuario -->
        <div class="form-section">
          <h3><i class="fas fa-user-circle me-2"></i>Información del Usuario</h3>
          <div class="form-group">
            <label>Nombre del cliente:</label>
            <input type="text" name="nombre_cliente" placeholder="Ingrese nombre" required>
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
            <input type="text" name="contenido" placeholder="Ingresa notas del contenido del envío">
          </div>
          <input type="hidden" name="usuario_id" value="<?php echo $_SESSION['id']; ?>">
        </div>

        <!-- Calcular Precio -->
        <div class="calculate-section">
          <h3><i class="fas fa-calculator me-2"></i>Calcular Precio</h3>
          <div class="form-group">
            <label>Ancho (cm):</label>
            <input type="number" step="0.01" name="ancho" id="ancho" placeholder="Ingrese el ancho del paquete" required>
          </div>
          <div class="form-group">
            <label>Alto (cm):</label>
            <input type="number" step="0.01" name="alto" id="alto" placeholder="Ingrese el alto del paquete" required>
          </div>
          <div class="form-group">
            <label>Largo (cm):</label>
            <input type="number" step="0.01" name="largo" id="largo" placeholder="Ingrese el largo del paquete" required>
          </div>

          <input type="hidden" name="precio" id="precioInput">
          <input type="hidden" name="rango" id="rangoInput">

          <div class="price-box">
            <h4>Precio Calculado</h4>
            <div class="price" id="precio">$0</div>
            <div class="range" id="rango">Rango aplicado: -</div>
          </div>
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
    const rangos = <?php echo json_encode($data); ?>;
  </script>
  <script src="../../js/pages/calcPrecio/scriptDimensiones.js"></script>
</body>
</html>
