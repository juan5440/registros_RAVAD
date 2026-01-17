<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get the current script name to avoid infinite redirection on login.php
$current_page = basename($_SERVER['PHP_SELF']);
$in_auth_folder = strpos($_SERVER['PHP_SELF'], '/auth/') !== false;

if (!isset($_SESSION['user_id'])) {
    if (!$in_auth_folder && $current_page !== 'login.php') {
        // Calculate root for redirection
        $is_subf = strpos($_SERVER['PHP_SELF'], '/modules/') !== false;
        $redir_path = $is_subf ? '../../auth/login.php' : (strpos($_SERVER['PHP_SELF'], '/auth/') !== false ? 'login.php' : 'auth/login.php');
        header('Location: ' . $redir_path);
        exit;
    }
}
?>
