/* [LOCK-MODULE: JS-COTIZADOR] - NO MODIFICAR ESTE BLOQUE A MENOS QUE EL USUARIO LO ESPECIFIQUE */

document.addEventListener('DOMContentLoaded', () => {
    // Referencias al DOM
    const sliderHoras = document.getElementById('slider_horas');
    const inputHorasNum = document.getElementById('input_horas_num');
    const sliderGramos = document.getElementById('slider_gramos');
    const inputGramosNum = document.getElementById('input_gramos_num');
    const selectFilamento = document.getElementById('select_filamento');
    const inputCostoGramo = document.getElementById('input_costo_gramo');
    const inputPotencia = document.getElementById('input_potencia');
    const inputTarifa = document.getElementById('input_tarifa');
    const inputFalla = document.getElementById('input_falla');
    const inputDesgaste = document.getElementById('input_desgaste');
    const sliderMargen = document.getElementById('slider_margen');
    const displayMargen = document.getElementById('display_margen');

    // Displays
    const displayPrecioSugerido = document.getElementById('display_precio_sugerido');
    const displayPrecioLoteTotal = document.getElementById('display_precio_lote_total');
    const displayCostoBase = document.getElementById('display_costo_base');
    const displayGanancia = document.getElementById('display_ganancia');
    const badgeModo = document.getElementById('badge_modo');

    // Desglose
    const detailCostoEnergia = document.getElementById('detail_costo_energia');
    const detailFormulaEnergia = document.getElementById('detail_formula_energia');
    const detailCostoMaterial = document.getElementById('detail_costo_material');
    const detailFormulaMaterial = document.getElementById('detail_formula_material');
    const detailFactorFalla = document.getElementById('detail_factor_falla');
    const detailHorasTotales = document.getElementById('detail_horas_totales');
    const detailGramosTotales = document.getElementById('detail_gramos_totales');
    const detailGananciaTotal = document.getElementById('detail_ganancia_total');

    // Botones de lote
    const batchButtons = document.querySelectorAll('.batch-btn');

    // Estado local
    let currentBatchQty = 1;
    let currentBatchDiscount = 0.0;

    const formatARS = (val) => {
        return new Intl.NumberFormat('es-AR', {
            style: 'currency',
            currency: 'ARS',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(val);
    };

    /**
     * Motor de Cálculo de Cotización 3D y Energía
     * Cumple con las fórmulas del Manifiesto de Arquitectura Fase 2:
     * - Costo Energía = (Potencia / 1000) * Horas * Tarifa kWh
     * - Costo Material = Gramos * Costo por Gramo
     * - Costo Base = (Costo Energía + Costo Material + Desgaste) * (1 + Tasa Falla)
     * - Precio Sugerido = Costo Base / (1 - Margen de Ganancia)
     */
    function recalcular() {
        const horas = parseFloat(inputHorasNum.value) || 0;
        const gramos = parseFloat(inputGramosNum.value) || 0;
        const costoPorGramo = parseFloat(inputCostoGramo.value) || 0;
        const potenciaW = parseFloat(inputPotencia.value) || 0;
        const tarifaKwh = parseFloat(inputTarifa.value) || 0;
        const tasaFallaPct = parseFloat(inputFalla.value) || 0;
        const desgaste = parseFloat(inputDesgaste.value) || 0;
        const margenPct = parseFloat(sliderMargen.value) || 40;

        displayMargen.textContent = `${margenPct}%`;

        // 1. Costo Energía Eléctrica
        const costoEnergiaUnitario = (potenciaW / 1000.0) * horas * tarifaKwh;

        // 2. Costo Material (Filamento)
        const costoMaterialUnitario = gramos * costoPorGramo;

        // 3. Costo Base de Producción
        const subtotal = costoEnergiaUnitario + costoMaterialUnitario + desgaste;
        const factorFalla = 1.0 + (tasaFallaPct / 100.0);
        const costoBaseUnitario = subtotal * factorFalla;

        // 4. Precio Sugerido Unitario de Venta
        const factorMargen = Math.min(0.99, Math.max(0.01, margenPct / 100.0));
        const precioSugeridoUnitario = costoBaseUnitario / (1.0 - factorMargen);
        const gananciaNetaUnitaria = precioSugeridoUnitario - costoBaseUnitario;

        // 5. Aplicación a Escala / Lote Mayorista
        const factorDescuentoLote = 1.0 - (currentBatchDiscount / 100.0);
        const precioUnitarioConDescuento = precioSugeridoUnitario * factorDescuentoLote;
        const precioTotalLote = precioUnitarioConDescuento * currentBatchQty;
        const costoTotalLote = costoBaseUnitario * currentBatchQty;
        const gananciaTotalLote = precioTotalLote - costoTotalLote;

        // Actualizar UI
        displayPrecioSugerido.textContent = formatARS(precioUnitarioConDescuento);
        displayCostoBase.textContent = formatARS(costoBaseUnitario);
        displayGanancia.textContent = formatARS(gananciaNetaUnitaria);

        if (currentBatchQty > 1) {
            badgeModo.textContent = `Lote ${currentBatchQty}u (-${currentBatchDiscount}%)`;
            badgeModo.className = "text-[11px] font-mono font-semibold px-2 py-0.5 rounded bg-emerald-950/80 text-emerald-300 border border-emerald-800";
            displayPrecioLoteTotal.textContent = `Total lote: ${formatARS(precioTotalLote)}`;
        } else {
            badgeModo.textContent = "Unidad (1u)";
            badgeModo.className = "text-[11px] font-mono font-semibold px-2 py-0.5 rounded bg-orange-950/80 text-orange-300 border border-orange-800";
            displayPrecioLoteTotal.textContent = "";
        }

        // Desglose
        detailCostoEnergia.textContent = formatARS(costoEnergiaUnitario);
        detailFormulaEnergia.textContent = `(${potenciaW}W / 1000) * ${horas.toFixed(1)}hs * ${formatARS(tarifaKwh)}/kWh`;

        detailCostoMaterial.textContent = formatARS(costoMaterialUnitario);
        detailFormulaMaterial.textContent = `${gramos.toFixed(1)}g * ${formatARS(costoPorGramo)}/g`;

        detailFactorFalla.textContent = `+ ${tasaFallaPct.toFixed(1)}% scrap`;

        detailHorasTotales.textContent = `${(horas * currentBatchQty).toFixed(1)} hs`;
        detailGramosTotales.textContent = `${(gramos * currentBatchQty).toFixed(1)} g`;
        detailGananciaTotal.textContent = formatARS(gananciaTotalLote);
    }

    // Sincronización Horas
    sliderHoras.addEventListener('input', (e) => {
        inputHorasNum.value = parseFloat(e.target.value).toFixed(1);
        recalcular();
    });
    inputHorasNum.addEventListener('input', (e) => {
        sliderHoras.value = e.target.value;
        recalcular();
    });

    // Sincronización Gramos
    sliderGramos.addEventListener('input', (e) => {
        inputGramosNum.value = e.target.value;
        recalcular();
    });
    inputGramosNum.addEventListener('input', (e) => {
        sliderGramos.value = e.target.value;
        recalcular();
    });

    // Filamento select
    selectFilamento.addEventListener('change', (e) => {
        if (e.target.value !== 'custom') {
            inputCostoGramo.value = parseFloat(e.target.value).toFixed(2);
        }
        recalcular();
    });
    inputCostoGramo.addEventListener('input', () => {
        selectFilamento.value = 'custom';
        recalcular();
    });

    // Sliders & Controles secundarios
    sliderMargen.addEventListener('input', recalcular);
    inputPotencia.addEventListener('input', recalcular);
    inputTarifa.addEventListener('input', recalcular);
    inputFalla.addEventListener('input', recalcular);
    inputDesgaste.addEventListener('input', recalcular);

    // Selección de lote mayorista
    batchButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            batchButtons.forEach(b => {
                b.className = "batch-btn py-2 px-3 rounded-xl text-xs font-bold font-mono border border-slate-800 bg-slate-950 text-slate-400 hover:text-white";
            });
            btn.className = "batch-btn py-2 px-3 rounded-xl text-xs font-bold font-mono border border-orange-500 bg-orange-600/20 text-orange-400";
            currentBatchQty = parseInt(btn.dataset.qty, 10);
            currentBatchDiscount = parseFloat(btn.dataset.disc);
            recalcular();
        });
    });

    // Intentar sincronizar parámetros iniciales desde /api/cotizador.php
    fetch('/api/cotizador.php')
        .then(res => res.json())
        .then(data => {
            if (data && data.status === 'success' && data.configuracion) {
                const cfg = data.configuracion;
                if (cfg.tarifa_kwh) inputTarifa.value = parseFloat(cfg.tarifa_kwh).toFixed(2);
                if (cfg.potencia_promedio_w) inputPotencia.value = cfg.potencia_promedio_w;
                if (cfg.tasa_falla_estimada) inputFalla.value = parseFloat(cfg.tasa_falla_estimada).toFixed(1);
                if (cfg.margen_ganancia_defecto) {
                    sliderMargen.value = cfg.margen_ganancia_defecto;
                    displayMargen.textContent = `${cfg.margen_ganancia_defecto}%`;
                }
            }
            recalcular();
        })
        .catch(() => {
            // Si el backend no está disponible en este momento, calcular con defaults
            recalcular();
        });

    // Cálculo inicial
    recalcular();
});
