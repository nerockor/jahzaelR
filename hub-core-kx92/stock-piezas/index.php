<?php
/* [LOCK-MODULE: VIEW-PIEZAS] - NO MODIFICAR ESTE BLOQUE A MENOS QUE EL USUARIO LO ESPECIFIQUE */

declare(strict_types=1);

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/auth.php';

require_auth();
$user = get_authenticated_user();
?>
<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <title>3D Farm Manager | Stock de Piezas &amp; Ventas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/hub-core-kx92/assets/css/custom.css">
</head>
<body class="min-h-full flex flex-col bg-slate-950">
    <!-- Navbar Superior -->
    <header class="bg-slate-900 border-b border-slate-800 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="/hub-core-kx92/dashboard.php" class="p-2 text-slate-400 hover:text-white bg-slate-800/80 rounded-xl transition border border-slate-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white flex items-center justify-center font-bold shadow-md shadow-emerald-500/20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <div>
                    <h1 class="font-bold text-base text-white tracking-tight leading-tight">Stock de Piezas Impresas</h1>
                    <span class="text-xs text-emerald-400 font-mono">Circuito Producción &bull; Ventas &bull; Control de Scrap</span>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="button" id="btn_nueva_pieza" 
                        class="bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-semibold text-xs py-2 px-3.5 rounded-xl shadow-lg shadow-emerald-600/20 transition flex items-center gap-1.5 uppercase tracking-wide">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Nueva Pieza</span>
                </button>
                <a href="/hub-core-kx92/index.php?action=logout" class="text-xs text-red-400 hover:text-red-300 font-mono ml-2">Salir</a>
            </div>
        </div>
    </header>

    <!-- Toast de Notificaciones -->
    <div id="toast" class="fixed top-20 right-6 z-50 transform transition-all duration-300 opacity-0 pointer-events-none translate-y-2"></div>

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <!-- Tarjetas de Métricas -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4">
                <span class="text-xs font-mono text-slate-400 block uppercase">En Stock (Venta)</span>
                <span class="text-2xl font-black text-emerald-400 font-mono" id="stat_piezas_stock">0</span>
            </div>
            <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4">
                <span class="text-xs font-mono text-slate-400 block uppercase">Piezas Vendidas</span>
                <span class="text-2xl font-black text-sky-400 font-mono" id="stat_piezas_vendidas">0</span>
            </div>
            <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4">
                <span class="text-xs font-mono text-slate-400 block uppercase">Total Facturado</span>
                <span class="text-2xl font-black text-white font-mono" id="stat_total_facturado">$ 0.00</span>
            </div>
            <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4">
                <span class="text-xs font-mono text-slate-400 block uppercase">Scrap / Dañadas</span>
                <span class="text-2xl font-black text-rose-400 font-mono" id="stat_piezas_scrap">0</span>
            </div>
        </div>

        <!-- Filtros Rápidos por Estado -->
        <div class="flex flex-wrap items-center justify-between gap-4 bg-slate-900/50 p-3 rounded-2xl border border-slate-800/80">
            <div class="flex items-center gap-2" id="filter_estados">
                <button data-estado="all" class="filter-btn text-xs font-mono font-semibold px-3 py-1.5 rounded-xl bg-emerald-600 text-white">Todas</button>
                <button data-estado="en_stock" class="filter-btn text-xs font-mono font-semibold px-3 py-1.5 rounded-xl bg-slate-950 text-slate-400 hover:text-white border border-slate-800">En Stock</button>
                <button data-estado="vendida" class="filter-btn text-xs font-mono font-semibold px-3 py-1.5 rounded-xl bg-slate-950 text-slate-400 hover:text-white border border-slate-800">Vendidas</button>
                <button data-estado="dañada" class="filter-btn text-xs font-mono font-semibold px-3 py-1.5 rounded-xl bg-slate-950 text-slate-400 hover:text-white border border-slate-800">Dañadas (Scrap)</button>
            </div>
            <div class="text-xs text-slate-500 font-mono">
                Transaccionalidad contable Append-Only
            </div>
        </div>

        <!-- Grilla de Tarjetas de Piezas -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" id="grid_piezas">
            <div class="col-span-full py-12 text-center text-slate-500 font-mono">
                Cargando inventario de piezas impresas...
            </div>
        </div>
    </main>

    <!-- Modal: Registrar Venta de Pieza -->
    <div id="modal_vender" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm hidden flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-5">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-950 border border-emerald-700/60 text-emerald-400 flex items-center justify-center font-bold">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-white">Registrar Venta de Pieza</h3>
                </div>
                <button type="button" class="btn-close-modal text-slate-400 hover:text-white">&times;</button>
            </div>

            <div class="bg-slate-950 p-3.5 rounded-xl border border-slate-800 space-y-2 text-xs font-mono">
                <div class="text-slate-400">Pieza a liquidar:</div>
                <div class="text-sm font-bold text-white" id="venta_pieza_nombre">-</div>
                <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-800/80">
                    <div>
                        <span class="text-slate-400 block">Costo Producción:</span>
                        <span class="text-red-400 font-bold" id="venta_costo_unitario">$ 0.00</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Precio Lista:</span>
                        <span class="text-emerald-400 font-bold" id="venta_precio_sugerido">$ 0.00</span>
                    </div>
                </div>
            </div>

            <form id="form_vender" class="space-y-4">
                <input type="hidden" id="venta_pieza_id">

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Cantidad</label>
                        <input type="number" id="venta_cantidad" step="1" min="1" value="1" required
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-slate-100 font-mono font-bold text-base focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-300 uppercase mb-1">Precio Cobrado Total ($)</label>
                        <input type="number" id="venta_precio_final" step="any" required
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-slate-100 font-mono font-bold text-base focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>

                <!-- Canal y Comisiones -->
                <div class="space-y-3 p-3 bg-slate-900 border border-slate-800 rounded-xl">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Canal de Venta</label>
                        <select id="venta_canal" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-slate-200 text-sm focus:outline-none focus:border-emerald-500">
                            <option value="directa">Venta Directa / Efectivo (0%)</option>
                            <option value="ml_clasica">MercadoLibre Clásica (~15%)</option>
                            <option value="ml_premium">MercadoLibre Premium (~30%)</option>
                            <option value="tienda_online">Tienda Online + MP (~8%)</option>
                            <option value="personalizado">Personalizado</option>
                        </select>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-400 uppercase mb-1">Comisión / Impuestos (%)</label>
                            <input type="number" id="venta_comision_pct" step="any" min="0" value="0" 
                                   class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-1.5 text-slate-300 text-sm font-mono focus:outline-none focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-400 uppercase mb-1">Costo Fijo (Envío, etc) $</label>
                            <input type="number" id="venta_comision_fija" step="any" min="0" value="0" 
                                   class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-1.5 text-slate-300 text-sm font-mono focus:outline-none focus:border-emerald-500">
                        </div>
                    </div>
                </div>

                <div class="bg-slate-950 p-3 rounded-xl border border-slate-800/80 flex justify-between items-center">
                    <span class="text-xs text-slate-400 uppercase font-semibold">Ganancia Neta Real:</span>
                    <span class="text-emerald-400 font-bold font-mono text-lg" id="venta_ganancia_preview">$ 0.00</span>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" class="btn-close-modal px-4 py-2 rounded-xl text-xs font-mono text-slate-400 hover:text-white bg-slate-800">Cancelar</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 shadow-lg shadow-emerald-600/30 transition">
                        Confirmar Venta
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Editar Precio -->
    <div id="modal_precio" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm hidden flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-sm w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <span>Editar Precio Lote</span>
                </h3>
                <button type="button" class="btn-close-modal text-slate-400 hover:text-white">&times;</button>
            </div>
            <form id="form_precio" class="space-y-4">
                <input type="hidden" id="precio_pieza_id">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Precio de Venta Total ($)</label>
                    <input type="number" id="editar_precio_input" min="0" step="any" required
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-slate-100 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" class="btn-close-modal px-4 py-2 rounded-xl text-xs text-slate-400 bg-slate-800">Cancelar</button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold uppercase text-white bg-emerald-600 hover:bg-emerald-500">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Marcar Dañada / Scrap -->
    <div id="modal_scrap" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm hidden flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-base font-bold text-red-400 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>Declarar Pieza Dañada (Scrap)</span>
                </h3>
                <button type="button" class="btn-close-modal text-slate-400 hover:text-white">&times;</button>
            </div>

            <p class="text-xs text-slate-300">
                Esta acción removerá la pieza de los ítems aptos para la venta.
            </p>

            <form id="form_scrap" class="space-y-4">
                <input type="hidden" id="scrap_pieza_id">
                
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Cantidad</label>
                        <input type="number" id="scrap_cantidad" min="1" step="1" value="1" required
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Motivo de falla</label>
                        <input type="text" id="scrap_motivo" required value="Falla de extrusión"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" class="btn-close-modal px-4 py-2 rounded-xl text-xs text-slate-400 bg-slate-800">Cancelar</button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold uppercase text-white bg-red-700 hover:bg-red-600">Registrar Scrap</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Nueva Pieza Producida -->
    <div id="modal_pieza" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm hidden flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-base font-bold text-white">Alta de Pieza en Stock</h3>
                <button type="button" class="btn-close-modal text-slate-400 hover:text-white">&times;</button>
            </div>

            <form id="form_pieza" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Nombre o Modelo 3D</label>
                    <input type="text" id="pieza_nombre" required
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                           placeholder="ej: Soporte GoPro Brazo Pivotante">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Filamento Utilizado</label>
                        <select id="pieza_filamento_id" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <!-- Inyectado dinámicamente -->
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Peso Consumido (g)</label>
                        <input type="number" id="pieza_peso_g" required min="1" step="1" value="80"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-slate-100 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Horas Impresión</label>
                        <input type="number" id="pieza_horas" required min="0.1" step="0.1" value="4.0"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-slate-100 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Costo Prod. ($)</label>
                        <input type="number" id="pieza_costo" required min="1" step="any" value="1923.60"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-slate-100 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Precio Venta ($)</label>
                        <input type="number" id="pieza_precio" required min="1" step="any" value="3206.00"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-slate-100 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>

                <!-- Checkbox para descontar bobina automáticamente -->
                <div class="p-3 bg-slate-950 rounded-xl border border-slate-800 flex items-center gap-3">
                    <input type="checkbox" id="pieza_descontar_bobina" class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 bg-slate-900 border-slate-700" checked>
                    <label for="pieza_descontar_bobina" class="text-xs text-slate-300">
                        Descontar gramos automáticamente de la bobina seleccionada (Transacción ACID)
                    </label>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                    <button type="button" class="btn-close-modal px-4 py-2 rounded-xl text-xs font-mono text-slate-400 hover:text-white bg-slate-800">Cancelar</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 shadow-lg shadow-emerald-600/30 transition">
                        Guardar Pieza
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="/hub-core-kx92/stock-piezas/piezas.js"></script>
</body>
</html>
