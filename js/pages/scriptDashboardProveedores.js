// Variable global para almacenar la instancia del gráfico
let proveedoresChart = null;

// Función para inicializar o actualizar el gráfico
function inicializarGrafico() {
    const ctxMes = document.getElementById('graficoProveedoresMes');

    if (proveedoresChart) {
        proveedoresChart.destroy();
    }

    proveedoresChart = new Chart(ctxMes, {
        type: 'line',
        data: {
            labels: meses, 
            datasets: [{
                label: 'Proveedores Registrados',
                data: totales, 
                backgroundColor: '#2ab64b',
                borderColor: '#1a8c39',
                borderWidth: 2,
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 10,
                    cornerRadius: 6
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(42, 182, 75, 0.1)' }
                },
                x: { grid: { display: false } }
            },
            animation: {
                duration: 1500,
                easing: 'easeOutQuart'
            }
        }
    });
}

// Inicializar gráfico al cargar
document.addEventListener('DOMContentLoaded', () => {
    inicializarGrafico();
    window.addEventListener('resize', inicializarGrafico);
});

// Función exportar Excel
function exportToExcel() {
    try {
        const table = document.getElementById('tablaProveedores');
        const data = [];
        const headers = [];

        for (let i = 0; i < table.rows[0].cells.length - 1; i++) {
            headers.push(table.rows[0].cells[i].textContent.trim());
        }
        data.push(headers);

        for (let i = 1; i < table.rows.length; i++) {
            const rowData = [];
            for (let j = 0; j < table.rows[i].cells.length - 1; j++) {
                rowData.push(table.rows[i].cells[j].textContent.trim());
            }
            data.push(rowData);
        }

        const ws = XLSX.utils.aoa_to_sheet(data);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Proveedores");

        const fileName = `proveedores_${new Date().toISOString().split('T')[0]}.xlsx`;
        XLSX.writeFile(wb, fileName);

        mostrarNotificacion('Excel exportado correctamente', 'success');
    } catch (error) {
        console.error(error);
        mostrarNotificacion('Error al exportar a Excel', 'error');
    }
}

// Función exportar PDF
function exportToPDF() {
    try {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

        doc.setFontSize(18);
        doc.setTextColor(42, 182, 75);
        doc.text('Lista de Proveedores', 14, 15);

        doc.setFontSize(12);
        doc.setTextColor(100, 100, 100);
        doc.text(`Fecha: ${new Date().toLocaleDateString()}`, 14, 22);

        const table = document.getElementById('tablaProveedores');
        const data = [];

        for (let i = 1; i < table.rows.length; i++) {
            const rowData = [];
            for (let j = 0; j < table.rows[i].cells.length - 1; j++) {
                rowData.push(table.rows[i].cells[j].textContent.trim());
            }
            data.push(rowData);
        }

        doc.autoTable({
            startY: 30,
            head: [['ID', 'Nombre', 'Dirección', 'Teléfono', 'Correo', 'Fecha Registro']],
            body: data,
            theme: 'grid',
            headStyles: {
                fillColor: [42, 182, 75],
                textColor: [255, 255, 255],
                fontStyle: 'bold'
            },
            alternateRowStyles: { fillColor: [240, 240, 240] },
            styles: { fontSize: 9, cellPadding: 3 }
        });

        const fileName = `proveedores_${new Date().toISOString().split('T')[0]}.pdf`;
        doc.save(fileName);

        mostrarNotificacion('PDF exportado correctamente', 'success');
    } catch (error) {
        console.error(error);
        mostrarNotificacion('Error al exportar a PDF', 'error');
    }
}

// Notificaciones
function mostrarNotificacion(mensaje, tipo) {
    const notificacion = document.createElement('div');
    notificacion.style.position = 'fixed';
    notificacion.style.top = '20px';
    notificacion.style.right = '20px';
    notificacion.style.padding = '12px 20px';
    notificacion.style.borderRadius = '8px';
    notificacion.style.color = 'white';
    notificacion.style.fontWeight = '500';
    notificacion.style.zIndex = '10000';
    notificacion.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
    notificacion.style.opacity = '0';
    notificacion.style.transition = 'opacity 0.3s ease';

    notificacion.style.background = tipo === 'success'
        ? 'linear-gradient(135deg, #2ab64b 0%, #1a8c39 100%)'
        : 'linear-gradient(135deg, #dc2626 0%, #bd2130 100%)';

    notificacion.textContent = mensaje;
    document.body.appendChild(notificacion);

    setTimeout(() => notificacion.style.opacity = '1', 10);
    setTimeout(() => {
        notificacion.style.opacity = '0';
        setTimeout(() => notificacion.remove(), 300);
    }, 3000);
}

// Auto-ocultar alertas
setTimeout(() => {
    document.querySelectorAll('.alert').forEach(alert => {
        alert.style.opacity = '0';
        alert.style.transition = 'opacity 0.5s ease';
        setTimeout(() => alert.remove(), 500);
    });
}, 5000);
