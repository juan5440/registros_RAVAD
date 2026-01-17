<?php
session_start();
if (!isset($_SESSION['user_id'])) { exit('Acceso denegado'); }
require_once '../../config/db.php';

if (isset($_GET['id']) && isset($_GET['status'])) {
    $db = getDBConnection();
    $id = (int)$_GET['id'];
    $status = (int)$_GET['status'];
    
    // Safety check: Don't physically delete if they have payments, just deactivate
    // If status 0 is passed, deactivate. If 1, reactivate.
    $stmt = $db->prepare("UPDATE personas SET activo = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
}

header("Location: personas.php");
exit;
