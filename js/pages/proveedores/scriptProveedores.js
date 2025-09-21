// Llenar modal de edición con datos
var modalEditar = document.getElementById('modalEditar');
if (modalEditar) {
    modalEditar.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        document.getElementById('edit-id').value = button.getAttribute('data-id');
        document.getElementById('edit-nombre').value = button.getAttribute('data-nombre');
        document.getElementById('edit-direccion').value = button.getAttribute('data-direccion');
        document.getElementById('edit-telefono').value = button.getAttribute('data-telefono');
        document.getElementById('edit-correo').value = button.getAttribute('data-correo');
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Configurar búsqueda en tiempo real
    const buscarInput = document.getElementById('busquedaRapida');
    if (buscarInput) {
        buscarInput.addEventListener('keyup', function() {
            const texto = this.value.toLowerCase();
            aplicarFiltroRapido(texto);
        });
    }

    // Configurar filtros avanzados
    const btnAplicarFiltros = document.getElementById('btnAplicarFiltros');
    if (btnAplicarFiltros) {
        btnAplicarFiltros.addEventListener('click', aplicarFiltrosAvanzados);
    }

    const btnLimpiarFiltros = document.getElementById('btnLimpiarFiltros');
    if (btnLimpiarFiltros) {
        btnLimpiarFiltros.addEventListener('click', limpiarFiltros);
    }
    inicializarGrafico();
});

// Función para aplicar filtro rápido
function aplicarFiltroRapido(texto) {
    const tabla = document.getElementById('tablaProveedores');
    if (!tabla) return;
    
    const filas = tabla.getElementsByTagName('tr');
    let contador = 0;

    for (let i = 1; i < filas.length; i++) {
        const fila = filas[i];
        const celdas = fila.getElementsByTagName('td');
        let coincide = false;

        for (let j = 0; j < celdas.length; j++) {
            if (celdas[j].textContent.toLowerCase().indexOf(texto) > -1) {
                coincide = true;
                break;
            }
        }

        fila.style.display = coincide ? '' : 'none';
        if (coincide) contador++;
    }

    const contadorResultados = document.getElementById('contadorResultados');
    const contadorResultadosTarjetas = document.getElementById('contadorResultadosTarjetas');
    
    if (contadorResultados) {
        contadorResultados.textContent = `Mostrando ${contador} resultados`;
    }
    
    if (contadorResultadosTarjetas) {
        contadorResultadosTarjetas.textContent = `Mostrando ${contador} resultados`;
    }

    // También filtrar en la vista de tarjetas
    filtrarVistaTarjetas(texto);
}

// Función para aplicar filtros avanzados
function aplicarFiltrosAvanzados() {
    const nombre = document.getElementById('filtroNombre')?.value.toLowerCase() || '';
    const direccion = document.getElementById('filtroDireccion')?.value.toLowerCase() || '';
    const telefono = document.getElementById('filtroTelefono')?.value.toLowerCase() || '';
    const correo = document.getElementById('filtroCorreo')?.value.toLowerCase() || '';
    const fechaDesde = document.getElementById('filtroFechaDesde')?.value || '';
    const fechaHasta = document.getElementById('filtroFechaHasta')?.value || '';

    const tabla = document.getElementById('tablaProveedores');
    if (!tabla) return;
    
    const filas = tabla.getElementsByTagName('tr');
    let contador = 0;

    for (let i = 1; i < filas.length; i++) {
        const fila = filas[i];
        const celdas = fila.getElementsByTagName('td');
        const nombreCell = celdas[1].textContent.toLowerCase();
        const direccionCell = celdas[2].textContent.toLowerCase();
        const telefonoCell = celdas[3].textContent.toLowerCase();
        const correoCell = celdas[4].textContent.toLowerCase();
        const fechaCell = celdas[5].textContent;

        // Convertir fecha de la tabla a formato YYYY-MM-DD para comparación
        const partesFecha = fechaCell.split('/');
        const fechaTabla = partesFecha[2] + '-' + partesFecha[1] + '-' + partesFecha[0];

        let coincide = true;

        // Aplicar filtros
        if (nombre && !nombreCell.includes(nombre)) coincide = false;
        if (direccion && !direccionCell.includes(direccion)) coincide = false;
        if (telefono && !telefonoCell.includes(telefono)) coincide = false;
        if (correo && !correoCell.includes(correo)) coincide = false;

        // Filtros de fecha
        if (fechaDesde && fechaTabla < fechaDesde) coincide = false;
        if (fechaHasta && fechaTabla > fechaHasta) coincide = false;

        fila.style.display = coincide ? '' : 'none';
        if (coincide) contador++;
    }

    const contadorResultados = document.getElementById('contadorResultados');
    const contadorResultadosTarjetas = document.getElementById('contadorResultadosTarjetas');
    
    if (contadorResultados) {
        contadorResultados.textContent = `Mostrando ${contador} resultados`;
    }
    
    if (contadorResultadosTarjetas) {
        contadorResultadosTarjetas.textContent = `Mostrando ${contador} resultados`;
    }

    // También aplicar filtros a la vista de tarjetas
    aplicarFiltrosAvanzadosTarjetas(nombre, direccion, telefono, correo, fechaDesde, fechaHasta);
}

