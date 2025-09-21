<?php 
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: /Sotramagdalena/index.php");
    exit();
}

// Datos de conexión
$servername = "localhost"; 
$username   = "root"; 
$password   = ""; 
$dbname     = "enviosdb";  

$conn = new mysqli($servername, $username, $password, $dbname);  

if ($conn->connect_error) {     
    die("Conexión fallida: " . $conn->connect_error); 
}

date_default_timezone_set('America/Bogota');

// Variables
$id_envio = 0;
$mensaje = "";
$tipo_mensaje = "";
$mostrar_botones = false;
$detalles_envio = [];

// Procesar formulario (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre_cliente   = mysqli_real_escape_string($conn, $_POST['nombre_cliente']); 
    $direccion_origen = mysqli_real_escape_string($conn, $_POST['direccion_origen']); 
    $direccion_destino= mysqli_real_escape_string($conn, $_POST['direccion_destino']); 
    $contenido        = mysqli_real_escape_string($conn, $_POST['contenido']); 
    $peso             = mysqli_real_escape_string($conn, $_POST['peso']); 
    $precio_envio     = mysqli_real_escape_string($conn, $_POST['precio_envio']);

    $sql = "INSERT INTO enviosXpeso 
            (nombre_cliente, direccion_origen, direccion_destino, contenido, peso, precio, usuario_id)         
            VALUES 
            ('$nombre_cliente', '$direccion_origen', '$direccion_destino', '$contenido', '$peso', '$precio_envio', '{$_SESSION['id']}')";  

    if ($conn->query($sql) === TRUE) {
        // Guardar en sesión para evitar duplicados
        $_SESSION['envio_guardado_peso'] = [
            'id_envio' => $conn->insert_id,
            'nombre_cliente' => $nombre_cliente,
            'direccion_origen' => $direccion_origen,
            'direccion_destino' => $direccion_destino,
            'contenido' => $contenido,
            'peso' => $peso,
            'precio' => $precio_envio,
            'fecha' => date('d/m/Y H:i:s')
        ];

        // Redirigir con GET
        header("Location: " . $_SERVER['PHP_SELF'] . "?exito=1");
        exit();
    } else {
        $mensaje = "Error al registrar el envío: " . $conn->error;
        $tipo_mensaje = "error";
        $mostrar_botones = true;
    }
}

// Si venimos de una redirección exitosa (GET)
elseif (isset($_GET['exito']) && isset($_SESSION['envio_guardado_peso'])) {
    $detalles_envio = $_SESSION['envio_guardado_peso'];
    $id_envio = $detalles_envio['id_envio'];
    $mensaje = "¡Envío registrado correctamente!";
    $tipo_mensaje = "success";
    $mostrar_botones = true;
}

// Si no es ni POST ni redirección válida
else {
    $mensaje = "Acceso no válido a esta página.";
    $tipo_mensaje = "warning";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Envío por Peso - SOTRA Magdalena</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/pages/calcPrecio/styleGuarPrecioXPeso.css">
</head>
<body>
    <div class="overlay">
        <div class="top-bar">
            <button onclick="window.location.href='../dashboardPrecios.php'"><i class="fas fa-arrow-left"></i> VOLVER</button>
            <div class="title">Confirmación de Envío por Peso</div>
            <div style="width: 70px;"></div>
        </div>

        <div class="container">
            <!-- Panel de Estado -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-weight-scale icon-large 
                        <?php echo $tipo_mensaje == 'success' ? 'success-icon' : 
                              ($tipo_mensaje == 'error' ? 'error-icon' : 'warning-icon'); ?>"></i>
                    <h3>Estado del Registro</h3>
                </div>
                
                <div class="status-message 
                    <?php echo $tipo_mensaje == 'success' ? 'success-message' : 
                          ($tipo_mensaje == 'error' ? 'error-message' : 'warning-message'); ?>">
                    <?php if ($tipo_mensaje == 'success'): ?>
                        <h2><i class="fas fa-check-circle"></i> ¡Registro Exitoso!</h2>
                    <?php elseif ($tipo_mensaje == 'error'): ?>
                        <h2><i class="fas fa-exclamation-circle"></i> Error en el Registro</h2>
                    <?php else: ?>
                        <h2><i class="fas fa-exclamation-triangle"></i> Acceso Restringido</h2>
                    <?php endif; ?>
                    
                    <p><?php echo htmlspecialchars($mensaje); ?></p>
                    
                    <?php if ($tipo_mensaje == 'success'): ?>
                        <div class="price-highlight">
                            <div class="price-label">Total del Envío</div>
                            <div class="price-value">$<?php echo number_format($detalles_envio['precio'], 2); ?></div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($mostrar_botones): ?>
                    <div class="btn-container">
                        <?php if ($tipo_mensaje == 'success'): ?>
                            <a href="../paquetes/factPesoUsu.php?id=<?= $id_envio ?>" class="btn btn-primary">
                                <i class="fas fa-print"></i> Imprimir Factura
                            </a>
                        <?php endif; ?>
                        
                        <a href="peso.php" class="btn btn-secondary">
                            <i class="fas fa-weight-scale"></i> Nuevo Envío
                        </a>
                        
                        <a href="../dashboardPrecios.php" class="btn btn-secondary">
                            <i class="fas fa-home"></i> Ir al Dashboard
                        </a>
                    </div>
                <?php else: ?>
                    <div class="btn-container">
                        <a href="../dashboardPrecios.php" class="btn btn-primary">
                            <i class="fas fa-home"></i> Ir al Dashboard
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Panel de Detalles (solo mostrar si fue éxito) -->
            <?php if ($tipo_mensaje == 'success' && !empty($detalles_envio)): ?>
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-info-circle icon-large"></i>
                    <h3>Detalles del Envío</h3>
                </div>
                
                <div class="details-grid">
                    <div class="detail-item">
                        <div class="detail-label">Número de Envío</div>
                        <div class="detail-value">#<?php echo $id_envio; ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Fecha y Hora</div>
                        <div class="detail-value"><?php echo $detalles_envio['fecha']; ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Cliente</div>
                        <div class="detail-value"><?php echo htmlspecialchars($detalles_envio['nombre_cliente']); ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Contenido</div>
                        <div class="detail-value"><?php echo !empty($detalles_envio['contenido']) ? htmlspecialchars($detalles_envio['contenido']) : 'Sin especificar'; ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Origen</div>
                        <div class="detail-value"><?php echo htmlspecialchars($detalles_envio['direccion_origen']); ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Destino</div>
                        <div class="detail-value"><?php echo htmlspecialchars($detalles_envio['direccion_destino']); ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Peso</div>
                        <div class="detail-value"><?php echo $detalles_envio['peso']; ?> kg</div>
                    </div>
                    
                </div>
                
                <div class="note">
                    <div class="detail-label">Nota:</div>
                    <div class="detail-value">Este envío ha sido registrado en nuestro sistema y está pendiente de recolección.</div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        <?php if ($tipo_mensaje == 'success'): ?>
        setTimeout(function() {
            <?= $id_envio ?>
        }, 1000);
        <?php endif; ?>
    </script>
</body>
</html>