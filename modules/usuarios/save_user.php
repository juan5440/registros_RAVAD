<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = getDBConnection();
    $id = $_POST['id'] ?? null;
    $username = $_POST['username'] ?? '';
    $nombre_completo = $_POST['nombre_completo'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($nombre_completo)) {
        header('Location: index.php?error=Faltan campos requeridos');
        exit;
    }

    if ($id) {
        // Update user
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE usuarios SET username = ?, nombre_completo = ?, password = ? WHERE id = ?");
            $res = $stmt->execute([$username, $nombre_completo, $hashed_password, $id]);
        } else {
            $stmt = $db->prepare("UPDATE usuarios SET username = ?, nombre_completo = ? WHERE id = ?");
            $res = $stmt->execute([$username, $nombre_completo, $id]);
        }
        $msg = $res ? "Usuario actualizado correctamente" : "Error al actualizar usuario";
    } else {
        // Create user
        if (empty($password)) {
            header('Location: index.php?error=Contraseña requerida para nuevo usuario');
            exit;
        }
        
        // Check if username already exists
        $check = $db->prepare("SELECT COUNT(*) FROM usuarios WHERE username = ?");
        $check->execute([$username]);
        if ($check->fetchColumn() > 0) {
            header('Location: index.php?error=El nombre de usuario ya existe');
            exit;
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO usuarios (username, password, nombre_completo) VALUES (?, ?, ?)");
        $res = $stmt->execute([$username, $hashed_password, $nombre_completo]);
        $msg = $res ? "Usuario creado correctamente" : "Error al crear usuario";
    }

    if ($res) {
        header('Location: index.php?success=' . urlencode($msg));
    } else {
        header('Location: index.php?error=' . urlencode($msg));
    }
} else {
    header('Location: index.php');
}
?>
