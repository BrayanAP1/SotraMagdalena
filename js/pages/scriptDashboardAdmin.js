// Gráfica de envíos mensuales
const ctxEnvios = document.getElementById('graficaEnviosMensuales').getContext('2d');
new Chart(ctxEnvios, {
    type: 'bar',
    data: {
        labels: meses,
        datasets: [
            {
                label: 'Por Dimensiones',
                data: datosDimensiones,
                backgroundColor: '#2ab64b',
                borderWidth: 1
            },
            {
                label: 'Por Peso',
                data: datosPeso,
                backgroundColor: '#c2db34',
                borderWidth: 1
            }
        ]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Cantidad de Envíos'
                }
            },
            x: {
                title: {
                    display: true,
                    text: 'Meses'
                }
            }
        }
    }
});

// Gráfica de distribución de paquetes
const ctxDistribucion = document.getElementById('graficaDistribucion').getContext('2d');
new Chart(ctxDistribucion, {
    type: 'doughnut',
    data: {
        labels: ['Por Dimensiones', 'Por Peso'],
        datasets: [{
            data: [totalDim, totalPeso],
            backgroundColor: ['#2ab64b', '#c2db34'],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Gráfica de estado de usuarios
const ctxEstadoUsuarios = document.getElementById('graficaEstadoUsuarios').getContext('2d');
new Chart(ctxEstadoUsuarios, {
    type: 'pie',
    data: {
        labels: ['Activos', 'Inactivos'],
        datasets: [{
            data: [usuariosActivos, usuariosInactivos],
            backgroundColor: ['#2ab64b', '#6c757d'],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
