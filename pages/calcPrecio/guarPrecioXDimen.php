<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: /Sotramagdalena/index.php");
    exit();
}

date_default_timezone_set('America/Bogota');

// Conexión a PostgreSQL (PDO)
$host = "dpg-d3he09ali9vc73e2a6o0-a";
$port = "5432";
$dbname = "enviosdb";
$user = "enviosdb_user"; // cambia si tu usuario es distinto
$password = "vgVeoNl0vf7WaTNH05FLHlHMAi2xi3uH"; // agrega la contraseña si tiene

try {
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Variables
$id_envio = 0;
$mensaje = "";
$tipo_mensaje = "";
$mostrar_botones = false;
$detalles_envio = [];

// Procesar formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre_cliente   = $_POST['nombre_cliente'] ?? '';
    $direccion_origen = $_POST['direccion_origen'] ?? '';
    $direccion_destino= $_POST['direccion_destino'] ?? '';
    $contenido        = $_POST['contenido'] ?? '';
    $ancho            = $_POST['ancho'] ?? '';
    $alto             = $_POST['alto'] ?? '';
    $largo            = $_POST['largo'] ?? '';
    $precio           = $_POST['precio'] ?? '';
    $rango            = $_POST['rango'] ?? '';

    try {
        $sql = "INSERT INTO enviosxdimensiones 
                (nombre_cliente, direccion_origen, direccion_destino, contenido, ancho, alto, largo, precio, rango, usuario_id)
                VALUES 
                (:nombre_cliente, :direccion_origen, :direccion_destino, :contenido, :ancho, :alto, :largo, :precio, :rango, :usuario_id)
                RETURNING id";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':nombre_cliente'   => $nombre_cliente,
            ':direccion_origen' => $direccion_origen,
            ':direccion_destino'=> $direccion_destino,
            ':contenido'        => $contenido,
            ':ancho'            => $ancho,
            ':alto'             => $alto,
            ':largo'            => $largo,
            ':precio'           => $precio,
            ':rango'            => $rango,
            ':usuario_id'       => $_SESSION['id']
        ]);

        $id_envio = $stmt->fetchColumn(); // obtiene el id retornado

        $_SESSION['envio_guardado'] = [
            'id_envio' => $id_envio,
            'nombre_cliente' => $nombre_cliente,
            'direccion_origen' => $direccion_origen,
            'direccion_destino' => $direccion_destino,
            'contenido' => $contenido,
            'ancho' => $ancho,
            'alto' => $alto,
            'largo' => $largo,
            'precio' => $precio,
            'rango' => $rango,
            'fecha' => date('d/m/Y H:i:s')
        ];

        header("Location: " . $_SERVER['PHP_SELF'] . "?exito=1");
        exit();

    } catch (PDOException $e) {
        $mensaje = "Error al registrar el envío: " . $e->getMessage();
        $tipo_mensaje = "error";
        $mostrar_botones = true;
    }
}
// Si venimos de una redirección exitosa
elseif (isset($_GET['exito']) && isset($_SESSION['envio_guardado'])) {
    $detalles_envio = $_SESSION['envio_guardado'];
    $id_envio = $detalles_envio['id_envio'];
    $mensaje = "¡Envío registrado correctamente!";
    $tipo_mensaje = "success";
    $mostrar_botones = true;
} else {
    $mensaje = "Acceso no válido a esta página.";
    $tipo_mensaje = "warning";
}

$conn = null; // cerrar conexión
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Envío - SOTRA Magdalena</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/pages/calcPrecio/styleGuarPrecioXDimen.css">
</head>
<body>
    <div class="overlay">
        <div class="top-bar">
            <button onclick="window.location.href='../dashboardPrecios.php'">
                <i class="fas fa-arrow-left"></i> VOLVER
            </button>
            <div class="title">Confirmación de Envío por Dimensiones</div>
            <div style="width: 70px;"></div>
        </div>

        <div class="container">
            <!-- Panel de Estado -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-clipboard-check icon-large 
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
    <?php if (!empty($detalles_envio)): ?>
        <div class="price-highlight">
            <div class="price-label">Total del Envío</div>
            <div class="price-value">$<?php echo number_format($detalles_envio['precio'], 2); ?></div>
            <div class="rango-info">Rango aplicado: <?php echo htmlspecialchars($detalles_envio['rango']); ?></div>
        </div>
    <?php else: ?>
        <div class="price-highlight">
            <div class="price-label">Estado</div>
            <div class="price-value">✅ El envío ya fue registrado previamente.</div>
        </div>
    <?php endif; ?>
<?php endif; ?>

                </div>
                
                <?php if ($mostrar_botones): ?>
                    <div class="btn-container">
                        <?php if ($tipo_mensaje == 'success'): ?>
                            <a href="../paquetes/factDimensionesUsu.php?id=<?= $id_envio ?>" class="btn btn-primary">
                                <i class="fas fa-print"></i> Imprimir Factura
                            </a>
                        <?php endif; ?>
                        
                        <a href="dimensiones.php" class="btn btn-secondary">
                            <i class="fas fa-calculator"></i> Nuevo Cálculo
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
            
            <!-- Panel de Detalles -->
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
                        <div class="detail-label">Dimensiones (An x Al x La)</div>
                        <div class="detail-value"><?php echo $detalles_envio['ancho']; ?>cm x <?php echo $detalles_envio['alto']; ?>cm x <?php echo $detalles_envio['largo']; ?>cm</div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Volumen</div>
                        <div class="detail-value"><?php echo number_format($detalles_envio['ancho'] * $detalles_envio['alto'] * $detalles_envio['largo'], 2); ?> cm³</div>
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
</body>
</html>