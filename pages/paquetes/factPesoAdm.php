<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit();
}

// Conexión a PostgreSQL
$host = "dpg-d3he09ali9vc73e2a6o0-a";
$port = "5432";
$dbname = "enviosdb";
$user = "enviosdb_user";
$password = "vgVeoNl0vf7WaTNH05FLHlHMAi2xi3uH"; // <-- cámbiala por la real

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");
if (!$conn) {
    die("❌ Error de conexión con la base de datos PostgreSQL");
}

date_default_timezone_set('America/Bogota');

// Validar ID recibido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("⚠ ID de envío inválido.");
}

$id_envio = intval($_GET['id']);

// Consulta SQL
$sql = "SELECT e.*, u.nombre AS usuario_nombre, u.username, u.rol 
        FROM enviosxpeso e
        INNER JOIN usuarios u ON e.usuario_id = u.id
        WHERE e.id = $1
        LIMIT 1";

// Ejecutar consulta
$result = pg_query_params($conn, $sql, array($id_envio));

if (!$result || pg_num_rows($result) === 0) {
    die("⚠ No se encontró la factura con ID $id_envio");
}

// Obtener datos
$envio = pg_fetch_assoc($result);

pg_close($conn);
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura de Envío - Peso</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/pages/paquetes/styleFactPesoAdm.css">
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
                <div class="invoice-number">Nº <?= str_pad($envio['id'], 9, "0", STR_PAD_LEFT) ?></div>
                <div class="invoice-date">Fecha: <?= date("d/M/Y", strtotime($envio['fecha_registro'])) ?></div>
            </div>
        </div>

        <!-- Información del cliente -->
        <div class="client-info">
            <div class="section-title">INFORMACIÓN DEL CLIENTE</div>
            <p>
                <strong>Nombre:</strong> <?= htmlspecialchars($envio['nombre_cliente']) ?><br>
                <strong>Dirección Origen:</strong> <?= htmlspecialchars($envio['direccion_origen']) ?><br>
                <strong>Dirección Destino:</strong> <?= htmlspecialchars($envio['direccion_destino']) ?><br>
            </p>
        </div>

        <!-- Detalles del envío -->
        <div class="invoice-details">
            <div class="section-title">DETALLES DEL ENVÍO</div>
            <table>
                <thead>
                    <tr>
                        <th>Contenido</th>
                        <th>Peso</th>
                        <th>Valor Unitario</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?= htmlspecialchars($envio['contenido']) ?></td>
                        <td><?= number_format($envio['peso'], 2) ?> Kg</td>
                        <td>$<?= number_format($envio['precio'], 0, ',', '.') ?></td>
                        <td>$<?= number_format($envio['precio'], 0, ',', '.') ?></td>
                    </tr>
                </tbody>
            </table>

            <div class="totals">
                <p><strong>Total:</strong> <span>$<?= number_format($envio['precio'], 0, ',', '.') ?></span></p>
                <?= number_format($envio['precio'] * 0.19, 0, ',', '.') ?>
                <?= number_format($envio['precio'] * 1.19, 0, ',', '.') ?>
            </div>
        </div>

        <!-- Información del usuario -->
        <div class="user-info">
            <div class="section-title">ATENDIDO POR</div>
            <p>
                <strong>Usuario:</strong> <?= htmlspecialchars($envio['usuario_nombre']) ?> (<?= htmlspecialchars($envio['rol']) ?>)<br>
                <strong>Username:</strong> <?= htmlspecialchars($envio['username']) ?>
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>SOTRA MAGDALENA S.A.S.</strong> · NIT: 900.123.456-7<br>
                "Soluciones logísticas confiables y eficientes"<br>
                www.sotramagdalena.com · Tel: (5) 432 1000</p>
            <p><strong>Fecha de generación:</strong> <?= date("d/M/Y H:i:s") ?></p>
        </div>

        <!-- Botones -->
        <button class="print-btn" onclick="window.print()">
            <i class="fas fa-print me-2"></i> Imprimir Factura
        </button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>