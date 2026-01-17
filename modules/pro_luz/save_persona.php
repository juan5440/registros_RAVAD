<?php
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nombre'])) {
    $db = getDBConnection();
    $nombre = trim($_POST['nombre']);
    
    if (isset($_POST['id'])) {
        // Update
        $id = (int)$_POST['id'];
        $stmt = $db->prepare("UPDATE personas SET nombre = ? WHERE id = ?");
        $stmt->execute([$nombre, $id]);
    } else {
        // New
        $stmt = $db->prepare("INSERT INTO personas (nombre) VALUES (?)");
        $stmt->execute([$nombre]);
    }
}

header("Location: personas.php");
exit;
