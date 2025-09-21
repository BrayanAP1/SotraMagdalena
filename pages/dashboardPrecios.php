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
    <title>Registrar Nuevo Envío - SotraMagdalena S.A.</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../css/pages/styleDashboardPrecios.css">
</head>
<body>
    <div class="header">
            <button onclick="window.location.href='../softwares.php'" class="salir-btn"><i class="fas fa-sign-out-alt"></i>SALIR</button>
        <div class="header-title">Registrar Nuevo Envío - SotraMagdalena S.A.</div>
        <div style="width: 75px;"></div>
    </div>
    
    <div class="main">
        <div class="left">
            <div class="overlay">
                <i class="fas fa-ruler-combined option-icon"></i>
                <a href="calcPrecio/dimensiones.php">
                    <button class="btn-envio">Registrar<br>Nuevo Envío Por<br>Dimensiones</button>
                </a>
                <p class="option-description">Calcula el costo basado en las dimensiones del paquete</p>
            </div>
        </div>
        <div class="right">
            <div class="overlay">
                <i class="fas fa-weight-hanging option-icon"></i>
                <a href="calcPrecio/peso.php">
                    <button class="btn-envio">Registrar<br>Nuevo Envío<br>Por Peso</button>
                </a>
                <p class="option-description">Calcula el costo basado en el peso total del paquete</p>
            </div>
        </div>
    </div>
</body>
</html>