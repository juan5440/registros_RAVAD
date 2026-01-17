<?php
session_start();
if (!isset($_SESSION['user_id'])) { exit('Acceso denegado'); }
require_once '../../config/db.php';
require_once '../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $month = (int)$_POST['month'];
    $year = (int)$_POST['year'];

    $db = getDBConnection();

    try {
        $db->beginTransaction();

        // 1. Calculate total unsynced for this month/year
        $stmt_sum = $db->prepare("SELECT SUM(monto) FROM pro_luz WHERE mes_correspondiente = ? AND anio_correspondiente = ? AND procesado = 0");
        $stmt_sum->execute([$month, $year]);
        $total = (float)$stmt_sum->fetchColumn();

        if ($total > 0) {
            // 2. Calculate latest balance in movimientos
            $stmt_bal = $db->query("SELECT saldo FROM movimientos ORDER BY fecha DESC, id DESC LIMIT 1");
            $last_saldo = (float)$stmt_bal->fetchColumn() ?: 0;
            $new_saldo = $last_saldo + $total;

            // 3. Create entry in movimientos
            $detalle = "Consolidado Pro-Luz - " . getMonthName($month) . " " . $year;
            $stmt_insert = $db->prepare("INSERT INTO movimientos (fecha, detalle, debe, haber, saldo, origen) VALUES (?, ?, ?, ?, ?, 'pro_luz')");
            $stmt_insert->execute([date('Y-m-d'), $detalle, $total, 0, $new_saldo]);

            // 4. Mark records as processed
            $stmt_update = $db->prepare("UPDATE pro_luz SET procesado = 1 WHERE mes_correspondiente = ? AND anio_correspondiente = ? AND procesado = 0");
            $stmt_update->execute([$month, $year]);
        }

        $db->commit();
        header("Location: index.php?synced=1&month=$month&year=$year");
    } catch (Exception $e) {
        $db->rollBack();
        die("Error: " . $e->getMessage());
    }
}
?>