// Función para limpiar todos los filtros
function limpiarFiltros() {
    document.getElementById('filtroNombre').value = '';
    document.getElementById('filtroDireccion').value = '';
    document.getElementById('filtroTelefono').value = '';
    document.getElementById('filtroCorreo').value = '';
    document.getElementById('filtroFechaDesde').value = '';
    document.getElementById('filtroFechaHasta').value = '';
    document.getElementById('busquedaRapida').value = '';

    const tabla = document.getElementById('tablaProveedores');
    if (!tabla) return;
    
    const filas = tabla.getElementsByTagName('tr');
    const totalFilas = filas.length - 1;

    for (let i = 1; i < filas.length; i++) {
        filas[i].style.display = '';
    }

    const contadorResultados = document.getElementById('contadorResultados');
    const contadorResultadosTarjetas = document.getElementById('contadorResultadosTarjetas');
    
    if (contadorResultados) {
        contadorResultados.textContent = `Mostrando ${totalFilas} resultados`;
    }
    
    if (contadorResultadosTarjetas) {
        contadorResultadosTarjetas.textContent = `Mostrando ${totalFilas} resultados`;
    }

    // También limpiar filtros en la vista de tarjetas
    const tarjetas = document.querySelectorAll('#contenedorTarjetas > div');
    tarjetas.forEach(tarjeta => {
        tarjeta.style.display = '';
    });
}

// Función para ordenar tabla
function sortTable(colIndex, asc = true) {
    let tabla = document.getElementById("tablaProveedores");
    if (!tabla) return;
    
    let tbody = tabla.querySelector("tbody");
    let filas = Array.from(tbody.querySelectorAll("tr"));

    filas.sort((a, b) => {
        let aText = a.children[colIndex].innerText.trim().toLowerCase();
        let bText = b.children[colIndex].innerText.trim().toLowerCase();

        // Detectar números y fechas para orden correcto
        if (!isNaN(aText) && !isNaN(bText)) {
            return asc ? aText - bText : bText - aText;
        }

        // Para fechas en formato dd/mm/yyyy
        if (aText.includes('/') && bText.includes('/')) {
            const aDateParts = aText.split('/');
            const bDateParts = bText.split('/');
            const aDate = new Date(aDateParts[2], aDateParts[1] - 1, aDateParts[0]);
            const bDate = new Date(bDateParts[2], bDateParts[1] - 1, bDateParts[0]);
            return asc ? aDate - bDate : bDate - aDate;
        }

        return asc ? aText.localeCompare(bText) : bText.localeCompare(aText);
    });

    filas.forEach(fila => tbody.appendChild(fila));
}

