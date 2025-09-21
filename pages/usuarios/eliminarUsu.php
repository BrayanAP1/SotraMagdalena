<?php include("conexion.php"); ?>
<?php
$id = $_GET['id'];
$sql = "DELETE FROM usuarios WHERE id=$id";
if ($conn->query($sql)) {
    header("Location: usuarios.php");
} else {
    echo "Error: " . $conn->error;
}
?>
