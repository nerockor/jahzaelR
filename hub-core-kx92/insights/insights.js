/* [LOCK-MODULE: JS-INSIGHTS] - NO MODIFICAR ESTE BLOQUE A MENOS QUE EL USUARIO LO ESPECIFIQUE */

document.addEventListener('DOMContentLoaded', () => {
    const listHora = document.getElementById('insights_top_hora');
    const listGramo = document.getElementById('insights_top_gramo');
    const listVolumen = document.getElementById('insights_top_volumen');

    // Helper para formato de moneda
    const formatARS = (num) => {
        return new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' }).format(num);
    };

    async function cargarInsights() {
        try {
            const res = await fetch('/api/insights.php?action=dashboard_data');
            const result = await res.json();

            if (result.status === 'success') {
                window.insightsData = result.data;
                renderSplitLista(
                    document.getElementById('insights_top_hora'), 
                    document.getElementById('insights_perdidas_hora'), 
                    result.data.top_hora, 'rentabilidad_por_hora', formatARS, '/h'
                );
                renderSplitLista(
                    document.getElementById('insights_top_gramo'), 
                    document.getElementById('insights_perdidas_gramo'), 
                    result.data.top_gramo, 'rentabilidad_por_gramo', formatARS, '/g'
                );
                renderSplitLista(
                    document.getElementById('insights_top_volumen'), 
                    document.getElementById('insights_perdidas_volumen'), 
                    result.data.top_volumen, 'total_vendido', (v) => v + ' uds', ''
                );
            } else {
                console.error("Error al cargar insights:", result.message);
                listHora.innerHTML = '<div class="text-red-400 text-xs">Error al cargar datos.</div>';
                listGramo.innerHTML = '<div class="text-red-400 text-xs">Error al cargar datos.</div>';
                listVolumen.innerHTML = '<div class="text-red-400 text-xs">Error al cargar datos.</div>';
            }
        } catch (err) {
            console.error("Error de red:", err);
        }
    }

    function renderItemHTML(item, index, propClave, formatValor, sufijo) {
        const isFirst = index === 0 && item.ganancia_total >= 0;
        const esPerdida = item.ganancia_total < 0;
        
        const colorClave = esPerdida ? 'text-red-500' : 'text-emerald-400';
        const colorBg = esPerdida 
            ? 'border-red-900/50 bg-red-950/40 shadow-[inset_0_0_20px_rgba(153,27,27,0.2)]' 
            : (isFirst ? 'border-orange-500/30 bg-gradient-to-r from-orange-900/10 to-transparent' : 'border-slate-800/80 bg-slate-950/80');
        const alertBadge = esPerdida 
            ? '<span class="ml-2 inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-black tracking-widest bg-red-900/80 text-red-200 border border-red-700 animate-pulse">PÉRDIDA</span>' 
            : '';

        return `
            <div class="flex items-center justify-between p-3 rounded-xl border ${colorBg} transition-colors">
                <div class="flex items-center gap-3 truncate pr-4">
                    <div class="w-6 h-6 rounded bg-slate-900 border border-slate-700 flex items-center justify-center text-xs font-bold font-mono text-slate-400 shrink-0">
                        ${index + 1}
                    </div>
                    <div class="truncate">
                        <h4 class="text-sm font-bold text-white truncate flex items-center" title="${item.nombre}">
                            ${item.nombre} ${alertBadge}
                        </h4>
                        <p class="text-[10px] text-slate-500 font-mono mt-0.5">Total Vendido: ${item.total_vendido}</p>
                    </div>
                </div>
                <div class="text-right shrink-0">
                    <div class="text-xs font-mono font-bold ${colorClave}">
                        ${formatValor(item[propClave])}${sufijo}
                    </div>
                    <div class="text-[10px] ${esPerdida ? 'text-red-400 font-bold' : 'text-slate-500'} font-mono mt-0.5">Ganancia: ${formatARS(item.ganancia_total)}</div>
                </div>
            </div>
        `;
    }

    function renderSplitLista(containerWinners, containerLosers, arrayDatos, propClave, formatValor, sufijo) {
        if (!arrayDatos || arrayDatos.length === 0) {
            containerWinners.innerHTML = '<div class="text-center text-slate-500 font-mono text-xs py-8">No hay suficientes datos de ventas.</div>';
            containerLosers.innerHTML = '';
            return;
        }

        const winners = arrayDatos.filter(item => item.ganancia_total >= 0).slice(0, 10);
        const losers = arrayDatos.filter(item => item.ganancia_total < 0);

        containerWinners.innerHTML = winners.length > 0 
            ? winners.map((item, index) => renderItemHTML(item, index, propClave, formatValor, sufijo)).join('')
            : '<div class="text-center text-slate-500 font-mono text-xs py-8">No hay productos en ganancia.</div>';

        if (losers.length > 0) {
            // Find the original indices for the losers so the rank numbers are accurate
            containerLosers.innerHTML = losers.map(item => {
                const originalIndex = arrayDatos.indexOf(item);
                return renderItemHTML(item, originalIndex, propClave, formatValor, sufijo);
            }).join('');
        } else {
            containerLosers.innerHTML = '';
        }
    }

    cargarInsights();
});

