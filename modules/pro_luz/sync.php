<?php
session_start();
if (!isset($_SESSION['user_id'])) { exit('Acceso denegado'); }
require_once '../../config/db.php';
require_once '../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $month_redirect = (int)$_POST['month'];
    $year_redirect = (int)$_POST['year'];

    $db = getDBConnection();

    try {
        $db->beginTransaction();

        // 1. Calculate total unsynced and periods covered
        $stmt_total = $db->query("SELECT SUM(monto) as total_global FROM pro_luz WHERE procesado = 0");
        $total_global = (float)$stmt_total->fetchColumn();

        if ($total_global > 0) {
            // 2. Build detail string with all unique periods covered
            $stmt_periods = $db->query("
                SELECT DISTINCT mes_correspondiente, anio_correspondiente 
                FROM pro_luz 
                WHERE procesado = 0
                ORDER BY anio_correspondiente ASC, mes_correspondiente ASC
            ");
            $periods = $stmt_periods->fetchAll();
            
            $detail_parts = [];
            foreach ($periods as $p) {
                $detail_parts[] = getMonthName($p['mes_correspondiente']) . " " . $p['anio_correspondiente'];
            }
            $detalle = "Consolidado Pro-Luz Global: " . implode(", ", $detail_parts);
            if (strlen($detalle) > 250) { $detalle = substr($detalle, 0, 247) . "..."; }

            // 3. Calculate latest balance in movimientos
            $stmt_bal = $db->query("SELECT saldo FROM movimientos ORDER BY fecha DESC, id DESC LIMIT 1");
            $last_saldo = (float)$stmt_bal->fetchColumn() ?: 0;
            $new_saldo = $last_saldo + $total_global;

            // 4. Create ONE entry in movimientos (using current date)
            $stmt_insert = $db->prepare("INSERT INTO movimientos (fecha, detalle, debe, haber, saldo, origen) VALUES (?, ?, ?, ?, ?, 'pro_luz')");
            $stmt_insert->execute([date('Y-m-d'), $detalle, $total_global, 0, $new_saldo]);
            
            // 5. Mark ALL as processed
            $db->query("UPDATE pro_luz SET procesado = 1 WHERE procesado = 0");
        }

        $db->commit();
        header("Location: index.php?synced=1&month=$month_redirect&year=$year_redirect");
    } catch (Exception $e) {
        $db->rollBack();
        die("Error: " . $e->getMessage());
    }
}
?>
