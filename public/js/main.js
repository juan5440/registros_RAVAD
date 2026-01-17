// Main JavaScript file for RAVAD system
console.log("RAVAD System Initialized");

/**
 * Professional Confirmation Dialog
 * @param {string} title - Modal title
 * @param {string} text - Description text
 * @param {string} icon - icon type (warning, info, etc)
 * @param {function} onConfirm - Callback if confirmed
 */
function swalConfirm(title, text, icon = 'warning', onConfirm) {
    const isDarkMode = document.documentElement.getAttribute('data-theme') === 'dark';
    
    Swal.fire({
        title: title,
        text: text,
        icon: icon,
        showCancelButton: true,
        confirmButtonColor: '#0d6efd',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, continuar',
        cancelButtonText: 'Cancelar',
        background: isDarkMode ? '#1e293b' : '#ffffff',
        color: isDarkMode ? '#f8f9fa' : '#212529',
        customClass: {
            popup: 'rounded-4 border-0 shadow'
        }
    }).then((result) => {
        if (result.isConfirmed && onConfirm) {
            onConfirm();
        }
    });
}

// Helper for form submissions that need confirmation
function confirmAndSubmit(event, title, text) {
    event.preventDefault();
    const form = event.target.closest('form');
    swalConfirm(title, text, 'question', () => {
        form.submit();
    });
    return false;
}