// Función para exportar a PDF
function exportToPDF() {
    try {
        // Crear instancia de jsPDF
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

        // Añadir título
        doc.setFontSize(18);
        doc.setTextColor(42, 182, 75);
        doc.text('Lista de Proveedores - SOTRA Magdalena', 14, 15);

        doc.setFontSize(12);
        doc.setTextColor(100, 100, 100);
        doc.text(`Fecha de exportación: ${new Date().toLocaleDateString()}`, 14, 22);

        // Obtener datos de la tabla
        const table = document.getElementById('tablaProveedores');
        if (!table) return;
        
        const data = [];

        // Obtener solo las filas visibles (filtradas)
        for (let i = 1; i < table.rows.length; i++) {
            const row = table.rows[i];
            if (row.style.display !== 'none') {
                const rowData = [];

                for (let j = 0; j < row.cells.length - 2; j++) { // Excluir columnas de estado y acciones
                    rowData.push(row.cells[j].textContent.trim());
                }

                data.push(rowData);
            }
        }

        // Configurar autoTable
        doc.autoTable({
            startY: 30,
            head: [
                ['ID', 'Nombre', 'Dirección', 'Teléfono', 'Correo', 'Fecha Registro']
            ],
            body: data,
            theme: 'grid',
            headStyles: {
                fillColor: [42, 182, 75],
                textColor: [255, 255, 255],
                fontStyle: 'bold'
            },
            alternateRowStyles: {
                fillColor: [240, 240, 240]
            },
            styles: {
                fontSize: 9,
                cellPadding: 3
            }
        });

        // Descargar PDF
        const fileName = `proveedores_sotra_magdalena_${new Date().toISOString().split('T')[0]}.pdf`;
        doc.save(fileName);

        // Mostrar notificación
        mostrarNotificacion('PDF exportado correctamente', 'success');

    } catch (error) {
        console.error('Error al exportar a PDF:', error);
        mostrarNotificacion('Error al exportar a PDF', 'error');
    }
}

// Función para mostrar notificación
function mostrarNotificacion(mensaje, tipo) {
    const toast = document.querySelector('.custom-toast');
    if (!toast) return;
    
    toast.querySelector('.toast-body').textContent = mensaje;
    toast.classList.remove('alert-success', 'alert-danger');

    if (tipo === 'success') {
        toast.classList.add('alert-success');
    } else {
        toast.classList.add('alert-danger');
    }

    toast.classList.add('show');

    // Ocultar automáticamente después de 3 segundos
    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}

// Función para mostrar vista de tabla
function mostrarVistaTabla() {
    document.getElementById('vistaTabla').classList.remove('d-none');
    document.getElementById('vistaTarjetas').classList.add('d-none');

    // Actualizar botones de toggle
    document.querySelectorAll('.btn-group .btn').forEach(btn => {
        btn.classList.remove('active');
    });
    const btnTabla = document.querySelector('[onclick="mostrarVistaTabla()"]');
    if (btnTabla) {
        btnTabla.classList.add('active');
    }
}

// Función para mostrar vista de tarjetas
function mostrarVistaTarjetas() {
    document.getElementById('vistaTabla').classList.add('d-none');
    document.getElementById('vistaTarjetas').classList.remove('d-none');

    // Actualizar botones de toggle
    document.querySelectorAll('.btn-group .btn').forEach(btn => {
        btn.classList.remove('active');
    });
    const btnTarjetas = document.querySelector('[onclick="mostrarVistaTarjetas()"]');
    if (btnTarjetas) {
        btnTarjetas.classList.add('active');
    }
}

