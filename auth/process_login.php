<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        header('Location: login.php?error=Usuario y contraseña requeridos');
        exit;
    }

    $db = getDBConnection();
    $stmt = $db->prepare("SELECT * FROM usuarios WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Regenerate session ID for security
        session_regenerate_id();
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['nombre_completo'] = $user['nombre_completo'];

        // Update last access
        $upd = $db->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?");
        $upd->execute([$user['id']]);

        header('Location: ../index.php');
        exit;
    } else {
        header('Location: login.php?error=Usuario o contraseña incorrectos');
        exit;
    }
} else {
    header('Location: login.php');
    exit;
}
?>
