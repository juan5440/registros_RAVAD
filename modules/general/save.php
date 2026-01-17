<?php
session_start();
if (!isset($_SESSION['user_id'])) { exit('Acceso denegado'); }
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fecha = $_POST['fecha'];
    $factura = $_POST['factura'] ?? null;
    $detalle = $_POST['detalle'];
    $debe = (float)$_POST['debe'];
    $haber = (float)$_POST['haber'];

    $db = getDBConnection();

    try {
        // Start transaction
        $db->beginTransaction();

        // Calculate latest balance
        $stmt = $db->query("SELECT saldo FROM movimientos ORDER BY fecha DESC, id DESC LIMIT 1");
        $last_saldo = (float)$stmt->fetchColumn() ?: 0;
        
        $new_saldo = $last_saldo + $debe - $haber;

        // Insert new record
        $stmt_insert = $db->prepare("INSERT INTO movimientos (fecha, factura, detalle, debe, haber, saldo, origen) VALUES (?, ?, ?, ?, ?, ?, 'general')");
        $stmt_insert->execute([$fecha, $factura, $detalle, $debe, $haber, $new_saldo]);

        $db->commit();
        header("Location: ../../index.php?success=" . urlencode("Registro guardado exitosamente"));
    } catch (Exception $e) {
        $db->rollBack();
        die("Error: " . $e->getMessage());
    }
}
?>
