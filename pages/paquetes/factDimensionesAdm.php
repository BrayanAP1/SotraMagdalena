<?php
$host = "dpg-d3he09ali9vc73e2a6o0-a";
$port = "5432";
$dbname = "enviosdb";
$user = "enviosdb_user";
$password = "vgVeoNl0vf7WaTNH05FLHlHMAi2xi3uH"; // <-- cámbiala por la real

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");
if (!$conn) {
    die("❌ Error en la conexión a PostgreSQL");
}

session_start();

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

date_default_timezone_set('America/Bogota');

// Obtener ID del envío
$id_envio = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Consulta SQL con parámetro seguro
$sql = "SELECT e.id, e.nombre_cliente, e.direccion_origen, e.direccion_destino, e.contenido, 
               e.ancho, e.alto, e.largo, e.precio, e.rango, e.fecha_registro,
               u.nombre AS usuario_nombre, u.username, u.rol
        FROM enviosxdimensiones e
        JOIN usuarios u ON e.usuario_id = u.id
        WHERE e.id = $1";

$result = pg_query_params($conn, $sql, array($id_envio));

if (!$result || pg_num_rows($result) === 0) {
    die("⚠ No se encontró la factura con ID $id_envio");
}

// Obtener datos
$factura = pg_fetch_assoc($result);

pg_close($conn);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura de Envío - Dimensiones </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/pages/paquetes/styleFactDimensionesAdm.css">
</head>

<body>
    <div class="invoice-container">
        <!-- Marca de agua -->
        <div class="watermark">SOTRA MAGDALENA</div>

        <!-- Encabezado -->
        <div class="invoice-header">
            <div class="company-info">
                <div class="company-name">SOTRA MAGDALENA S.A.S.</div>
                <div class="company-details">
                    NIT: 900.123.456-7<br>
                    Dirección: Cra 15 # 20-35, Santa Marta, Magdalena<br>
                    Teléfono: (5) 432 1000 - Móvil: 300 123 4567<br>
                    Email: info@sotramagdalena.com<br>
                    Régimen: Simplificado
                </div>
            </div>
            <div class="invoice-info">
                <div class="invoice-title">FACTURA DE VENTA</div>
                <div class="invoice-number">Nº <?= str_pad($factura['id'], 9, "0", STR_PAD_LEFT) ?></div>
                <div class="invoice-date">Fecha: <?= date("d/M/Y", strtotime($factura['fecha_registro'])) ?></div>
                <div class="invoice-date">Resolución DIAN: 18764000045678</div>
            </div>
        </div>

        <!-- Información del cliente -->
        <div class="client-info">
            <div class="section-title">INFORMACIÓN DEL CLIENTE</div>
            <div>
                <strong>Nombre:</strong> <?= $factura['nombre_cliente'] ?><br>
                <strong>Dirección Origen:</strong> <?= $factura['direccion_origen'] ?><br>
                <strong>Dirección Destino:</strong> <?= $factura['direccion_destino'] ?><br>
                <strong>Registrado por:</strong> <?= $factura['usuario_nombre'] ?> (<?= $factura['rol'] ?>)<br>
            </div>
        </div>

        <!-- Detalles de la factura -->
        <div class="invoice-details">
            <div class="section-title">DETALLES DEL ENVÍO</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Descripción</th>
                        <th>Dimensiones</th>
                        <th>Valor Unitario</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong><?= $factura['contenido'] ?></strong></td>
                        <td><?= $factura['ancho'] ?> × <?= $factura['alto'] ?> × <?= $factura['largo'] ?> cm</td>
                        <td>$<?= number_format($factura['precio'], 0, ',', '.') ?></td>
                        <td>$<?= number_format($factura['precio'], 0, ',', '.') ?></td>
                    </tr>
                </tbody>
            </table>

            <?php
            $subtotal = $factura['precio'];
            $total = $subtotal;
            ?>
            <div class="totals">
                <?= number_format($subtotal, 0, ',', '.') ?>

                <?= number_format($iva, 0, ',', '.') ?>
                <div class="totals-row final">
                    <div>TOTAL:</div>
                    <div>$<?= number_format($total, 0, ',', '.') ?></div>
                </div>
            </div>
        </div>

        <!-- Pie de página -->
        <div class="footer">
            <p><strong>SOTRA MAGDALENA S.A.S.</strong> · NIT: 900.123.456-7<br>
                "Soluciones logísticas confiables y eficientes"<br>
                www.sotramagdalena.com · Tel: (5) 432 1000</p>
            <p>Esta factura es un documento legal válido para fines tributarios<br>
                <strong>Fecha de generación:</strong> <?= date("d/M/Y H:i:s") ?>
            </p>
        </div>

        <!-- Botones -->
        <button class="print-btn" onclick="window.print()">
            <i class="fas fa-print me-2"></i> Imprimir Factura
        </button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>