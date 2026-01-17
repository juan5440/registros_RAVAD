<?php
// Dynamic root path calculation (same as header)
$current_path = $_SERVER['PHP_SELF'];
$is_subfolder = strpos($current_path, '/modules/') !== false;
$root = $is_subfolder ? '../../' : './';
?>
            </div>
        </div>
        <!-- /#page-content-wrapper -->
    </div>
    <!-- /#wrapper -->

    <!-- Bootstrap 5 Bundle with Popper -->
    <script src="<?= $root ?>public/vendor/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $root ?>public/js/main.js"></script>

    <script>
    // Professional Alert System (SweetAlert2)
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const isDarkMode = document.documentElement.getAttribute('data-theme') === 'dark';
        const swalConfig = {
            background: isDarkMode ? '#1e293b' : '#ffffff',
            color: isDarkMode ? '#f8f9fa' : '#212529',
            confirmButtonColor: '#0d6efd',
            customClass: {
                popup: 'rounded-4 border-0 shadow-lg'
            }
        };

        if (urlParams.has('success')) {
            Swal.fire({
                ...swalConfig,
                icon: 'success',
                title: '¡Éxito!',
                text: urlParams.get('success'),
                timer: 3000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end',
                timerProgressBar: true
            });
            // Clean URL
            window.history.replaceState({}, document.title, window.location.pathname);
        }

        if (urlParams.has('error')) {
            Swal.fire({
                ...swalConfig,
                icon: 'error',
                title: 'Atención',
                text: urlParams.get('error'),
                confirmButtonText: 'Entendido'
            });
            // Clean URL
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    });

    // Handle SweetAlert2 theme on toggle
    document.getElementById('theme-toggle')?.addEventListener('click', () => {
        setTimeout(() => {
            const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
            // Current open alerts will keep their theme until closed, 
            // but subsequent ones will use the correct background.
        }, 100);
    });
    </script>
</body>
</html>
