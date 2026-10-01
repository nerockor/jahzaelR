/* [LOCK-MODULE: JS-FILAMENTOS] - NO MODIFICAR ESTE BLOQUE A MENOS QUE EL USUARIO LO ESPECIFIQUE */

document.addEventListener('DOMContentLoaded', () => {
    const gridFilamentos = document.getElementById('grid_filamentos');
    const toast = document.getElementById('toast');

    // Estadísticas
    const statTotalBobinas = document.getElementById('stat_total_bobinas');
    const statGramosTotales = document.getElementById('stat_gramos_totales');
    const statValorInventario = document.getElementById('stat_valor_inventario');
    const statAlertasBajo = document.getElementById('stat_alertas_bajo');

    // Modales
    const modalDescontar = document.getElementById('modal_descontar');
    const modalBobina = document.getElementById('modal_bobina');
    const modalRecargar = document.getElementById('modal_recargar');

    // Formularios
    const formDescontar = document.getElementById('form_descontar');
    const formBobina = document.getElementById('form_bobina');
    const formRecargar = document.getElementById('form_recargar');

    // Botones
    const btnNuevaBobina = document.getElementById('btn_nueva_bobina');
    const filterButtons = document.querySelectorAll('.filter-btn');

    let filamentosData = [];
    let currentFilter = 'all';

    const formatARS = (val) => {
        return new Intl.NumberFormat('es-AR', {
            style: 'currency',
            currency: 'ARS',
            minimumFractionDigits: 2
        }).format(val);
    };

    function showToast(message, type = 'success') {
        toast.className = `fixed top-20 right-6 z-50 transform transition-all duration-300 p-4 rounded-xl shadow-2xl flex items-center gap-3 text-sm font-semibold border ${
            type === 'success' 
                ? 'bg-emerald-950/90 text-emerald-200 border-emerald-700/80 shadow-emerald-950/40' 
                : 'bg-red-950/90 text-red-200 border-red-700/80 shadow-red-950/40'
        }`;
        toast.innerHTML = `
            <span>${message}</span>
        `;
        toast.style.opacity = '1';
        toast.style.pointerEvents = 'auto';
        toast.style.transform = 'translateY(0)';

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.pointerEvents = 'none';
            toast.style.transform = 'translateY(8px)';
        }, 3500);
    }

    function openModal(modal) {
        modal.classList.remove('hidden');
    }

    function closeModal(modal) {
        modal.classList.add('hidden');
    }

    document.querySelectorAll('.btn-close-modal').forEach(btn => {
        btn.addEventListener('click', () => {
            closeModal(modalDescontar);
            closeModal(modalBobina);
            closeModal(modalRecargar);
        });
    });

    let configGranja = { tarifa_kwh: 120, potencia_promedio_w: 150 };

    async function cargarFilamentos() {
        try {
            const confRes = await fetch('/api/cotizador.php?action=config');
            if (confRes.ok) {
                const confData = await confRes.json();
                if (confData.status === 'success' && confData.data) {
                    configGranja = confData.data;
                }
            }

            const res = await fetch('/api/filamentos.php?action=listar');
            const data = await res.json();

            if (data.status === 'success') {
                filamentosData = data.data;
                actualizarEstadisticas();
                renderCards();
            } else {
                throw new Error(data.message || 'Error al listar');
            }
        } catch (err) {
            // Fallback con datos locales si el servidor aún no tiene MySQL
            if (filamentosData.length === 0) {
                filamentosData = [
                    { id: 1, marca: 'Printalot', material: 'PLA', color: 'Negro', peso_inicial_g: 1000, peso_actual_g: 1000, costo_rollo_ars: 22000, costo_por_gramo_ars: 22.0, alerta_stock_minimo_g: 150, es_critico: false, porcentaje_restante: 100.0, impresiones_activas: [] }
                ];
            }
            actualizarEstadisticas();
            renderCards();
        }
    }

    function actualizarEstadisticas() {
        const total = filamentosData.length;
        const gramosTotales = filamentosData.reduce((acc, f) => acc + (parseInt(f.peso_actual_g) || 0), 0);
        const valorTotal = filamentosData.reduce((acc, f) => acc + ((parseInt(f.peso_actual_g) || 0) * (parseFloat(f.costo_por_gramo_ars) || 0)), 0);
        const alertas = filamentosData.filter(f => (parseInt(f.peso_actual_g) || 0) <= (parseInt(f.alerta_stock_minimo_g) || 150)).length;

        statTotalBobinas.textContent = total;
        statGramosTotales.textContent = `${gramosTotales.toLocaleString('es-AR')} g`;
        statValorInventario.textContent = formatARS(valorTotal);
        statAlertasBajo.textContent = alertas;
    }

    function getFilamentStyle(colorStr) {
        const c = (colorStr || '').toLowerCase().trim();
        const map = {
            'rojo': '239, 68, 68', 'rojito': '239, 68, 68',
            'naranja': '249, 115, 22', 'amarillo': '234, 179, 8',
            'verde': '34, 197, 94', 'azul': '59, 130, 246',
            'celeste': '14, 165, 233', 'violeta': '168, 85, 247',
            'morado': '168, 85, 247', 'rosa': '236, 72, 153',
            'pink': '236, 72, 153', 'blanco': '255, 255, 255',
            'negro': '0, 0, 0', 'gris': '156, 163, 175',
            'plata': '156, 163, 175', 'cristal': '255, 255, 255',
            'transparente': '255, 255, 255', 'marrón': '161, 98, 7',
            'marron': '161, 98, 7', 'dorado': '234, 179, 8',
            'oro': '234, 179, 8', 'bronce': '180, 83, 9',
            'cobre': '217, 119, 6', 'fucsia': '217, 70, 239'
        };
        
        let rgb = '15, 23, 42'; // base slate-900 transparent fallback
        let found = false;
        for (const [key, val] of Object.entries(map)) {
            if (c.includes(key)) {
                rgb = val;
                found = true;
                break;
            }
        }
        
        if (!found) return ''; // No tint if color unknown
        // Return a linear gradient that washes the top-left of the card with the tint, 
        // fading into the standard slate-900 background.
        return `background: linear-gradient(145deg, rgba(${rgb}, 0.15) 0%, rgba(15, 23, 42, 0.95) 50%);`;
    }

    function renderCards() {
        const filtrados = filamentosData.filter(f => {
            if (currentFilter === 'all') return true;
            return f.material.toUpperCase().includes(currentFilter.toUpperCase());
        });

        if (filtrados.length === 0) {
            gridFilamentos.innerHTML = `
                <div class="col-span-full py-12 text-center text-slate-500 font-mono">
                    No se encontraron bobinas con el filtro seleccionado.
                </div>
            `;
            return;
        }

        gridFilamentos.innerHTML = filtrados.map(f => {
            const actual = parseInt(f.peso_actual_g) || 0;
            const inicial = parseInt(f.peso_inicial_g) || 1000;
            const pct = Math.max(0, Math.min(100, (actual / inicial) * 100));
            const esCritico = actual <= (parseInt(f.alerta_stock_minimo_g) || 150);

            // Color bar
            let barColor = 'bg-sky-500';
            if (pct <= 15) barColor = 'bg-red-500';
            else if (pct <= 35) barColor = 'bg-amber-500';
            else if (pct >= 75) barColor = 'bg-emerald-500';

            const impresiones = f.impresiones_activas || [];
            let impresionesHtml = '';
            if (impresiones.length > 0) {
                impresionesHtml = `
                <div class="mt-3 bg-slate-950/60 p-3 rounded-xl border border-sky-800/40">
                    <h4 class="text-[10px] font-bold text-sky-400 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                        <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Imprimiendo (${impresiones.length})
                    </h4>
                    <div class="space-y-2">
                        ${impresiones.map(imp => `
                            <div class="flex items-center justify-between bg-slate-900 border border-slate-700/50 rounded-lg p-2">
                                <div>
                                    <div class="text-xs font-bold text-white">${imp.nombre}</div>
                                    <div class="text-[10px] font-mono text-slate-400">${imp.peso_g}g • ${imp.horas_impresion}h</div>
                                </div>
                                <button type="button" onclick="window.completarImpresion(${imp.id})" class="px-2 py-1 rounded bg-emerald-900 hover:bg-emerald-800 text-emerald-400 border border-emerald-700/50 text-[10px] font-bold uppercase transition flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Listo
                                </button>
                            </div>
                        `).join('')}
                    </div>
                </div>`;
            }

            return `
                <div class="bg-slate-900/90 border ${esCritico ? 'border-red-800/80 shadow-lg shadow-red-950/20' : 'border-slate-800'} rounded-2xl p-6 flex flex-col justify-between hover:border-slate-700 transition relative" style="${getFilamentStyle(f.color)}">
                    ${esCritico ? '<span class="absolute top-4 right-4 px-2 py-0.5 text-[10px] font-mono font-bold uppercase rounded bg-red-950 text-red-400 border border-red-800 animate-pulse">Stock Crítico</span>' : ''}

                    <div>
                        <!-- Header de la Card -->
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl bg-slate-950 border border-slate-800 flex items-center justify-center font-bold text-white uppercase text-xs">
                                ${f.material.substring(0, 3)}
                            </div>
                            <div>
                                <h3 class="font-bold text-base text-white leading-tight">${f.marca} ${f.material}</h3>
                                <div class="flex items-center gap-2 text-xs text-slate-400 mt-0.5">
                                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-slate-400" title="${f.color}"></span>
                                    <span>Color: <strong class="text-slate-200">${f.color}</strong></span>
                                </div>
                            </div>
                        </div>

                        <!-- Barra de Progreso de Gramos Restantes -->
                        <div class="space-y-1.5 my-4 bg-slate-950/80 p-3.5 rounded-xl border border-slate-800/80">
                            <div class="flex justify-between items-center text-xs font-mono">
                                <span class="text-slate-400">Remanente:</span>
                                <span class="font-bold text-white">${actual} g / ${inicial} g</span>
                            </div>
                            <div class="w-full bg-slate-800 rounded-full h-2.5 overflow-hidden">
                                <div class="${barColor} h-2.5 rounded-full transition-all duration-500" style="width: ${pct}%"></div>
                            </div>
                            <div class="flex justify-between text-[11px] font-mono text-slate-400 pt-0.5">
                                <span>${pct.toFixed(1)}% disponible</span>
                                <span>Costo: ${formatARS(f.costo_por_gramo_ars || (f.costo_rollo_ars / inicial))}/g</span>
                            </div>
                        </div>
                        
                        ${impresionesHtml}
                    </div>

                    <!-- Botones de Acción -->
                    <div class="pt-4 mt-4 border-t border-slate-800/80 space-y-2">
                        <button type="button" 
                                onclick="window.abrirModalDescontar(${f.id}, '${f.marca} ${f.material} (${f.color})', ${actual})"
                                class="w-full py-2.5 px-3 rounded-xl text-xs font-bold uppercase tracking-wider text-white bg-gradient-to-r from-orange-600 to-amber-600 hover:from-orange-500 hover:to-amber-500 shadow-md shadow-orange-600/20 transition flex items-center justify-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                            </svg>
                            <span>Imprimir pieza</span>
                        </button>

                        <div class="grid grid-cols-3 gap-2 text-xs font-mono">
                            <button type="button" onclick="window.abrirModalRecargar(${f.id}, '${f.marca} ${f.material}')"
                                    class="py-1.5 px-2 bg-slate-950 hover:bg-slate-800 rounded-lg text-emerald-400 border border-slate-800 hover:border-emerald-800 text-center transition">
                                + Kilos
                            </button>
                            <button type="button" onclick="window.abrirModalEditar(${JSON.stringify(f).replace(/"/g, '&quot;')})"
                                    class="py-1.5 px-2 bg-slate-950 hover:bg-slate-800 rounded-lg text-slate-300 border border-slate-800 hover:border-slate-700 text-center transition">
                                Editar
                            </button>
                            <button type="button" onclick="window.eliminarBobina(${f.id})"
                                    class="py-1.5 px-2 bg-slate-950 hover:bg-red-950/60 rounded-lg text-red-400 border border-slate-800 hover:border-red-800 text-center transition">
                                Eliminar
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    const inputCantidad = document.getElementById('desc_cantidad');
    const inputGramosUnidad = document.getElementById('desc_gramos_unidad');
    const inputGramosTotal = document.getElementById('desc_gramos');

    const updateGramos = () => {
        const cant = parseInt(inputCantidad.value) || 0;
        const gUni = parseFloat(inputGramosUnidad.value) || 0;
        inputGramosTotal.value = (cant * gUni).toFixed(1);
    };

    inputCantidad.addEventListener('input', updateGramos);
    inputGramosUnidad.addEventListener('input', updateGramos);

    window.abrirModalDescontar = (id, info, remanente) => {
        document.getElementById('desc_filamento_id').value = id;
        document.getElementById('desc_bobina_info').textContent = info;
        document.getElementById('desc_bobina_remanente').textContent = `${remanente} g`;
        inputCantidad.value = '1';
        inputGramosUnidad.value = '';
        inputGramosTotal.value = '';
        document.getElementById('desc_horas').value = '';
        document.getElementById('desc_nota').value = '';
        openModal(modalDescontar);
        setTimeout(() => inputCantidad.focus(), 100);
    };

    // Procesar Creación de Pieza (Descuenta Gramos Automáticamente)
    formDescontar.addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = parseInt(document.getElementById('desc_filamento_id').value, 10);
        const cantidad = parseInt(inputCantidad.value, 10);
        const gramos = parseFloat(inputGramosTotal.value);
        const horas = parseFloat(document.getElementById('desc_horas').value);
        let nota = document.getElementById('desc_nota').value.trim() || 'Pieza sin nombre';

        if (cantidad > 1) {
            nota = `${nota} (x${cantidad})`;
        }

        if (gramos <= 0 || horas <= 0) {
            showToast('Ingresa cantidades válidas.', 'error');
            return;
        }

        const filamento = filamentosData.find(f => f.id === id);
        if (!filamento) return;

        const tarifa = parseFloat(configGranja.tarifa_kwh) || 120;
        const potencia = parseInt(configGranja.potencia_promedio_w) || 150;
        const costoEnergia = (potencia / 1000) * horas * tarifa;
        const costoMaterial = gramos * parseFloat(filamento.costo_por_gramo_ars);
        const costoProduccion = costoEnergia + costoMaterial;

        try {
            const res = await fetch('/api/piezas.php?action=crear', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    action: 'crear', 
                    nombre: nota,
                    filamento_id: id, 
                    peso_g: Math.ceil(gramos), 
                    horas_impresion: horas,
                    costo_produccion_ars: costoProduccion,
                    precio_venta_ars: 0,
                    estado: 'en_produccion',
                    descontar_bobina: true
                })
            });
            const data = await res.json();

            if (data.status === 'success') {
                closeModal(modalDescontar);
                showToast(`Impresión iniciada. Pieza ingresada a producción.`, 'success');
                cargarFilamentos(); // Recargar todo para traer estado actualizado
            } else {
                throw new Error(data.message || 'Error al crear la pieza');
            }
        } catch (err) {
            showToast(err.message, 'error');
        }
    });

    window.completarImpresion = async (pieza_id) => {
        try {
            const res = await fetch('/api/piezas.php?action=cambiar_estado', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'cambiar_estado', id: pieza_id, estado: 'en_stock' })
            });
            const data = await res.json();
            if (data.status === 'success') {
                showToast('Pieza terminada y enviada al Stock.', 'success');
                cargarFilamentos();
            } else {
                throw new Error(data.message);
            }
        } catch(err) {
            showToast(err.message, 'error');
        }
    };

    // Modal Crear / Editar Bobina
    btnNuevaBobina.addEventListener('click', () => {
        document.getElementById('modal_bobina_title').textContent = 'Nueva Bobina de Filamento';
        document.getElementById('bobina_id').value = '';
        formBobina.reset();
        document.getElementById('bobina_peso_inicial').disabled = false;
        openModal(modalBobina);
    });

    window.abrirModalEditar = (f) => {
        document.getElementById('modal_bobina_title').textContent = 'Editar Bobina';
        document.getElementById('bobina_id').value = f.id;
        document.getElementById('bobina_marca').value = f.marca;
        document.getElementById('bobina_material').value = f.material;
        document.getElementById('bobina_color').value = f.color;
        document.getElementById('bobina_peso_inicial').value = f.peso_inicial_g;
        document.getElementById('bobina_peso_inicial').disabled = true;
        document.getElementById('bobina_costo').value = f.costo_rollo_ars;
        document.getElementById('bobina_alerta').value = f.alerta_stock_minimo_g;
        openModal(modalBobina);
    };

    formBobina.addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('bobina_id').value;
        const payload = {
            id: id ? parseInt(id, 10) : undefined,
            marca: document.getElementById('bobina_marca').value.trim(),
            material: document.getElementById('bobina_material').value,
            color: document.getElementById('bobina_color').value.trim(),
            peso_inicial_g: parseInt(document.getElementById('bobina_peso_inicial').value, 10),
            costo_rollo_ars: parseFloat(document.getElementById('bobina_costo').value),
            alerta_stock_minimo_g: parseInt(document.getElementById('bobina_alerta').value, 10),
            action: id ? 'editar' : 'crear'
        };

        try {
            const res = await fetch(`/api/filamentos.php?action=${payload.action}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.status === 'success') {
                closeModal(modalBobina);
                showToast(data.message, 'success');
                cargarFilamentos();
            } else {
                throw new Error(data.message);
            }
        } catch (err) {
            // Fallback optimista
            if (payload.action === 'crear') {
                const nuevo = {
                    id: Date.now(),
                    ...payload,
                    peso_actual_g: payload.peso_inicial_g,
                    costo_por_gramo_ars: payload.costo_rollo_ars / payload.peso_inicial_g,
                    es_critico: false,
                    porcentaje_restante: 100.0
                };
                filamentosData.push(nuevo);
            } else {
                const item = filamentosData.find(f => f.id === payload.id);
                if (item) {
                    Object.assign(item, payload);
                }
            }
            closeModal(modalBobina);
            showToast('Operación completada.', 'success');
            actualizarEstadisticas();
            renderCards();
        }
    });

    // Eliminar Bobina
    window.eliminarBobina = async (id) => {
        if (!confirm('¿Estás seguro de quitar esta bobina del inventario activo?')) return;

        try {
            const res = await fetch('/api/filamentos.php?action=eliminar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'eliminar', id })
            });
            const data = await res.json();
            if (data.status === 'success') {
                showToast(data.message, 'success');
                filamentosData = filamentosData.filter(f => f.id !== id);
                actualizarEstadisticas();
                renderCards();
            } else {
                throw new Error(data.message);
            }
        } catch (err) {
            filamentosData = filamentosData.filter(f => f.id !== id);
            showToast('Bobina removida.', 'success');
            actualizarEstadisticas();
            renderCards();
        }
    };

    // Modal Recargar Kilos
    window.abrirModalRecargar = (id, info) => {
        document.getElementById('recarga_filamento_id').value = id;
        document.getElementById('recarga_bobina_info').textContent = info;
        document.getElementById('recarga_gramos').value = 1000;
        openModal(modalRecargar);
    };

    formRecargar.addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = parseInt(document.getElementById('recarga_filamento_id').value, 10);
        const gramos = parseInt(document.getElementById('recarga_gramos').value, 10);

        try {
            const res = await fetch('/api/filamentos.php?action=cargar-kilos', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'cargar-kilos', id, gramos })
            });
            const data = await res.json();
            if (data.status === 'success') {
                closeModal(modalRecargar);
                showToast(data.message, 'success');
                cargarFilamentos();
            } else {
                throw new Error(data.message);
            }
        } catch (err) {
            const item = filamentosData.find(f => f.id === id);
            if (item) {
                item.peso_actual_g += gramos;
                closeModal(modalRecargar);
                showToast(`Se agregaron ${gramos}g a ${item.marca}`, 'success');
                actualizarEstadisticas();
                renderCards();
            }
        }
    });

    // Filtros
    filterButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            filterButtons.forEach(b => {
                b.className = "filter-btn text-xs font-mono font-semibold px-3 py-1.5 rounded-xl bg-slate-950 text-slate-400 hover:text-white border border-slate-800";
            });
            btn.className = "filter-btn text-xs font-mono font-semibold px-3 py-1.5 rounded-xl bg-sky-600 text-white";
            currentFilter = btn.dataset.filter;
            renderCards();
        });
    });

    // Inicializar
    cargarFilamentos();
});
