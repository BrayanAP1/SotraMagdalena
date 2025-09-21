// Validación básica de rangos
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const minimo = parseFloat(form.querySelector('input[name="minimo"]').value);
                    const maximo = parseFloat(form.querySelector('input[name="maximo"]').value);
                    
                    if (minimo >= maximo) {
                        e.preventDefault();
                        alert('El valor mínimo debe ser menor que el máximo');
                        return false;
                    }
                    
                    const precio = parseFloat(form.querySelector('input[name="precio"]').value);
                    if (precio <= 0) {
                        e.preventDefault();
                        alert('El precio debe ser mayor que cero');
                        return false;
                    }
                });
            });
            
            // Cerrar automáticamente las alertas después de 5 segundos
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });