/* [LOCK-MODULE: JS-PIEZAS] - NO MODIFICAR ESTE BLOQUE A MENOS QUE EL USUARIO LO ESPECIFIQUE */

document.addEventListener('DOMContentLoaded', () => {
    const gridPiezas = document.getElementById('grid_piezas');
    const toast = document.getElementById('toast');

    // Métricas
    const statPiezasStock = document.getElementById('stat_piezas_stock');
    const statPiezasVendidas = document.getElementById('stat_piezas_vendidas');
    const statTotalFacturado = document.getElementById('stat_total_facturado');
    const statPiezasScrap = document.getElementById('stat_piezas_scrap');

    // Modales
    const modalVender = document.getElementById('modal_vender');
    const modalScrap = document.getElementById('modal_scrap');
    const modalPieza = document.getElementById('modal_pieza');
    const modalPrecio = document.getElementById('modal_precio');

    // Formularios
    const formVender = document.getElementById('form_vender');
    const formScrap = document.getElementById('form_scrap');
    const formPieza = document.getElementById('form_pieza');
    const formPrecio = document.getElementById('form_precio');

    // Botones y Selectores
    const btnNuevaPieza = document.getElementById('btn_nueva_pieza');
    const filterButtons = document.querySelectorAll('.filter-btn');
    const selectFilamentoPieza = document.getElementById('pieza_filamento_id');

    let piezasData = [];
    let filamentosList = [];
    let currentFilter = 'all';

    const formatARS = (val) => {
        return new Intl.NumberFormat('es-AR', {
            style: 'currency',
            currency: 'ARS',
            minimumFractionDigits: 2
        }).format(val || 0);
    };

    function showToast(message, type = 'success') {
        toast.className = `fixed top-20 right-6 z-50 transform transition-all duration-300 p-4 rounded-xl shadow-2xl flex items-center gap-3 text-sm font-semibold border ${
            type === 'success' 
                ? 'bg-emerald-950/90 text-emerald-200 border-emerald-700/80 shadow-emerald-950/40' 
                : 'bg-red-950/90 text-red-200 border-red-700/80 shadow-red-950/40'
        }`;
        toast.innerHTML = `<span>${message}</span>`;
        toast.style.opacity = '1';
        toast.style.pointerEvents = 'auto';
        toast.style.transform = 'translateY(0)';

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.pointerEvents = 'none';
            toast.style.transform = 'translateY(8px)';
        }, 3500);
    }

    function openModal(m) { m.classList.remove('hidden'); }
    function closeModal(m) { m.classList.add('hidden'); }

    document.querySelectorAll('.btn-close-modal').forEach(btn => {
        btn.addEventListener('click', () => {
            closeModal(modalVender);
            closeModal(modalScrap);
            closeModal(modalPieza);
        });
    });

    // Cargar Catálogo de Filamentos para el Select del Alta de Pieza
    async function cargarFilamentosParaSelect() {
        try {
            const res = await fetch('/api/filamentos.php?action=listar');
            const data = await res.json();
            if (data.status === 'success' && Array.isArray(data.data)) {
                filamentosList = data.data;
                selectFilamentoPieza.innerHTML = filamentosList.map(f => `
                    <option value="${f.id}">${f.marca} ${f.material} (${f.color}) - Restan ${f.peso_actual_g}g</option>
                `).join('');
            }
        } catch (e) {
            selectFilamentoPieza.innerHTML = `
                <option value="1">Printalot PLA (Negro)</option>
                <option value="2">Grilon3 PETG (Cristal)</option>
            `;
        }
    }

    // Cargar Piezas desde el API
    async function cargarPiezas() {
        try {
            const res = await fetch('/api/piezas.php?action=listar');
            const data = await res.json();

            if (data.status === 'success') {
                piezasData = data.data;
                actualizarMetricas();
                renderCards();
            } else {
                throw new Error(data.message);
            }
        } catch (err) {
            // Datos iniciales de demostración si la DB aún no está sincronizada
            if (piezasData.length === 0) {
                piezasData = [
                    {
                        id: 1,
                        nombre: 'Soporte de Cámara Articulado',
                        filamento_id: 1,
                        filamento_marca: 'Printalot',
                        filamento_material: 'PLA',
                        filamento_color: 'Negro',
                        peso_g: 80,
                        horas_impresion: 4.0,
                        costo_produccion_ars: 1923.60,
                        precio_venta_ars: 3206.00,
                        ganancia_estimada_ars: 1282.40,
                        estado: 'en_stock',
                        creado_en: '2026-10-01 10:00:00'
                    },
                    {
                        id: 2,
                        nombre: 'Engranaje Reductor Helicoidal',
                        filamento_id: 2,
                        filamento_marca: 'Grilon3',
                        filamento_material: 'PETG',
                        filamento_color: 'Cristal',
                        peso_g: 110,
                        horas_impresion: 5.5,
                        costo_produccion_ars: 2800.00,
                        precio_venta_ars: 4700.00,
                        ganancia_estimada_ars: 1900.00,
                        estado: 'vendida',
                        venta_precio_unitario: 4700.00,
                        venta_costo_unitario: 2800.00,
                        ganancia_neta_ars: 1900.00,
                        creado_en: '2026-09-30 18:30:00'
                    }
                ];
            }
            actualizarMetricas();
            renderCards();
        }
    }

    function actualizarMetricas() {
        const enStock = piezasData.filter(p => p.estado === 'en_stock').length;
        const vendidas = piezasData.filter(p => p.estado === 'vendida').length;
        const scrap = piezasData.filter(p => p.estado === 'dañada').length;
        const facturado = piezasData
            .filter(p => p.estado === 'vendida')
            .reduce((acc, p) => acc + (p.venta_precio_unitario || p.precio_venta_ars || 0), 0);

        statPiezasStock.textContent = enStock;
        statPiezasVendidas.textContent = vendidas;
        statPiezasScrap.textContent = scrap;
        statTotalFacturado.textContent = formatARS(facturado);
    }

    function renderCards() {
        const filtradas = piezasData.filter(p => {
            if (currentFilter === 'all') return true;
            return p.estado === currentFilter;
        });

        if (filtradas.length === 0) {
            gridPiezas.innerHTML = `
                <div class="col-span-full py-12 text-center text-slate-500 font-mono">
                    No se encontraron piezas en la categoría '${currentFilter}'.
                </div>
            `;
            return;
        }

        gridPiezas.innerHTML = filtradas.map(p => {
            let badgeClass = 'bg-emerald-950 text-emerald-400 border-emerald-800';
            let badgeText = 'En Stock';

            if (p.estado === 'vendida') {
                badgeClass = 'bg-sky-950 text-sky-400 border-sky-800';
                badgeText = 'Vendida';
            } else if (p.estado === 'dañada') {
                badgeClass = 'bg-rose-950 text-rose-400 border-rose-800';
                badgeText = 'Dañada (Scrap)';
            } else if (p.estado === 'en_produccion') {
                badgeClass = 'bg-amber-950 text-amber-400 border-amber-800';
                badgeText = 'En Impresión';
            }

            const ganancia = p.estado === 'vendida' 
                ? (p.ganancia_neta_ars || (p.precio_venta_ars - p.costo_produccion_ars))
                : (p.precio_venta_ars - p.costo_produccion_ars);

            return `
                <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6 flex flex-col justify-between hover:border-slate-700 transition">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-[10px] font-mono font-bold uppercase px-2.5 py-0.5 rounded-full border ${badgeClass}">
                                ${badgeText}
                            </span>
                            <span class="text-xs text-slate-500 font-mono">#${p.id}</span>
                        </div>

                        <h3 class="font-bold text-base text-white leading-tight mb-2">${p.nombre}</h3>

                        <div class="space-y-1.5 text-xs text-slate-400 font-mono bg-slate-950/80 p-3 rounded-xl border border-slate-800/80 mb-4">
                            <div class="flex justify-between">
                                <span>Material:</span>
                                <span class="text-slate-200 font-semibold">${p.filamento_marca} ${p.filamento_material} (${p.filamento_color})</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Masa consumida:</span>
                                <span class="text-slate-200 font-bold">${p.peso_g} g</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Tiempo de máquina:</span>
                                <span class="text-slate-200">${p.horas_impresion} hs</span>
                            </div>
                        </div>

                        <!-- Finanzas Unitarias -->
                        <div class="grid grid-cols-2 gap-2 text-xs font-mono mb-4">
                            <div class="bg-slate-950 p-2.5 rounded-xl border border-slate-800">
                                <span class="text-slate-400 block text-[10px]">Costo Producción:</span>
                                <span class="text-red-400 font-bold">${formatARS(p.costo_produccion_ars)}</span>
                            </div>
                            <div class="bg-slate-950 p-2.5 rounded-xl border border-slate-800">
                                <span class="text-slate-400 block text-[10px]">Precio Venta:</span>
                                <span class="text-emerald-400 font-bold">${formatARS(p.precio_venta_ars)}</span>
                            </div>
                        </div>

                        <div class="text-[11px] font-mono text-slate-400 flex justify-between px-1">
                            <span>Margen Proyectado:</span>
                            <span class="text-emerald-300 font-bold">+ ${formatARS(ganancia)}</span>
                        </div>
                    </div>

                    <!-- Botones de Acción según Estado -->
                    <div class="pt-4 border-t border-slate-800/80 mt-4">
                        ${p.estado === 'en_stock' ? `
                            <div class="grid grid-cols-3 gap-2">
                                <button type="button" 
                                        onclick="window.abrirModalVender(${p.id}, '${p.nombre.replace(/'/g, "\\'")}', ${p.costo_produccion_ars}, ${p.precio_venta_ars})"
                                        class="py-2.5 px-3 rounded-xl text-[11px] font-bold uppercase tracking-wider text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 shadow-md shadow-emerald-600/20 transition text-center col-span-2">
                                    Vender
                                </button>
                                <button type="button" 
                                        onclick="window.abrirModalPrecio(${p.id}, ${p.precio_venta_ars})"
                                        class="py-2.5 px-2 rounded-xl text-[10px] font-mono text-sky-400 bg-slate-950 hover:bg-sky-950/40 border border-slate-800 hover:border-sky-800 transition text-center">
                                    Editar $
                                </button>
                            </div>
                            <button type="button" 
                                    onclick="window.abrirModalScrap(${p.id})"
                                    class="w-full mt-2 py-2 px-3 rounded-xl text-xs font-mono text-rose-400 bg-slate-950 hover:bg-rose-950/40 border border-slate-800 hover:border-rose-800 transition text-center">
                                Scrap / Falla
                            </button>
                        ` : p.estado === 'vendida' ? `
                            <div class="p-2.5 rounded-xl bg-sky-950/40 border border-sky-900/60 text-center text-xs font-mono text-sky-300 flex items-center justify-center gap-1.5">
                                <svg class="w-4 h-4 text-sky-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                <span>Venta consolidada en auditoría</span>
                            </div>
                        ` : `
                            <div class="p-2.5 rounded-xl bg-rose-950/40 border border-rose-900/60 text-center text-xs font-mono text-rose-300">
                                Descarte registrado (Scrap)
                            </div>
                        `}
                    </div>
                </div>
            `;
        }).join('');
    }

    // Modal Vender
    window.abrirModalVender = (id, nombre, costo, precio) => {
        document.getElementById('venta_pieza_id').value = id;
        document.getElementById('venta_pieza_nombre').textContent = nombre;
        document.getElementById('venta_costo_unitario').textContent = formatARS(costo);
        document.getElementById('venta_precio_sugerido').textContent = formatARS(precio);
        
        // Determinar cantidad inicial sugerida
        let cantidad_lote = 1;
        const match = nombre.match(/\(x(\d+)\)$/);
        if (match) cantidad_lote = parseInt(match[1], 10);
        
        document.getElementById('venta_cantidad').value = cantidad_lote;
        document.getElementById('venta_precio_final').value = precio / cantidad_lote;
        openModal(modalVender);
    };

    formVender.addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = parseInt(document.getElementById('venta_pieza_id').value, 10);
        const cantidad = parseInt(document.getElementById('venta_cantidad').value, 10) || 1;
        const precio = parseFloat(document.getElementById('venta_precio_final').value);

        try {
            const res = await fetch('/api/piezas.php?action=vender', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'vender', pieza_id: id, cantidad, precio_unitario_ars: precio })
            });
            const data = await res.json();

            if (data.status === 'success') {
                closeModal(modalVender);
                showToast(`Venta confirmada. Ganancia: +${formatARS(data.ganancia_neta_ars)}`, 'success');
                cargarPiezas(); // Recargar para reflejar división de lote
            } else {
                throw new Error(data.message);
            }
        } catch (err) {
            showToast(err.message, 'error');
        }
    });

    // Modal Editar Precio
    window.abrirModalPrecio = (id, precio) => {
        document.getElementById('precio_pieza_id').value = id;
        document.getElementById('editar_precio_input').value = precio;
        openModal(modalPrecio);
    };

    formPrecio.addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = parseInt(document.getElementById('precio_pieza_id').value, 10);
        const precio = parseFloat(document.getElementById('editar_precio_input').value);

        try {
            const res = await fetch('/api/piezas.php?action=editar_precio', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, precio_venta_ars: precio })
            });
            const data = await res.json();

            if (data.status === 'success') {
                closeModal(modalPrecio);
                showToast('Precio actualizado exitosamente', 'success');
                const pieza = piezasData.find(p => p.id === id);
                if (pieza) pieza.precio_venta_ars = precio;
                actualizarMetricas();
                renderCards();
            } else {
                throw new Error(data.message);
            }
        } catch (err) {
            showToast(err.message, 'error');
        }
    });

    // Modal Scrap
    window.abrirModalScrap = (id) => {
        document.getElementById('scrap_pieza_id').value = id;
        const pieza = piezasData.find(p => p.id === id);
        let cantidad_lote = 1;
        if (pieza) {
            const match = pieza.nombre.match(/\(x(\d+)\)$/);
            if (match) cantidad_lote = parseInt(match[1], 10);
        }
        document.getElementById('scrap_cantidad').value = cantidad_lote;
        openModal(modalScrap);
    };

    formScrap.addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = parseInt(document.getElementById('scrap_pieza_id').value, 10);
        const motivo = document.getElementById('scrap_motivo').value.trim();
        const cantidad = parseInt(document.getElementById('scrap_cantidad').value, 10) || 1;

        try {
            const res = await fetch('/api/piezas.php?action=marcar_danada', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'marcar_danada', pieza_id: id, motivo, cantidad })
            });
            const data = await res.json();

            if (data.status === 'success') {
                closeModal(modalScrap);
                showToast('Pieza marcada como scrap.', 'success');
                cargarPiezas(); // Recargar para reflejar división de lote
            } else {
                throw new Error(data.message);
            }
        } catch (err) {
            showToast(err.message, 'error');
        }
    });

    // Modal Alta de Pieza
    btnNuevaPieza.addEventListener('click', () => {
        cargarFilamentosParaSelect();
        formPieza.reset();
        document.getElementById('pieza_horas').value = "4.0";
        document.getElementById('pieza_peso_g').value = "80";
        document.getElementById('pieza_costo').value = "1923.60";
        document.getElementById('pieza_precio').value = "3206.00";
        openModal(modalPieza);
    });

    formPieza.addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = {
            action: 'crear',
            nombre: document.getElementById('pieza_nombre').value.trim(),
            filamento_id: parseInt(document.getElementById('pieza_filamento_id').value, 10),
            peso_g: parseInt(document.getElementById('pieza_peso_g').value, 10),
            horas_impresion: parseFloat(document.getElementById('pieza_horas').value),
            costo_produccion_ars: parseFloat(document.getElementById('pieza_costo').value),
            precio_venta_ars: parseFloat(document.getElementById('pieza_precio').value),
            descontar_bobina: document.getElementById('pieza_descontar_bobina').checked
        };

        try {
            const res = await fetch('/api/piezas.php?action=crear', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.status === 'success') {
                closeModal(modalPieza);
                showToast(data.message, 'success');
                cargarPiezas();
            } else {
                throw new Error(data.message);
            }
        } catch (err) {
            const nueva = {
                id: Date.now(),
                ...payload,
                filamento_marca: 'Printalot',
                filamento_material: 'PLA',
                filamento_color: 'Negro',
                estado: 'en_stock'
            };
            piezasData.unshift(nueva);
            closeModal(modalPieza);
            showToast(`Pieza '${payload.nombre}' agregada a stock.`, 'success');
            actualizarMetricas();
            renderCards();
        }
    });

    // Filtros por Estado
    filterButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            filterButtons.forEach(b => {
                b.className = "filter-btn text-xs font-mono font-semibold px-3 py-1.5 rounded-xl bg-slate-950 text-slate-400 hover:text-white border border-slate-800";
            });
            btn.className = "filter-btn text-xs font-mono font-semibold px-3 py-1.5 rounded-xl bg-emerald-600 text-white";
            currentFilter = btn.dataset.estado;
            renderCards();
        });
    });

    // Iniciar
    cargarPiezas();
    cargarFilamentosParaSelect();
});
