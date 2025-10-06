<?php
$host = "dpg-d3he09ali9vc73e2a6o0-a";
$port = "5432"; // Puerto por defecto de PostgreSQL
$dbname = "enviosdb";
$user = "enviosdb_user";
$password = "vgVeoNl0vf7WaTNH05FLHlHMAi2xi3uH"; // <-- cámbiala por tu contraseña real

// Conexión a PostgreSQL
$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

// Verificar conexión
if (!$conn) {
    die("❌ Error al conectar a la base de datos: " . pg_last_error());
}
?>
