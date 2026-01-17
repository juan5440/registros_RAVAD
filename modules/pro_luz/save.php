<?php
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fecha = $_POST['fecha'];
    $persona_id = (int)$_POST['persona_id'];
    $monto = (float)$_POST['monto'];
    $mes_c = (int)$_POST['mes_c'];
    $anio_c = (int)$_POST['anio_c'];

    $db = getDBConnection();

    try {
        $stmt = $db->prepare("INSERT INTO pro_luz (fecha, persona_id, monto, mes_correspondiente, anio_correspondiente) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$fecha, $persona_id, $monto, $mes_c, $anio_c]);

        header("Location: index.php?success=1&month=$mes_c&year=$anio_c");
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
}
?>
