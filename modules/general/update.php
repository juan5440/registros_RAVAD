<?php
session_start();
if (!isset($_SESSION['user_id'])) { exit('Acceso denegado'); }
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];
    $fecha = $_POST['fecha'];
    $factura = $_POST['factura'];
    $detalle = $_POST['detalle'];
    $debe = (float)$_POST['debe'];
    $haber = (float)$_POST['haber'];
    
    $month = (int)$_POST['month_redirect'];
    $year = (int)$_POST['year_redirect'];

    $db = getDBConnection();

    try {
        $stmt = $db->prepare("UPDATE movimientos SET fecha = ?, factura = ?, detalle = ?, debe = ?, haber = ? WHERE id = ?");
        $stmt->execute([$fecha, $factura, $detalle, $debe, $haber, $id]);

        header("Location: ../../index.php?success=Registro actualizado correctamente&month=$month&year=$year");
    } catch (Exception $e) {
        header("Location: ../../index.php?error=" . urlencode($e->getMessage()) . "&month=$month&year=$year");
    }
}
?>
