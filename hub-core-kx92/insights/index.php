<?php
/* [LOCK-MODULE: VIEW-INSIGHTS] - NO MODIFICAR ESTE BLOQUE A MENOS QUE EL USUARIO LO ESPECIFIQUE */

declare(strict_types=1);

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/auth.php';

require_auth();
?>
<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <title>3D Farm Manager | Insights</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
    <link rel="stylesheet" href="/hub-core-kx92/assets/css/custom.css">
</head>
<body class="h-full antialiased p-6">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-black text-white tracking-tight">Insights & Rentabilidad</h2>
            <div class="text-xs font-mono text-slate-400 bg-slate-900/50 px-3 py-1.5 rounded-lg border border-slate-800">
                Inteligencia de Negocio
            </div>
        </div>

        <!-- Layout principal de tarjetas -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Tarjeta 1: Top Rentabilidad por Hora -->
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6 shadow-xl flex flex-col h-full" style="height: 600px;">
                <div class="flex items-center justify-between mb-4 border-b border-slate-800/80 pb-4 shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 bg-emerald-950/50 rounded-xl border border-emerald-900/50 text-emerald-400">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-white text-sm">Top Rentabilidad / Hora</h3>
                            <p class="text-[10px] font-mono text-slate-400">Mayor $ por hora de máquina</p>
                        </div>
                    </div>
                    <button type="button" onclick="window.generarPDF('hora')" class="flex items-center gap-1.5 px-3 py-1.5 bg-slate-800 hover:bg-emerald-900/40 border border-slate-700 hover:border-emerald-700/50 rounded-lg text-[10px] font-bold text-slate-300 hover:text-emerald-400 uppercase tracking-wider transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        PDF
                    </button>
                </div>
                <div id="insights_top_hora" class="space-y-3 overflow-y-auto pr-2 custom-scrollbar flex-1 mb-2">
                    <div class="text-center text-slate-500 font-mono text-xs py-8 animate-pulse">Cargando métricas...</div>
                </div>
                <div id="insights_perdidas_hora" class="space-y-3 shrink-0 pt-3 border-t border-slate-800/80 empty:hidden"></div>
            </div>

            <!-- Tarjeta 2: Top Rentabilidad por Gramo -->
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6 shadow-xl flex flex-col h-full" style="height: 600px;">
                <div class="flex items-center justify-between mb-4 border-b border-slate-800/80 pb-4 shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 bg-amber-950/50 rounded-xl border border-amber-900/50 text-amber-400">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-white text-sm">Top Rentabilidad / Gramo</h3>
                            <p class="text-[10px] font-mono text-slate-400">Mayor $ extraído por gramo</p>
                        </div>
                    </div>
                    <button type="button" onclick="window.generarPDF('gramo')" class="flex items-center gap-1.5 px-3 py-1.5 bg-slate-800 hover:bg-amber-900/40 border border-slate-700 hover:border-amber-700/50 rounded-lg text-[10px] font-bold text-slate-300 hover:text-amber-400 uppercase tracking-wider transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        PDF
                    </button>
                </div>
                <div id="insights_top_gramo" class="space-y-3 overflow-y-auto pr-2 custom-scrollbar flex-1 mb-2">
                    <div class="text-center text-slate-500 font-mono text-xs py-8 animate-pulse">Cargando métricas...</div>
                </div>
                <div id="insights_perdidas_gramo" class="space-y-3 shrink-0 pt-3 border-t border-slate-800/80 empty:hidden"></div>
            </div>

            <!-- Tarjeta 3: Más Vendidos (Volumen) -->
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6 shadow-xl flex flex-col h-full" style="height: 600px;">
                <div class="flex items-center justify-between mb-4 border-b border-slate-800/80 pb-4 shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 bg-sky-950/50 rounded-xl border border-sky-900/50 text-sky-400">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-white text-sm">Productos más vendidos</h3>
                            <p class="text-[10px] font-mono text-slate-400">Los "caballitos de batalla"</p>
                        </div>
                    </div>
                    <button type="button" onclick="window.generarPDF('volumen')" class="flex items-center gap-1.5 px-3 py-1.5 bg-slate-800 hover:bg-sky-900/40 border border-slate-700 hover:border-sky-700/50 rounded-lg text-[10px] font-bold text-slate-300 hover:text-sky-400 uppercase tracking-wider transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        PDF
                    </button>
                </div>
                <div id="insights_top_volumen" class="space-y-3 overflow-y-auto pr-2 custom-scrollbar flex-1 mb-2">
                    <div class="text-center text-slate-500 font-mono text-xs py-8 animate-pulse">Cargando métricas...</div>
                </div>
                <div id="insights_perdidas_volumen" class="space-y-3 shrink-0 pt-3 border-t border-slate-800/80 empty:hidden"></div>
            </div>

        </div>
    </div>
    <script src="/hub-core-kx92/insights/insights.js"></script>
</body>
</html>
