<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

$id = $_GET['id'] ?? null;

// Cannot delete yourself
if ($id == $_SESSION['user_id']) {
    header('Location: index.php?error=No puedes eliminar tu propio usuario');
    exit;
}

if ($id) {
    try {
        $db = getDBConnection();
        $stmt = $db->prepare("DELETE FROM usuarios WHERE id = ?");
        $res = $stmt->execute([$id]);
        
        if ($res) {
            header('Location: index.php?success=Usuario eliminado correctamente');
        } else {
            header('Location: index.php?error=No se pudo eliminar el usuario');
        }
    } catch (PDOException $e) {
        header('Location: index.php?error=Error de base de datos: ' . urlencode($e->getMessage()));
    }
} else {
    header('Location: index.php');
}
?>
