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
        // Verificar que el registro no haya sido procesado
        $stmt = $db->prepare("SELECT procesado FROM pro_luz WHERE id = ?");
        $stmt->execute([$id]);
        $procesado = $stmt->fetchColumn();

        if ($procesado == 1) {
            header("Location: index.php?error=No se puede anular un registro ya sincronizado&month=$month&year=$year");
            exit;
        }

        // Eliminar el registro
        $stmt = $db->prepare("DELETE FROM pro_luz WHERE id = ?");
        $stmt->execute([$id]);

        header("Location: index.php?success=Registro anulado correctamente&month=$month&year=$year");
    } catch (Exception $e) {
        header("Location: index.php?error=" . urlencode($e->getMessage()) . "&month=$month&year=$year");
    }
} else {
    header("Location: index.php?month=$month&year=$year");
}
?>
