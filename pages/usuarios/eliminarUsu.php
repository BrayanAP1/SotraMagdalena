<?php
include("conexion.php");

if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // ✅ Sanitiza el ID

    try {
        // ✅ Consulta preparada para evitar inyección SQL
        $sql = "DELETE FROM usuarios WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $id]);

        // ✅ Redirige si se eliminó correctamente
        header("Location: usuarios.php?mensaje=eliminado");
        exit();
    } catch (PDOException $e) {
        echo "❌ Error al eliminar el usuario: " . $e->getMessage();
    }
} else {
    echo "⚠️ No se proporcionó un ID válido.";
}
?>
