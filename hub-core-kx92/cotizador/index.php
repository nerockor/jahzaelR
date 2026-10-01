<?php
/* [LOCK-MODULE: VIEW-COTIZADOR] - NO MODIFICAR ESTE BLOQUE A MENOS QUE EL USUARIO LO ESPECIFIQUE */

declare(strict_types=1);

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/auth.php';

require_auth();
$user = get_authenticated_user();
$csrf_token = get_csrf_token();
?>
<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <title>3D Farm Manager | Cotizador &amp; Calculadora Energética</title>
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
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-amber-600 to-orange-500 text-white flex items-center justify-center font-bold shadow-md shadow-orange-500/20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <h1 class="font-bold text-base text-white tracking-tight leading-tight">Cotizador &amp; Calculadora Energética 3D</h1>
                    <span class="text-xs text-orange-400 font-mono">Pesos Argentinos ($ ARS) &bull; Módulo Aislado</span>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <span class="hidden md:inline-block px-2.5 py-1 text-xs font-mono font-medium rounded-lg bg-slate-800 text-slate-300 border border-slate-700">
                    Operador: <?= htmlspecialchars($user['usuario'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                </span>
                <a href="/hub-core-kx92/index.php?action=logout" class="text-xs text-red-400 hover:text-red-300 font-mono">Salir</a>
            </div>
        </div>
    </header>

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Formulario y Sliders -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Panel de Controles / Sliders (7 cols) -->
            <div class="lg:col-span-7 space-y-6">
                <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-6">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                        <h2 class="text-lg font-bold text-white flex items-center gap-2">
                            <span>Parámetros de Fabricación</span>
                        </h2>
                        <span class="text-xs font-mono px-2 py-0.5 rounded bg-orange-950 border border-orange-800 text-orange-400">En Vivo</span>
                    </div>

                    <!-- Slider 1: Horas de Impresión -->
                    <div class="space-y-2">
                        <div class="flex justify-between items-center text-sm">
                            <label for="input_horas" class="font-semibold text-slate-300">Tiempo de Impresión</label>
                            <div class="flex items-center gap-1 font-mono text-orange-400 font-bold bg-slate-950 px-2.5 py-1 rounded-lg border border-slate-800">
                                <input type="number" id="input_horas_num" min="0.1" max="120" step="0.1" value="4.0" class="w-16 bg-transparent text-right outline-none text-orange-400">
                                <span class="text-xs text-slate-400">hs</span>
                            </div>
                        </div>
                        <input type="range" id="slider_horas" min="0.5" max="48" step="any" value="4" 
                               class="w-full h-2 bg-slate-800 rounded-lg appearance-none cursor-pointer accent-orange-500">
                    </div>

                    <!-- Slider 2: Gramos de Filamento -->
                    <div class="space-y-2">
                        <div class="flex justify-between items-center text-sm">
                            <label for="input_gramos" class="font-semibold text-slate-300">Material Consumido</label>
                            <div class="flex items-center gap-1 font-mono text-orange-400 font-bold bg-slate-950 px-2.5 py-1 rounded-lg border border-slate-800">
                                <input type="number" id="input_gramos_num" min="1" max="5000" step="1" value="80" class="w-16 bg-transparent text-right outline-none text-orange-400">
                                <span class="text-xs text-slate-400">g</span>
                            </div>
                        </div>
                        <input type="range" id="slider_gramos" min="5" max="500" step="5" value="80" 
                               class="w-full h-2 bg-slate-800 rounded-lg appearance-none cursor-pointer accent-orange-500">
                    </div>

                    <!-- Selector de Filamento y Costo por Gramo -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Filamento / Bobina</label>
                            <select id="select_filamento" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2.5 text-sm text-slate-100 focus:outline-none focus:ring-2 focus:ring-orange-500">
                                <option value="22.00" selected>Printalot PLA ($22.00 /g)</option>
                                <option value="25.50">Grilon3 PETG ($25.50 /g)</option>
                                <option value="19.50">GST3D PLA+ ($19.50 /g)</option>
                                <option value="31.00">eSun ABS+ ($31.00 /g)</option>
                                <option value="custom">Personalizado...</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Costo por Gramo ($ ARS)</label>
                            <input type="number" id="input_costo_gramo" step="0.01" value="22.00" 
                                   class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2.5 text-sm text-slate-100 font-mono focus:outline-none focus:ring-2 focus:ring-orange-500">
                        </div>
                    </div>

                    <!-- Parámetros Energéticos y Granja -->
                    <div class="border-t border-slate-800 pt-4 space-y-4">
                        <div class="flex items-center justify-between cursor-pointer" id="toggle_advanced">
                            <span class="text-xs uppercase tracking-wider text-slate-400 font-bold flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                Parámetros Energéticos &amp; Costos Fijos
                            </span>
                            <span class="text-xs text-orange-400 font-mono">Editar</span>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                            <div class="bg-slate-950 p-3 rounded-xl border border-slate-800">
                                <span class="text-slate-400 block mb-1">Potencia (W)</span>
                                <input type="number" id="input_potencia" value="150" class="w-full bg-transparent font-mono font-bold text-white outline-none">
                            </div>
                            <div class="bg-slate-950 p-3 rounded-xl border border-slate-800">
                                <span class="text-slate-400 block mb-1">Tarifa kWh ($)</span>
                                <input type="number" id="input_tarifa" step="any" value="120.00" class="w-full bg-transparent font-mono font-bold text-white outline-none">
                            </div>
                            <div class="bg-slate-950 p-3 rounded-xl border border-slate-800">
                                <span class="text-slate-400 block mb-1">Tasa Falla (%)</span>
                                <input type="number" id="input_falla" step="any" value="5.0" class="w-full bg-transparent font-mono font-bold text-white outline-none">
                            </div>
                            <div class="bg-slate-950 p-3 rounded-xl border border-slate-800">
                                <span class="text-slate-400 block mb-1">Desgaste ($)</span>
                                <input type="number" id="input_desgaste" step="any" value="0.00" class="w-full bg-transparent font-mono font-bold text-white outline-none">
                            </div>
                        </div>
                    </div>

                    <!-- Slider 3: Margen de Ganancia -->
                    <div class="border-t border-slate-800 pt-4 space-y-2">
                        <div class="flex justify-between items-center text-sm">
                            <label class="font-semibold text-slate-300">Margen de Ganancia Deseado</label>
                            <span id="display_margen" class="font-mono text-emerald-400 font-bold bg-slate-950 px-2.5 py-1 rounded-lg border border-slate-800">40%</span>
                        </div>
                        <input type="range" id="slider_margen" min="10" max="80" step="1" value="40" 
                               class="w-full h-2 bg-slate-800 rounded-lg appearance-none cursor-pointer accent-emerald-500">
                    </div>

                    <!-- Selector de Lote Mayorista -->
                    <div class="border-t border-slate-800 pt-4">
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Escala de Producción (Unidad vs. Lote)</label>
                        <div class="grid grid-cols-5 gap-2" id="batch_buttons">
                            <button type="button" data-qty="1" data-disc="0" class="batch-btn py-2 px-3 rounded-xl text-xs font-bold font-mono border border-orange-500 bg-orange-600/20 text-orange-400">1 u (0%)</button>
                            <button type="button" data-qty="5" data-disc="5" class="batch-btn py-2 px-3 rounded-xl text-xs font-bold font-mono border border-slate-800 bg-slate-950 text-slate-400 hover:text-white">5 u (-5%)</button>
                            <button type="button" data-qty="10" data-disc="10" class="batch-btn py-2 px-3 rounded-xl text-xs font-bold font-mono border border-slate-800 bg-slate-950 text-slate-400 hover:text-white">10 u (-10%)</button>
                            <button type="button" data-qty="25" data-disc="15" class="batch-btn py-2 px-3 rounded-xl text-xs font-bold font-mono border border-slate-800 bg-slate-950 text-slate-400 hover:text-white">25 u (-15%)</button>
                            <button type="button" data-qty="50" data-disc="20" class="batch-btn py-2 px-3 rounded-xl text-xs font-bold font-mono border border-slate-800 bg-slate-950 text-slate-400 hover:text-white">50 u (-20%)</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel de Resultados y Desglose Financiero (5 cols) -->
            <div class="lg:col-span-5 space-y-6">
                <!-- Tarjeta Principal: Precio Sugerido -->
                <div class="bg-gradient-to-br from-slate-900 to-slate-900/90 border-2 border-orange-500/50 rounded-2xl p-6 shadow-2xl relative overflow-hidden">
                    <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-orange-500/10 rounded-full blur-2xl"></div>

                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-orange-400">Precio Sugerido de Venta</span>
                        <span id="badge_modo" class="text-[11px] font-mono font-semibold px-2 py-0.5 rounded bg-orange-950/80 text-orange-300 border border-orange-800">Unidad</span>
                    </div>

                    <div class="my-4">
                        <div class="text-4xl sm:text-5xl font-black text-white font-mono tracking-tight" id="display_precio_sugerido">
                            $ 0.00
                        </div>
                        <div class="text-xs text-slate-400 mt-1 flex items-center justify-between">
                            <span>Precio unitario final</span>
                            <span id="display_precio_lote_total" class="font-mono font-bold text-slate-300"></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-4 border-t border-slate-800">
                        <div class="bg-slate-950/80 p-3 rounded-xl border border-slate-800">
                            <span class="text-[10px] text-slate-400 uppercase font-semibold block">Costo Base Unitario</span>
                            <span class="text-lg font-bold font-mono text-red-400" id="display_costo_base">$ 0.00</span>
                        </div>
                        <div class="bg-slate-950/80 p-3 rounded-xl border border-slate-800">
                            <span class="text-[10px] text-slate-400 uppercase font-semibold block">Ganancia Neta</span>
                            <span class="text-lg font-bold font-mono text-emerald-400" id="display_ganancia">$ 0.00</span>
                        </div>
                    </div>
                </div>

                <!-- Desglose de Costos de Fabricación -->
                <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-4">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center justify-between border-b border-slate-800 pb-3">
                        <span>Desglose de Costos ($ ARS)</span>
                        <span class="text-xs font-mono text-slate-400">Fórmula Canónica</span>
                    </h3>

                    <div class="space-y-3 text-sm">
                        <!-- Energía -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                                <span class="text-slate-300">Costo Energía Eléctrica</span>
                            </div>
                            <span class="font-mono font-bold text-slate-100" id="detail_costo_energia">$ 0.00</span>
                        </div>
                        <div class="text-[11px] text-slate-500 font-mono pl-4.5" id="detail_formula_energia">
                            (150W / 1000) * 4.0hs * $120.00/kWh
                        </div>

                        <!-- Material -->
                        <div class="flex items-center justify-between pt-2 border-t border-slate-800/60">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-sky-400"></span>
                                <span class="text-slate-300">Costo de Filamento</span>
                            </div>
                            <span class="font-mono font-bold text-slate-100" id="detail_costo_material">$ 0.00</span>
                        </div>
                        <div class="text-[11px] text-slate-500 font-mono pl-4.5" id="detail_formula_material">
                            80g * $22.00/g
                        </div>

                        <!-- Desgaste / Scrap -->
                        <div class="flex items-center justify-between pt-2 border-t border-slate-800/60">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-rose-400"></span>
                                <span class="text-slate-300">Factor de Falla / Scrap</span>
                            </div>
                            <span class="font-mono font-bold text-slate-100" id="detail_factor_falla">+ 5.0%</span>
                        </div>

                        <!-- Resumen Total Lote -->
                        <div class="pt-4 border-t border-slate-800 text-xs text-slate-400 space-y-1">
                            <div class="flex justify-between">
                                <span>Tiempo acumulado máquina:</span>
                                <span class="font-mono text-slate-200" id="detail_horas_totales">4.0 hs</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Material total consumido:</span>
                                <span class="font-mono text-slate-200" id="detail_gramos_totales">80 g</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Ganancia neta total del lote:</span>
                                <span class="font-mono text-emerald-400 font-bold" id="detail_ganancia_total">$ 0.00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="/hub-core-kx92/cotizador/cotizador.js"></script>
</body>
</html>
