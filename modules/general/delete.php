<?php
session_start();
if (!isset($_SESSION['user_id'])) { exit('Acceso denegado'); }
require_once '../../config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('m');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

if ($id > 0) {
    $db = getDBConnection();
    
    try {
        // Fetch filename before deletion
        $stmt = $db->prepare("SELECT foto_factura FROM movimientos WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && !empty($row['foto_factura'])) {
            $file_path = "../../public/uploads/facturas/" . $row['foto_factura'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }

        $stmt = $db->prepare("DELETE FROM movimientos WHERE id = ?");
        $stmt->execute([$id]);

        header("Location: ../../index.php?success=Registro y archivo eliminados correctamente&month=$month&year=$year");
    } catch (Exception $e) {
        header("Location: ../../index.php?error=" . urlencode($e->getMessage()) . "&month=$month&year=$year");
    }
} else {
    header("Location: ../../index.php?month=$month&year=$year");
}
?>