// Función para filtrar vista de tarjetas
function filtrarVistaTarjetas(texto) {
    const tarjetas = document.querySelectorAll('#contenedorTarjetas > div');
    let contador = 0;

    tarjetas.forEach(tarjeta => {
        const contenido = tarjeta.textContent.toLowerCase();
        if (contenido.includes(texto)) {
            tarjeta.style.display = '';
            contador++;
        } else {
            tarjeta.style.display = 'none';
        }
    });

    const contadorResultadosTarjetas = document.getElementById('contadorResultadosTarjetas');
    if (contadorResultadosTarjetas) {
        contadorResultadosTarjetas.textContent = `Mostrando ${contador} resultados`;
    }
}

// Función para aplicar filtros avanzados a tarjetas
function aplicarFiltrosAvanzadosTarjetas(nombre, direccion, telefono, correo, fechaDesde, fechaHasta) {
    const tarjetas = document.querySelectorAll('#contenedorTarjetas > div');
    let contador = 0;

    tarjetas.forEach(tarjeta => {
        const nombreText = tarjeta.querySelector('.card-title')?.textContent.toLowerCase() || '';
        const direccionText = tarjeta.querySelectorAll('.card-body > div')[0]?.textContent.toLowerCase() || '';
        const telefonoText = tarjeta.querySelectorAll('.card-body > div')[1]?.textContent.toLowerCase() || '';
        const correoText = tarjeta.querySelectorAll('.card-body > div')[2]?.textContent.toLowerCase() || '';
        const fechaElement = tarjeta.querySelectorAll('.d-flex.justify-content-between span')[0];
        const fechaText = fechaElement ? fechaElement.textContent.toLowerCase() : '';

        let coincide = true;

        // Aplicar filtros
        if (nombre && !nombreText.includes(nombre)) coincide = false;
        if (direccion && !direccionText.includes(direccion)) coincide = false;
        if (telefono && !telefonoText.includes(telefono)) coincide = false;
        if (correo && !correoText.includes(correo)) coincide = false;

        // Filtros de fecha (si hay fecha en la tarjeta)
        if (fechaText && fechaDesde && fechaHasta) {
            // Extraer fecha en formato YYYY-MM-DD
            const fechaParts = fechaText.split(': ')[1]?.split('/');
            if (fechaParts && fechaParts.length === 3) {
                const fechaTarjeta = fechaParts[2] + '-' + fechaParts[1] + '-' + fechaParts[0];
                if (fechaDesde && fechaTarjeta < fechaDesde) coincide = false;
                if (fechaHasta && fechaTarjeta > fechaHasta) coincide = false;
            }
        }

        if (coincide) {
            tarjeta.style.display = '';
            contador++;
        } else {
            tarjeta.style.display = 'none';
        }
    });

    const contadorResultadosTarjetas = document.getElementById('contadorResultadosTarjetas');
    if (contadorResultadosTarjetas) {
        contadorResultadosTarjetas.textContent = `Mostrando ${contador} resultados`;
    }
}

// Función para filtrar por estado
function filtrarPorEstado(estado) {
    mostrarNotificacion(`Filtrando por: ${estado}`, 'success');
}

// Cerrar automáticamente las alertas después de 5 segundos
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
    });
}, 5000);

function inicializarGrafico() {
    const ctx = document.getElementById('proveedoresChart');
    if (!ctx) return;
    
    const meses = ctx.getAttribute('data-meses') ? JSON.parse(ctx.getAttribute('data-meses')) : [];
    const totales = ctx.getAttribute('data-totales') ? JSON.parse(ctx.getAttribute('data-totales')) : [];

    const rootStyles = getComputedStyle(document.documentElement);
    const primaryColor = rootStyles.getPropertyValue('--primary-color').trim();
    const primaryDark = rootStyles.getPropertyValue('--primary-dark').trim();

    const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, primaryColor + '33');
    gradient.addColorStop(1, primaryDark + '00');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: meses,
            datasets: [{
                label: 'Proveedores registrados',
                data: totales,
                backgroundColor: gradient,
                borderColor: primaryColor,
                borderWidth: 2,
                pointBackgroundColor: primaryDark,
                pointBorderColor: '#fff',
                pointRadius: 5,
                pointHoverRadius: 7,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}