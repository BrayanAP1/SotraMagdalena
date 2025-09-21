// Variable global para almacenar los detalles actuales
    let detalleActual = null;
    
    function mostrarDetalles(paquete, precio, fechaFormateada, dims, pesoTxt) {
        detalleActual = paquete;
        detalleActual.precio = precio;
        detalleActual.fechaFormateada = fechaFormateada;
        detalleActual.dims = dims;
        detalleActual.pesoTxt = pesoTxt;
        
        // Llenar el modal con la información
        document.getElementById('modalId').textContent = paquete.id;
        document.getElementById('modalCliente').textContent = paquete.nombre_cliente || '-';
        document.getElementById('modalOrigen').textContent = paquete.direccion_origen || '-';
        document.getElementById('modalDestino').textContent = paquete.direccion_destino || '-';
        document.getElementById('modalContenido').textContent = paquete.contenido || '-';
        document.getElementById('modalPrecio').textContent = precio;
        document.getElementById('modalFecha').textContent = fechaFormateada;
        document.getElementById('modalRango').textContent = paquete.rango || '-';
        document.getElementById('modalTipo').textContent = paquete.tipo || '-';
        
        // Mostrar u ocultar dimensiones/peso según el tipo
        if (paquete.tipo === 'Dimensiones') {
            document.getElementById('modalDimensiones').style.display = 'block';
            document.getElementById('modalPeso').style.display = 'none';
            document.getElementById('modalDimsVal').textContent = dims;
        } else {
            document.getElementById('modalDimensiones').style.display = 'none';
            document.getElementById('modalPeso').style.display = 'block';
            document.getElementById('modalPesoVal').textContent = pesoTxt;
        }
        
        // Mostrar el modal
        var modal = new bootstrap.Modal(document.getElementById('detalleModal'));
        modal.show();
    }
    
    function imprimirFila(boton, paquete, precio, fechaFormateada, dims, pesoTxt) {
        let contenidoImpresion = `
            <h2 class="text-center">Detalle del Paquete</h2>
            <table border="1" cellpadding="10" style="width:100%;border-collapse:collapse;margin-top:20px;">
                <tr><th style="padding:8px;background:#f2f2f2;">ID</th><td style="padding:8px;">#${paquete.id}</td></tr>
                <tr><th style="padding:8px;background:#f2f2f2;">Cliente</th><td style="padding:8px;">${paquete.nombre_cliente || '-'}</td></tr>
                <tr><th style="padding:8px;background:#f2f2f2;">Origen</th><td style="padding:8px;">${paquete.direccion_origen || '-'}</td></tr>
                <tr><th style="padding:8px;background:#f2f2f2;">Destino</th><td style="padding:8px;">${paquete.direccion_destino || '-'}</td></tr>
                <tr><th style="padding:8px;background:#f2f2f2;">Contenido</th><td style="padding:8px;">${paquete.contenido || '-'}</td></tr>
                <tr><th style="padding:8px;background:#f2f2f2;">Especificaciones</th><td style="padding:8px;">${paquete.tipo === 'Dimensiones' ? dims : pesoTxt}</td></tr>
                <tr><th style="padding:8px;background:#f2f2f2;">Precio</th><td style="padding:8px;">$${precio}</td></tr>
                <tr><th style="padding:8px;background:#f2f2f2;">Rango</th><td style="padding:8px;">${paquete.rango || '-'}</td></tr>
                <tr><th style="padding:8px;background:#f2f2f2;">Fecha</th><td style="padding:8px;">${fechaFormateada}</td></tr>
                <tr><th style="padding:8px;background:#f2f2f2;">Tipo</th><td style="padding:8px;">${paquete.tipo || '-'}</td></tr>
            </table>
            <p style="margin-top:20px;text-align:center;font-style:italic;">Generado el ${new Date().toLocaleDateString()}</p>
        `;

        let ventana = window.open('', 'PRINT', 'height=600,width=800');
        ventana.document.write(`
            <html>
                <head>
                    <title>Detalle Paquete #${paquete.id}</title>
                    <style>
                        body { font-family: Arial, sans-serif; padding: 20px; }
                        h2 { color: var(--primary-color); }
                        table { margin: 0 auto; }
                    </style>
                </head>
                <body>${contenidoImpresion}</body>
            </html>
        `);
        ventana.document.close();
        ventana.focus();
        ventana.print();
    }
    
    function imprimirDetalles() {
        if (!detalleActual) return;
        
        let paquete = detalleActual;
        let contenidoImpresion = `
            <h2 class="text-center">Detalle del Paquete #${paquete.id}</h2>
            <div style="display: flex; margin-bottom: 15px;">
                <div style="flex: 1;">
                    <p><strong>Cliente:</strong> ${paquete.nombre_cliente || '-'}</p>
                    <p><strong>Origen:</strong> ${paquete.direccion_origen || '-'}</p>
                    <p><strong>Destino:</strong> ${paquete.direccion_destino || '-'}</p>
                </div>
                <div style="flex: 1;">
                    <p><strong>Contenido:</strong> ${paquete.contenido || '-'}</p>
                    <p><strong>Precio:</strong> $${paquete.precio}</p>
                    <p><strong>Fecha:</strong> ${paquete.fechaFormateada}</p>
                </div>
            </div>
            <div style="margin-top: 15px;">
                <h6>Especificaciones:</h6>
                ${paquete.tipo === 'Dimensiones' ? 
                    `<p><strong>Dimensiones:</strong> ${paquete.dims}</p>` : 
                    `<p><strong>Peso:</strong> ${paquete.pesoTxt}</p>`}
                <p><strong>Rango:</strong> ${paquete.rango || '-'}</p>
                <p><strong>Tipo de cálculo:</strong> ${paquete.tipo || '-'}</p>
            </div>center;font-style:italic;">Generado el ${new Date().toLocaleDateString()}</p>
        `;

        let ventana = window.open('', 'PRINT', 'height=600,width=800');
        ventana.document.write(`
            <html>
                <head>
                    <title>Detalle Paquete #${paquete.id}</title>
                    <style>
                        body { font-family: Arial, sans-serif; padding: 20px; }
                        h2 { color: var(--primary-color); }
                        table { margin: 0 auto; }
                    </style>
                </head>
                <body>${contenidoImpresion}</body>
            </html>
        `);
        ventana.document.close();
        ventana.focus();
        ventana.print();
    }
    
    function imprimirDetalles() {
        if (!detalleActual) return;
        
        let paquete = detalleActual;
        let contenidoImpresion = `
            <h2 class="text-center">Detalle del Paquete #${paquete.id}</h2>
            <div style="display: flex; margin-bottom: 15px;">
                <div style="flex: 1;">
                    <p><strong>Cliente:</strong> ${paquete.nombre_cliente || '-'}</p>
                    <p><strong>Origen:</strong> ${paquete.direccion_origen || '-'}</p>
                    <p><strong>Destino:</strong> ${paquete.direccion_destino || '-'}</p>
                </div>
                <div style="flex: 1;">
                    <p><strong>Contenido:</strong> ${paquete.contenido || '-'}</p>
                    <p><strong>Precio:</strong> $${paquete.precio}</p>
                    <p><strong>Fecha:</strong> ${paquete.fechaFormateada}</p>
                </div>
            </div>
            <div style="margin-top: 15px;">
                <h6>Especificaciones:</h6>
                ${paquete.tipo === 'Dimensiones' ? 
                    `<p><strong>Dimensiones:</strong> ${paquete.dims}</p>` : 
                    `<p><strong>Peso:</strong> ${paquete.pesoTxt}</p>`}
                <p><strong>Rango:</strong> ${paquete.rango || '-'}</p>
                <p><strong>Tipo de cálculo:</strong> ${paquete.tipo || '-'}</p>
            </div>
            <p style="margin-top:20px;text-align:center;font-style:italic;">Generado el ${new Date().toLocaleDateString()}</p>
        `;

        let ventana = window.open('', 'PRINT', 'height=600,width=800');
        ventana.document.write(`
            <html>
                <head>
                    <title>Detalle Paquete #${paquete.id}</title>
                    <style>
                        body { font-family: Arial, sans-serif; padding: 20px; }
                        h2 { color: var(--primary-color); }
                        h6 { color: #6c757d; border-bottom: 1px solid #dee2e6; padding-bottom: 5px; }
                    </style>
                </head>
                <body>${contenidoImpresion}</body>
            </html>
        `);
        ventana.document.close();
        ventana.focus();
        ventana.print();
        
        // Cerrar el modal después de imprimir
        var modal = bootstrap.Modal.getInstance(document.getElementById('detalleModal'));
        modal.hide();
    }