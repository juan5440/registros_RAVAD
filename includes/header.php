<?php
// Dynamic root path calculation
$current_path = $_SERVER['PHP_SELF'];
$is_subfolder = strpos($current_path, '/modules/') !== false;
$is_pro_luz = strpos($current_path, '/pro_luz/') !== false;
$is_report = strpos($current_path, '/reportes/') !== false;
$is_usuarios = strpos($current_path, '/usuarios/') !== false;
$is_dashboard = strpos($current_path, '/dashboard/') !== false;
$root = $is_subfolder ? '../../' : './';
require_once $root . 'includes/auth_check.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Registros RAVAD</title>
    <!-- Bootstrap 5 CSS -->
    <link href="<?= $root ?>public/vendor/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?= $root ?>public/vendor/css/all.min.css">
    <!-- DataTables CSS -->
    <link href="<?= $root ?>public/vendor/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Theme Styles -->
    <link rel="stylesheet" href="<?= $root ?>public/css/style.css">
    
    <style>
        /* DataTables Custom Styles for Premium Look */
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0 !important;
            margin: 0 !important;
            border: none !important;
        }
        .dataTables_wrapper .dataTables_filter input {
            border-radius: 20px;
            padding: 5px 15px;
            border: 1px solid var(--border-color);
            background-color: var(--card-bg);
            color: var(--text-main);
        }
        [data-theme="dark"] .dataTables_wrapper .dataTables_length select,
        [data-theme="dark"] .dataTables_wrapper .dataTables_filter input {
            background-color: var(--bg-light);
            border-color: var(--border-color);
            color: var(--text-main);
        }
        [data-theme="dark"] .page-link {
            background-color: var(--card-bg);
            border-color: var(--border-color);
            color: var(--text-main);
        }
        [data-theme="dark"] .page-item.disabled .page-link {
            background-color: var(--bg-light);
            border-color: var(--border-color);
            opacity: 0.5;
        }
        .dataTables_info {
            font-size: 0.85rem;
            color: var(--text-muted);
        }
    </style>
    <!-- Chart.js -->
    <script src="<?= $root ?>public/vendor/js/chart.js"></script>
    <!-- SweetAlert2 -->
    <script src="<?= $root ?>public/vendor/js/sweetalert2.all.min.js"></script>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div class="bg-dark text-white" id="sidebar-wrapper">
            <div class="sidebar-heading border-bottom border-secondary p-2">
                <i class="fas fa-book-open me-2"></i> RAVAD Ledger
            </div>
            <div class="list-group list-group-flush mt-3">
                <a href="<?= $root ?>modules/dashboard/index.php" class="list-group-item list-group-item-action <?= strpos($current_path, '/dashboard/') !== false ? 'active' : '' ?>">
                    <i class="fas fa-chart-pie"></i> Dashboard
                </a>
                <a href="<?= $root ?>index.php" class="list-group-item list-group-item-action <?= (strpos($current_path, 'index.php') !== false && !$is_subfolder) ? 'active' : '' ?>">
                    <i class="fas fa-list-ul"></i> Registro General
                </a>
                <a href="<?= $root ?>modules/pro_luz/index.php" class="list-group-item list-group-item-action <?= strpos($current_path, '/pro_luz/') !== false ? 'active' : '' ?>">
                    <i class="fas fa-bolt"></i> Aportaciones Pro-Luz
                </a>
                <a href="<?= $root ?>modules/reportes/index.php" class="list-group-item list-group-item-action <?= strpos($current_path, '/reportes/') !== false ? 'active' : '' ?>">
                    <i class="fas fa-file-contract"></i> Reportes
                </a>
                <a href="<?= $root ?>modules/usuarios/index.php" class="list-group-item list-group-item-action <?= strpos($current_path, '/usuarios/') !== false ? 'active' : '' ?>">
                    <i class="fas fa-users-gear"></i> Usuarios
                </a>
            </div>
        </div>
        <!-- /#sidebar-wrapper -->

        <!-- Page Content -->
        <div id="page-content-wrapper">
            <nav class="navbar navbar-expand-lg navbar-light py-1 px-4 shadow-sm">
                <div class="container-fluid p-0">
                    <button class="btn btn-outline-secondary me-2" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h5 class="m-0 flex-grow-1 fw-bold text-truncate" id="module-title" style="color: var(--text-main); font-size: 1.1rem;">Módulo</h5>
                    
                    <div class="d-flex align-items-center">
                        <!-- User Info -->
                        <div class="dropdown me-3">
                            <a class="btn btn-link text-decoration-none dropdown-toggle p-0 d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 28px; height: 28px; font-size: 0.75rem;">
                                    <?= strtoupper(substr($_SESSION['nombre_completo'] ?? 'U', 0, 1)) ?>
                                </div>
                                <span class="d-none d-md-inline small fw-medium" style="color: var(--text-main); font-size: 0.85rem;"><?= htmlspecialchars($_SESSION['nombre_completo'] ?? 'Usuario') ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-2">
                                <li><a class="dropdown-item py-2 text-danger" href="<?= $root ?>auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión</a></li>
                            </ul>
                        </div>

                        <div class="theme-toggle me-3" id="theme-toggle">
                            <i class="fas fa-moon" id="theme-icon"></i>
                        </div>
                        <?php if ($is_pro_luz): ?>
                            <a href="<?= $root ?>modules/reportes/aportaciones_anual.php" class="btn btn-primary">
                                <i class="fas fa-chart-bar me-2"></i> Reporte Anual
                            </a>
                        <?php elseif ($is_report || $is_dashboard || $is_usuarios): ?>
                            <!-- No extra button for reports, dashboard, or users module -->
                        <?php else: ?>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRecordModal">
                                <i class="fas fa-plus me-2"></i> Nuevo Registro
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </nav>

            <div class="container-fluid p-2">

<script>
    // Theme logic
    const themeToggle = document.getElementById('theme-toggle');
    const themeIcon = document.getElementById('theme-icon');
    const html = document.documentElement;

    const currentTheme = localStorage.getItem('theme') || 'light';
    html.setAttribute('data-theme', currentTheme);
    updateIcon(currentTheme);

    themeToggle.addEventListener('click', () => {
        const targetTheme = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-theme', targetTheme);
        localStorage.setItem('theme', targetTheme);
        updateIcon(targetTheme);
    });

    function updateIcon(theme) {
        themeIcon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    }

    // Sidebar logic
    document.getElementById('sidebarToggle').addEventListener('click', () => {
        document.getElementById('wrapper').classList.toggle('toggled');
    });
</script>
