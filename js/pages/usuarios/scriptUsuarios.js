// Cerrar automáticamente las alertas después de 5 segundos
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
    
    // Preseleccionar opciones de filtro si existen en la URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('rol')) {
        document.querySelector('select[name="rol"]').value = urlParams.get('rol');
    }
    if (urlParams.get('estado')) {
        document.querySelector('select[name="estado"]').value = urlParams.get('estado');
    }
});