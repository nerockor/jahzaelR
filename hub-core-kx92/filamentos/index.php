<?php
/* [LOCK-MODULE: VIEW-FILAMENTOS] - NO MODIFICAR ESTE BLOQUE A MENOS QUE EL USUARIO LO ESPECIFIQUE */

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
    <title>3D Farm Manager | Stock de Filamentos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/hub-core-kx92/assets/css/custom.css">
    <style>
        .color-dot {
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.15);
        }
    </style>
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
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-sky-600 to-cyan-500 text-white flex items-center justify-center font-bold shadow-md shadow-sky-500/20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h1 class="font-bold text-base text-white tracking-tight leading-tight">Inventario de Filamentos</h1>
                    <span class="text-xs text-sky-400 font-mono">Control de Bobinas &bull; Descuento Transaccional ACID</span>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="button" id="btn_nueva_bobina" 
                        class="bg-gradient-to-r from-sky-600 to-cyan-600 hover:from-sky-500 hover:to-cyan-500 text-white font-semibold text-xs py-2 px-3.5 rounded-xl shadow-lg shadow-sky-600/20 transition flex items-center gap-1.5 uppercase tracking-wide">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Nueva Bobina</span>
                </button>
                <a href="/hub-core-kx92/index.php?action=logout" class="text-xs text-red-400 hover:text-red-300 font-mono ml-2">Salir</a>
            </div>
        </div>
    </header>

    <!-- Notificación Flotante -->
    <div id="toast" class="fixed top-20 right-6 z-50 transform transition-all duration-300 opacity-0 pointer-events-none translate-y-2"></div>

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <!-- Resumen de Métricas de Filamento -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4">
                <span class="text-xs font-mono text-slate-400 block uppercase">Total Bobinas</span>
                <span class="text-2xl font-black text-white font-mono" id="stat_total_bobinas">0</span>
            </div>
            <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4">
                <span class="text-xs font-mono text-slate-400 block uppercase">Material Disponible</span>
                <span class="text-2xl font-black text-sky-400 font-mono" id="stat_gramos_totales">0 g</span>
            </div>
            <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4">
                <span class="text-xs font-mono text-slate-400 block uppercase">Valor Inventario</span>
                <span class="text-2xl font-black text-emerald-400 font-mono" id="stat_valor_inventario">$ 0.00</span>
            </div>
            <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4">
                <span class="text-xs font-mono text-slate-400 block uppercase">Alertas Stock Bajo</span>
                <span class="text-2xl font-black text-rose-400 font-mono" id="stat_alertas_bajo">0</span>
            </div>
        </div>

        <!-- Filtros Rápidos -->
        <div class="flex flex-wrap items-center justify-between gap-4 bg-slate-900/50 p-3 rounded-2xl border border-slate-800/80">
            <div class="flex items-center gap-2" id="filter_materiales">
                <button data-filter="all" class="filter-btn text-xs font-mono font-semibold px-3 py-1.5 rounded-xl bg-sky-600 text-white">Todos</button>
                <button data-filter="PLA" class="filter-btn text-xs font-mono font-semibold px-3 py-1.5 rounded-xl bg-slate-950 text-slate-400 hover:text-white border border-slate-800">PLA</button>
                <button data-filter="PETG" class="filter-btn text-xs font-mono font-semibold px-3 py-1.5 rounded-xl bg-slate-950 text-slate-400 hover:text-white border border-slate-800">PETG</button>
                <button data-filter="ABS" class="filter-btn text-xs font-mono font-semibold px-3 py-1.5 rounded-xl bg-slate-950 text-slate-400 hover:text-white border border-slate-800">ABS</button>
                <button data-filter="TPU" class="filter-btn text-xs font-mono font-semibold px-3 py-1.5 rounded-xl bg-slate-950 text-slate-400 hover:text-white border border-slate-800">TPU</button>
            </div>
            <div class="text-xs text-slate-500 font-mono">
                Actualización instantánea ACID
            </div>
        </div>

        <!-- Grid de Cards de Filamento -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" id="grid_filamentos">
            <div class="col-span-full py-12 text-center text-slate-500 font-mono">
                Cargando bobinas de filamento...
            </div>
        </div>
    </main>

    <!-- Modal: Imprimir Pieza con Bobina (Descuento Rápido) -->
    <div id="modal_descontar" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm hidden flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-5">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-orange-950 border border-orange-700/60 text-orange-400 flex items-center justify-center font-bold">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-white">Imprimir Pieza con esta Bobina</h3>
                </div>
                <button type="button" class="btn-close-modal text-slate-400 hover:text-white">&times;</button>
            </div>

            <div class="bg-slate-950 p-3 rounded-xl border border-slate-800 text-xs font-mono space-y-1">
                <div class="text-slate-400">Bobina seleccionada:</div>
                <div class="text-sm font-bold text-sky-400" id="desc_bobina_info">-</div>
                <div class="text-slate-400 flex justify-between pt-1">
                    <span>Remanente actual:</span>
                    <span class="text-white font-bold" id="desc_bobina_remanente">0 g</span>
                </div>
            </div>

            <form id="form_descontar" class="space-y-4">
                <input type="hidden" id="desc_filamento_id" name="id">

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1.5">Cantidad de Piezas</label>
                        <input type="number" id="desc_cantidad" min="1" step="1" required 
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-slate-100 font-mono font-bold focus:outline-none focus:ring-2 focus:ring-orange-500"
                               placeholder="ej: 100">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1.5">Gramos por pieza</label>
                        <div class="relative">
                            <input type="number" id="desc_gramos_unidad" min="0.1" step="0.1" required 
                                   class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-slate-100 font-mono font-bold focus:outline-none focus:ring-2 focus:ring-orange-500"
                                   placeholder="ej: 10">
                            <span class="absolute right-4 top-3.5 text-xs text-slate-500 font-mono">g</span>
                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1.5">Gramos Totales</label>
                        <div class="relative">
                            <input type="number" id="desc_gramos" readonly 
                                   class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-3 text-slate-400 font-mono font-bold focus:outline-none">
                            <span class="absolute right-4 top-3.5 text-xs text-slate-500 font-mono">g</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1.5">Horas Totales</label>
                        <div class="relative">
                            <input type="number" id="desc_horas" min="0.1" step="0.1" required 
                                   class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-slate-100 font-mono font-bold focus:outline-none focus:ring-2 focus:ring-orange-500"
                                   placeholder="ej: 2.5">
                            <span class="absolute right-4 top-3.5 text-xs text-slate-500 font-mono">h</span>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1.5">Nombre de la pieza / Nota</label>
                    <input type="text" id="desc_nota" 
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                           placeholder="ej: Soporte de motor NEMA 17">
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" class="btn-close-modal px-4 py-2 rounded-xl text-xs font-mono text-slate-400 hover:text-white bg-slate-800">Cancelar</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider text-white bg-gradient-to-r from-orange-600 to-amber-600 hover:from-orange-500 hover:to-amber-500 shadow-lg shadow-orange-600/30 transition">
                        Confirmar y Descontar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Crear / Editar Bobina -->
    <div id="modal_bobina" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm hidden flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-5">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-base font-bold text-white" id="modal_bobina_title">Nueva Bobina de Filamento</h3>
                <button type="button" class="btn-close-modal text-slate-400 hover:text-white">&times;</button>
            </div>

            <form id="form_bobina" class="space-y-4">
                <input type="hidden" id="bobina_id" name="id" value="">

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Marca</label>
                        <input type="text" id="bobina_marca" required 
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500"
                               placeholder="Printalot, Grilon3...">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Material</label>
                        <select id="bobina_material" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                            <option value="PLA">PLA</option>
                            <option value="PLA+">PLA+</option>
                            <option value="PETG">PETG</option>
                            <option value="ABS">ABS</option>
                            <option value="ABS+">ABS+</option>
                            <option value="TPU">TPU</option>
                            <option value="PPS-CF">PPS-CF (Fibra Carbono)</option>
                            <option value="OTRO">Otro Polímero</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Color</label>
                        <input type="text" id="bobina_color" required 
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500"
                               placeholder="Negro, Rojo, Plata...">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Peso Inicial (g)</label>
                        <input type="number" id="bobina_peso_inicial" required min="100" step="any" value="1000" 
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-slate-100 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-sky-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Costo Rollo ($ ARS)</label>
                        <input type="number" id="bobina_costo" required min="1" step="any" value="22000" 
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-slate-100 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-sky-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Alerta Mínima (g)</label>
                        <input type="number" id="bobina_alerta" required min="10" step="any" value="150" 
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-slate-100 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-sky-500">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                    <button type="button" class="btn-close-modal px-4 py-2 rounded-xl text-xs font-mono text-slate-400 hover:text-white bg-slate-800">Cancelar</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider text-white bg-gradient-to-r from-sky-600 to-cyan-600 hover:from-sky-500 hover:to-cyan-500 shadow-lg shadow-sky-600/30 transition">
                        Guardar Bobina
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Recargar / Cargar Kilos -->
    <div id="modal_recargar" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm hidden flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-sm w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-base font-bold text-white">Recargar Gramos / Kilos</h3>
                <button type="button" class="btn-close-modal text-slate-400 hover:text-white">&times;</button>
            </div>
            <form id="form_recargar" class="space-y-4">
                <input type="hidden" id="recarga_filamento_id">
                <div class="text-xs text-slate-400 font-mono" id="recarga_bobina_info"></div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Gramos a agregar</label>
                    <input type="number" id="recarga_gramos" min="50" step="any" value="1000" required
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-slate-100 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" class="btn-close-modal px-4 py-2 rounded-xl text-xs text-slate-400 bg-slate-800">Cancelar</button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold uppercase text-white bg-emerald-600 hover:bg-emerald-500">Recargar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="/hub-core-kx92/filamentos/filamentos.js"></script>
</body>
</html>