// PDF Generation Logic
window.generarPDF = function(tipo) {
    if (!window.insightsData) {
        alert("Los datos aún no se han cargado. Por favor, espera unos segundos.");
        return;
    }

    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    
    // Configurar título y datos según el tipo de reporte
    let titulo = "";
    let dataList = [];
    let valorColumna = "";
    let propValor = "";
    let sufijo = "";

    if (tipo === 'hora') {
        titulo = "Reporte de Rentabilidad por Hora de Máquina";
        dataList = window.insightsData.top_hora;
        valorColumna = "Rentabilidad ($/h)";
        propValor = "rentabilidad_por_hora";
        sufijo = "/h";
    } else if (tipo === 'gramo') {
        titulo = "Reporte de Rentabilidad por Gramo de Material";
        dataList = window.insightsData.top_gramo;
        valorColumna = "Rentabilidad ($/g)";
        propValor = "rentabilidad_por_gramo";
        sufijo = "/g";
    } else if (tipo === 'volumen') {
        titulo = "Reporte de Productos Más Vendidos";
        dataList = window.insightsData.top_volumen;
        valorColumna = "Unidades Vendidas";
        propValor = "total_vendido";
        sufijo = " uds";
    }

    // Formateadores
    const formatARS = (num) => new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' }).format(num);
    const formatNum = (num) => new Intl.NumberFormat('es-AR').format(num);

    // Preparar filas para la tabla
    const bodyRows = dataList.map((item, index) => {
        let valFmt = "";
        if (tipo === 'volumen') {
            valFmt = formatNum(item[propValor]) + sufijo;
        } else {
            valFmt = formatARS(item[propValor]) + sufijo;
        }

        return [
            (index + 1).toString(),
            item.nombre,
            formatNum(item.horas_totales) + " h",
            formatNum(item.total_vendido) + " uds",
            formatARS(item.ganancia_total),
            valFmt
        ];
    });

    // Diseño del PDF
    doc.setFontSize(18);
    doc.setTextColor(15, 23, 42); // slate-900
    doc.text("3D Farm Manager", 14, 22);
    
    doc.setFontSize(12);
    doc.setTextColor(100, 116, 139); // slate-500
    doc.text(titulo, 14, 30);
    
    const fechaStr = new Date().toLocaleDateString('es-AR') + ' ' + new Date().toLocaleTimeString('es-AR');
    doc.setFontSize(10);
    doc.text("Generado: " + fechaStr, 14, 36);

    // Configurar AutoTable
    doc.autoTable({
        startY: 42,
        head: [['#', 'Producto', 'Horas Invertidas', 'Ventas', 'Ganancia Neta', valorColumna]],
        body: bodyRows,
        theme: 'striped',
        headStyles: { fillColor: [15, 23, 42] },
        styles: { fontSize: 9 },
        columnStyles: {
            0: { halign: 'center', cellWidth: 10 },
            2: { halign: 'right' },
            3: { halign: 'right' },
            4: { halign: 'right' },
            5: { halign: 'right', fontStyle: 'bold' }
        },
        willDrawCell: function(data) {
            // Resaltar en rojo si hay pérdida
            if (data.row.section === 'body') {
                const gananciaRaw = dataList[data.row.index].ganancia_total;
                if (gananciaRaw < 0) {
                    data.cell.styles.textColor = [220, 38, 38]; // red-600
                }
            }
        }
    });

    // Descargar
    doc.save(`Reporte_Insights_${tipo}_${new Date().getTime()}.pdf`);
};
