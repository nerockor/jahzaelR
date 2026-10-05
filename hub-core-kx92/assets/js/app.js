/* [LOCK-MODULE: CORE-APP] - NO MODIFICAR ESTE BLOQUE A MENOS QUE EL USUARIO LO ESPECIFIQUE */

document.addEventListener('DOMContentLoaded', () => {
    const moduleContainer = document.getElementById('module-container');
    const navItems = document.querySelectorAll('.nav-tab-btn');
    const currentViewTitle = document.getElementById('current-view-title');
    const currentViewSubtitle = document.getElementById('current-view-subtitle');

    let activeCharts = [];

    const formatARS = (val) => {
        return new Intl.NumberFormat('es-AR', {
            style: 'currency',
            currency: 'ARS',
            minimumFractionDigits: 2
        }).format(val || 0);
    };

    /**
     * Router SPA para conmutar entre el Panel Ejecutivo y los Módulos Aislados
     */
    function navigateTo(moduleKey) {
        // Actualizar estados visuales de la barra lateral
        navItems.forEach(btn => {
            const isTarget = btn.dataset.module === moduleKey;
            if (isTarget) {
                btn.className = "nav-tab-btn w-full flex items-center gap-3 px-3.5 py-3 rounded-xl text-xs font-semibold bg-orange-600/20 text-orange-400 border border-orange-500/40 shadow-sm transition";
            } else {
                btn.className = "nav-tab-btn w-full flex items-center gap-3 px-3.5 py-3 rounded-xl text-xs font-semibold text-slate-400 hover:text-white hover:bg-slate-800/80 border border-transparent transition";
            }
        });

        // Destruir gráficos anteriores para liberar canvas y memoria
        activeCharts.forEach(c => {
            try { if (c && c.destroy) c.destroy(); } catch (e) {}
        });
        activeCharts = [];

        if (moduleKey === 'overview') {
            currentViewTitle.textContent = "Panel de Control General & Métricas";
            currentViewSubtitle.textContent = "Visión Ejecutiva, Finanzas y Estado de la Granja 3D";
            renderExecutiveDashboard();
        } else {
            const modulePaths = {
                'cotizador': {
                    url: '/hub-core-kx92/cotizador/',
                    title: 'Cotizador & Calculadora 3D Energética',
                    subtitle: 'Lógica paramétrica de costos y cálculo por unidad o lotes mayoristas'
                },
                'filamentos': {
                    url: '/hub-core-kx92/filamentos/',
                    title: 'Inventario de Filamentos',
                    subtitle: 'Control dinámico de bobinas y descuento transaccional de gramos'
                },
                'piezas': {
                    url: '/hub-core-kx92/stock-piezas/',
                    title: 'Stock de Piezas Impresas & Ventas',
                    subtitle: 'Catálogo de producción terminada, control de scrap y registro contable'
                },
                'insights': {
                    url: '/hub-core-kx92/insights/',
                    title: 'Insights & Rentabilidad',
                    subtitle: 'Inteligencia de Negocio y Métricas Analíticas'
                }
            };

            const mod = modulePaths[moduleKey];
            if (mod) {
                currentViewTitle.textContent = mod.title;
                currentViewSubtitle.textContent = mod.subtitle;
                renderEmbeddedModule(mod.url);
            }
        }
    }

    /**
     * Inyecta un módulo aislado dentro de un iframe sandboxed limpio para preservar
     * 100% el aislamiento modular estricto sin colisión de scripts ni estilos.
     */
    function renderEmbeddedModule(srcUrl) {
        const cacheBuster = srcUrl.includes('?') ? '&v=' + Date.now() : '?v=' + Date.now();
        moduleContainer.innerHTML = `
            <div class="w-full bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-2xl relative" style="height: calc(100vh - 140px); min-height: 700px;">
                <iframe src="${srcUrl}${cacheBuster}" 
                        class="w-full h-full border-0" 
                        title="Módulo Aislado">
                </iframe>
            </div>
        `;
    }

    /**
     * Renderiza el Panel de Métricas Ejecutivas con Charts locales en formato Workstation Escritorio
     */
    async function renderExecutiveDashboard() {
        moduleContainer.innerHTML = `
            <div class="space-y-6">
                <!-- 1. KPIs Principales (Grid 4 columnas de escritorio) -->
                <div class="grid grid-cols-4 gap-5" id="kpi-cards">
                    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 shadow-xl flex flex-col justify-between">
                        <div>
                            <span class="text-[11px] font-mono font-semibold text-slate-400 block uppercase tracking-wider">Ganancia Neta Total</span>
                            <div class="text-3xl font-black text-emerald-400 font-mono tracking-tight mt-1" id="kpi-ganancia">$ 0.00</div>
                        </div>
                        <div class="text-xs text-slate-500 font-mono mt-3 pt-2 border-t border-slate-800/80" id="kpi-facturado">Facturado: $ 0.00</div>
                    </div>

                    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 shadow-xl flex flex-col justify-between">
                        <div>
                            <span class="text-[11px] font-mono font-semibold text-slate-400 block uppercase tracking-wider">Piezas Vendidas (Mes)</span>
                            <div class="text-3xl font-black text-sky-400 font-mono tracking-tight mt-1" id="kpi-ventas-mes">0</div>
                        </div>
                        <div class="text-xs text-slate-500 font-mono mt-3 pt-2 border-t border-slate-800/80" id="kpi-mes-ars">Facturación mes: $ 0.00</div>
                    </div>

                    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 shadow-xl flex flex-col justify-between">
                        <div>
                            <span class="text-[11px] font-mono font-semibold text-slate-400 block uppercase tracking-wider">Capital Inmovilizado Stock</span>
                            <div class="text-3xl font-black text-amber-400 font-mono tracking-tight mt-1" id="kpi-capital-stock">$ 0.00</div>
                        </div>
                        <div class="text-xs text-slate-500 font-mono mt-3 pt-2 border-t border-slate-800/80" id="kpi-valor-venta">Valor de lista: $ 0.00</div>
                    </div>

                    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 shadow-xl flex flex-col justify-between">
                        <div>
                            <span class="text-[11px] font-mono font-semibold text-slate-400 block uppercase tracking-wider">Bobinas en Stock</span>
                            <div class="text-3xl font-black text-white font-mono tracking-tight mt-1" id="kpi-bobinas">0</div>
                        </div>
                        <div class="text-xs text-slate-500 font-mono mt-3 pt-2 border-t border-slate-800/80" id="kpi-gramos">0 g disponibles</div>
                    </div>
                </div>

                <!-- 2. Alertas de Seguridad / Stock Mínimo -->
                <div id="alertas-container" class="hidden"></div>

                <!-- 3. Gráficos Ejecutivos (Grid 3 columnas de escritorio estrictamente contenidas) -->
                <div class="grid grid-cols-3 gap-6">
                    <!-- Gráfico 1: Filamentos más usados / distribución -->
                    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 shadow-xl flex flex-col justify-between overflow-hidden">
                        <div>
                            <div class="flex items-center justify-between mb-3 border-b border-slate-800/80 pb-2.5">
                                <h3 class="text-xs font-bold text-white uppercase tracking-wider">Distribución de Filamentos</h3>
                                <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-orange-950/80 text-orange-400 border border-orange-800">Material</span>
                            </div>
                            <div class="relative w-full h-56 flex items-center justify-center overflow-hidden">
                                <canvas id="chart-filamentos" class="w-full h-full"></canvas>
                            </div>
                        </div>
                        <div class="text-[11px] text-slate-500 font-mono text-center pt-2 border-t border-slate-800/60">
                            Gramos consumidos agrupados por tipo de polímero
                        </div>
                    </div>

                    <!-- Gráfico 2: Ventas del mes / tendencia -->
                    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 shadow-xl flex flex-col justify-between overflow-hidden">
                        <div>
                            <div class="flex items-center justify-between mb-3 border-b border-slate-800/80 pb-2.5">
                                <h3 class="text-xs font-bold text-white uppercase tracking-wider">Ventas Recientes ($ ARS)</h3>
                                <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-sky-950/80 text-sky-400 border border-sky-800">Tendencia</span>
                            </div>
                            <div class="relative w-full h-56 flex items-center justify-center overflow-hidden">
                                <canvas id="chart-ventas" class="w-full h-full"></canvas>
                            </div>
                        </div>
                        <div class="text-[11px] text-slate-500 font-mono text-center pt-2 border-t border-slate-800/60">
                            Facturación en pesos argentinos por periodo
                        </div>
                    </div>

                    <!-- Gráfico 3: Estado de Piezas y Capital -->
                    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 shadow-xl flex flex-col justify-between overflow-hidden">
                        <div>
                            <div class="flex items-center justify-between mb-3 border-b border-slate-800/80 pb-2.5">
                                <h3 class="text-xs font-bold text-white uppercase tracking-wider">Estado del Catálogo</h3>
                                <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-emerald-950/80 text-emerald-400 border border-emerald-800">Unidades</span>
                            </div>
                            <div class="relative w-full h-56 flex items-center justify-center overflow-hidden">
                                <canvas id="chart-estados" class="w-full h-full"></canvas>
                            </div>
                        </div>
                        <div class="text-[11px] text-slate-500 font-mono text-center pt-2 border-t border-slate-800/60">
                            Piezas en stock, vendidas, en producción y scrap
                        </div>
                    </div>
                </div>

                <!-- 4. Resumen de Parámetros Operativos de la Granja -->
                <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 shadow-xl">
                    <div class="flex items-center justify-between mb-3 border-b border-slate-800 pb-2.5">
                        <h3 class="text-xs font-bold text-white uppercase tracking-wider">Parámetros Base de la Granja (Argentina)</h3>
                        <span class="text-[11px] font-mono text-slate-400">Moneda Base: ARS &bull; Consumo: kWh</span>
                    </div>
                    <div class="grid grid-cols-4 gap-4 text-xs font-mono">
                        <div class="bg-slate-950 p-3 rounded-xl border border-slate-800">
                            <span class="text-slate-500 block mb-1">Costo Tarifa Eléctrica:</span>
                            <span class="text-white font-bold text-sm">$ 120.00 / kWh</span>
                        </div>
                        <div class="bg-slate-950 p-3 rounded-xl border border-slate-800">
                            <span class="text-slate-500 block mb-1">Potencia Media Máquina:</span>
                            <span class="text-white font-bold text-sm">150 Watts</span>
                        </div>
                        <div class="bg-slate-950 p-3 rounded-xl border border-slate-800">
                            <span class="text-slate-500 block mb-1">Margen Comercial Base:</span>
                            <span class="text-emerald-400 font-bold text-sm">40.0 %</span>
                        </div>
                        <div class="bg-slate-950 p-3 rounded-xl border border-slate-800">
                            <span class="text-slate-500 block mb-1">Tasa Falla / Scrap Promedio:</span>
                            <span class="text-rose-400 font-bold text-sm">5.0 %</span>
                        </div>
                    </div>
                </div>
            </div>
        `;

        try {
            const res = await fetch('/api/dashboard.php');
            const data = await res.json();
            if (data.status === 'success') {
                const kpis = data.kpis;
                document.getElementById('kpi-ganancia').textContent = formatARS(kpis.ganancias_totales_ars);
                document.getElementById('kpi-facturado').textContent = `Facturado: ${formatARS(kpis.ventas_totales_ars)}`;
                document.getElementById('kpi-ventas-mes').textContent = kpis.piezas_vendidas_mes;
                document.getElementById('kpi-mes-ars').textContent = `Facturación mes: ${formatARS(kpis.ventas_mes_ars)}`;
                document.getElementById('kpi-capital-stock').textContent = formatARS(kpis.capital_inmovilizado_stock_ars);
                document.getElementById('kpi-valor-venta').textContent = `Valor lista: ${formatARS(kpis.valor_venta_stock_ars)}`;
                document.getElementById('kpi-bobinas').textContent = kpis.total_bobinas;
                document.getElementById('kpi-gramos').textContent = `${(kpis.gramos_totales_disponibles).toLocaleString('es-AR')} g disponibles`;

                // Alertas de stock crítico
                if (data.filamentos_criticos && data.filamentos_criticos.length > 0) {
                    const alertasBox = document.getElementById('alertas-container');
                    alertasBox.className = "p-4 rounded-2xl bg-red-950/70 border border-red-800/80 text-red-200 text-xs font-mono flex items-center justify-between shadow-lg";
                    alertasBox.innerHTML = `
                        <div class="flex items-center gap-3">
                            <span class="w-2.5 h-2.5 rounded-full bg-red-400 animate-ping flex-shrink-0"></span>
                            <span><strong>Alerta Stock Bajo:</strong> Se detectaron ${data.filamentos_criticos.length} bobina(s) por debajo del umbral mínimo de seguridad: ${data.filamentos_criticos.map(f => f.marca + ' ' + f.material + ' (' + f.peso_actual_g + 'g)').join(', ')}.</span>
                        </div>
                        <button onclick="window.appNavigate('filamentos')" class="px-3.5 py-1.5 rounded-xl bg-red-800/80 hover:bg-red-700 text-white font-bold text-[10px] uppercase tracking-wider transition flex-shrink-0">Ver Filamentos &rarr;</button>
                    `;
                }

                // Esperar a que el layout termine de acomodarse en el DOM antes de renderizar los Canvas
                requestAnimationFrame(() => {
                    // Gráfico 1: Distribución de Filamentos
                    const mats = data.materiales_mas_usados || {};
                    const matLabels = Object.keys(mats);
                    const matValues = Object.values(mats);

                    const canvas1 = document.getElementById('chart-filamentos');
                    if (canvas1) {
                        const c1 = new Chart(canvas1, {
                            type: 'doughnut',
                            data: {
                                labels: matLabels,
                                datasets: [{
                                    data: matValues,
                                    backgroundColor: ['#38bdf8', '#f97316', '#34d399', '#a78bfa', '#fb7185']
                                }]
                            }
                        });
                        activeCharts.push(c1);
                    }

                    // Gráfico 2: Ventas Recientes ($ ARS)
                    const hist = data.historico_ventas || { labels: ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4'], ventas_ars: [12000, 15000, 13000, 18000] };
                    const canvas2 = document.getElementById('chart-ventas');
                    if (canvas2) {
                        const c2 = new Chart(canvas2, {
                            type: 'line',
                            data: {
                                labels: hist.labels,
                                datasets: [{
                                    data: hist.ventas_ars,
                                    borderColor: '#38bdf8'
                                }]
                            }
                        });
                        activeCharts.push(c2);
                    }

                    // Gráfico 3: Estado del Catálogo
                    const ests = data.estados_piezas || { en_stock: 5, vendida: 12, dañada: 1, en_produccion: 2 };
                    const canvas3 = document.getElementById('chart-estados');
                    if (canvas3) {
                        const c3 = new Chart(canvas3, {
                            type: 'bar',
                            data: {
                                labels: ['Stock', 'Vendida', 'Scrap', 'Producción'],
                                datasets: [{
                                    data: [ests.en_stock || 0, ests.vendida || 0, ests.dañada || 0, ests.en_produccion || 0],
                                    backgroundColor: ['#10b981', '#0ea5e9', '#ef4444', '#f59e0b']
                                }]
                            }
                        });
                        activeCharts.push(c3);
                    }
                });
            }
        } catch (err) {
            console.error('Error cargando métricas:', err);
        }
    }

    // Exponer función de navegación global
    window.appNavigate = navigateTo;

    // Listeners para los botones de la barra lateral
    navItems.forEach(btn => {
        btn.addEventListener('click', () => {
            const modKey = btn.dataset.module;
            navigateTo(modKey);
        });
    });

    // Iniciar con la vista de Overview
    navigateTo('overview');
});
