<?php
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['foto']) && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $file = $_FILES['foto'];
    
    // Validations
    $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed_ext)) {
        header("Location: ../../index.php?error=Tipo de archivo no permitido");
        exit;
    }
    
    if ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
        header("Location: ../../index.php?error=El archivo es demasiado grande (Máx 5MB)");
        exit;
    }
    
    $upload_dir = '../../public/uploads/facturas/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $new_name = 'factura_' . $id . '_' . time() . '.' . $ext;
    $target = $upload_dir . $new_name;
    
    if (move_uploaded_file($file['tmp_name'], $target)) {
        $db = getDBConnection();
        $stmt = $db->prepare("UPDATE movimientos SET foto_factura = ? WHERE id = ?");
        $stmt->execute([$new_name, $id]);
        
        header("Location: ../../index.php?success=Factura subida correctamente");
    } else {
        header("Location: ../../index.php?error=Error al subir el archivo");
    }
} else {
    header("Location: ../../index.php");
}
?>
