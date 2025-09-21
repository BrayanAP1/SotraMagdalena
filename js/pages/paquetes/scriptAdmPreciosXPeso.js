// Validación de formularios
        document.addEventListener('DOMContentLoaded', function() {
            // Validación de rangos de peso
            const forms = document.querySelectorAll('form');
            
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    // Solo validar formularios que tengan campos de peso
                    const pesoMin = form.querySelector('input[name="peso_min"]');
                    const pesoMax = form.querySelector('input[name="peso_max"]');
                    
                    if (pesoMin && pesoMax) {
                        const min = parseFloat(pesoMin.value);
                        const max = parseFloat(pesoMax.value);
                        
                        if (min >= max) {
                            e.preventDefault();
                            alert('El peso mínimo debe ser menor que el peso máximo');
                            return false;
                        }
                    }
                    
                    // Validar precio
                    const precio = form.querySelector('input[name="precio_kg"]');
                    if (precio && parseFloat(precio.value) <= 0) {
                        e.preventDefault();
                        alert('El precio por kg debe ser mayor que cero');
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