// Función para inicializar la gráfica de distribución
function inicializarGraficaDistribucion(totalDimensiones, totalPeso) {
    const ctxDistribucion = document.getElementById('graficaDistribucion');
    if (!ctxDistribucion) return;
    
    new Chart(ctxDistribucion.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['Por Dimensiones', 'Por Peso'],
            datasets: [{
                data: [totalDimensiones, totalPeso],
                backgroundColor: ['#2ab64b', '#c2db34'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            let value = context.raw || 0;
                            let total = context.dataset.data.reduce((a, b) => a + b, 0);
                            let percentage = Math.round((value / total) * 100);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

// Función para inicializar la gráfica de tendencia mensual
function inicializarGraficaTendencia(labels, datosDimensiones, datosPeso) {
    const ctxTendencia = document.getElementById('graficaTendencia');
    if (!ctxTendencia) return;
    
    new Chart(ctxTendencia.getContext('2d'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                    label: 'Por Dimensiones',
                    data: datosDimensiones,
                    backgroundColor: 'rgba(42, 182, 75, 0.1)',
                    borderColor: '#2ab64b',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                },
                {
                    label: 'Por Peso',
                    data: datosPeso,
                    backgroundColor: 'rgba(194, 219, 52, 0.1)',
                    borderColor: '#c2db34',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Cantidad de Envíos'
                    }
                }
            }
        }
    });
}

// Inicializar todas las gráficas cuando el documento esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Verificar si las variables globales están definidas
    if (typeof chartDataDistribucion !== 'undefined') {
        inicializarGraficaDistribucion(
            chartDataDistribucion.totalDimensiones, 
            chartDataDistribucion.totalPeso
        );
    }
    
    if (typeof chartDataTendencia !== 'undefined') {
        inicializarGraficaTendencia(
            chartDataTendencia.labels,
            chartDataTendencia.datosDimensiones,
            chartDataTendencia.datosPeso
        );
    }
});