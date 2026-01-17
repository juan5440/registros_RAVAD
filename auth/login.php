<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RAVAD Ledger - Acceso</title>
    <!-- Bootstrap 5 CSS -->
    <link href="../public/vendor/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../public/vendor/css/all.min.css">
    <link rel="stylesheet" href="../public/css/style.css">
    <!-- SweetAlert2 -->
    <script src="../public/vendor/js/sweetalert2.all.min.js"></script>
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 3rem 2.5rem;
            background: rgba(15, 23, 42, 0.9); /* Darker for better contrast */
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 2rem;
            color: #ffffff;
            box-shadow: 0 40px 100px -20px rgba(0, 0, 0, 0.8);
        }
        .login-logo {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            color: #3b82f6; 
            text-align: center;
            filter: drop-shadow(0 0 15px rgba(59, 130, 246, 0.6));
        }
        .login-title {
            color: #ffffff !important;
            font-size: 2rem;
            letter-spacing: -0.5px;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        .login-subtitle {
            color: rgba(255, 255, 255, 0.8) !important;
            font-size: 0.95rem;
            margin-bottom: 2.5rem;
        }
        .form-label {
            color: #ffffff !important;
            font-weight: 600;
            margin-bottom: 0.6rem;
            font-size: 0.9rem;
            display: block;
        }
        .form-control {
            background: rgba(255, 255, 255, 0.1) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            color: #ffffff !important;
            padding: 0.85rem 1rem;
            border-radius: 0.8rem;
        }
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }
        .form-control:focus {
            background: rgba(255, 255, 255, 0.12);
            border-color: #3b82f6;
            color: #ffffff;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
        }
        .input-group-text {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.6);
            border-radius: 0.8rem;
        }
        .btn-login {
            background: #3b82f6;
            border: none;
            padding: 0.9rem;
            border-radius: 0.8rem;
            font-weight: 700;
            font-size: 1rem;
            margin-top: 1.5rem;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px -5px rgba(59, 130, 246, 0.4);
        }
        .btn-login:hover {
            background: #2563eb;
            transform: translateY(-2px);
            box-shadow: 0 15px 25px -5px rgba(59, 130, 246, 0.5);
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-logo">
        <i class="fas fa-book-open"></i>
    </div>
    <h3 class="text-center login-title fw-bold">RAVAD Ledger</h3>
    <p class="text-center login-subtitle">Ingresa tus credenciales para continuar</p>

    <?php if (isset($_GET['error'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Error de acceso',
                text: '<?= htmlspecialchars($_GET['error']) ?>',
                background: '#1e293b',
                color: '#f8f9fa',
                confirmButtonColor: '#3d8bfd',
                customClass: { popup: 'rounded-4 border-0' }
            });
        });
    </script>
    <?php endif; ?>

    <form action="process_login.php" method="POST">
        <div class="mb-3">
            <label class="form-label">Usuario</label>
            <div class="input-group">
                <span class="input-group-text">
                    <i class="fas fa-user"></i>
                </span>
                <input type="text" name="username" class="form-control" placeholder="Nombre de usuario" required autofocus>
            </div>
        </div>
        <div class="mb-4">
            <label class="form-label">Contraseña</label>
            <div class="input-group">
                <span class="input-group-text">
                    <i class="fas fa-lock"></i>
                </span>
                <input type="password" name="password" class="form-control" placeholder="Tu contraseña" required>
            </div>
        </div>
        <button type="submit" class="btn btn-primary w-100 btn-login">
            Iniciar Sesión
        </button>
    </form>
</div>

</body>
</html>
