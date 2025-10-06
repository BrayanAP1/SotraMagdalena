<?php
$host = "dpg-d3he09ali9vc73e2a6o0-a"; 
$dbname = "enviosdb"; 
$user = "enviosdb_user";  // tu usuario de PostgreSQL
$password = "vgVeoNl0vf7WaTNH05FLHlHMAi2xi3uH"; // tu contraseña

try {
    // Conexión usando PDO
    $conn = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
