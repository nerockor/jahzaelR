<?php
/* [LOCK-MODULE: HUB-DASHBOARD] - NO MODIFICAR ESTE BLOQUE A MENOS QUE EL USUARIO LO ESPECIFIQUE */

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

// Control de Parada: Solo con credenciales válidas y sesión activa se permite el acceso
require_auth();

$user = get_authenticated_user();
?>
<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <title>3D Farm Manager | Workstation de Control</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/hub-core-kx92/assets/css/custom.css">
    <style>
        /* Estilos optimizados para estación de trabajo de escritorio */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #020617;
        }
        ::-webkit-scrollbar-thumb {
            background: #1e293b;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #334155;
        }
    </style>
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 font-sans antialiased overflow-x-hidden">

    <!-- BARRA LATERAL FIJA PERMANENTE (DESKTOP ONLY - NUNCA SE COLAPSA) -->
    <aside class="fixed top-0 bottom-0 left-0 w-64 bg-slate-900 border-r border-slate-800 flex flex-col justify-between z-30 select-none shadow-2xl">
        <div>
            <!-- Header Sidebar -->
            <div class="h-16 flex items-center gap-3 px-5 border-b border-slate-800 bg-slate-900/50">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-amber-600 to-orange-500 text-white flex items-center justify-center font-bold shadow-lg shadow-orange-500/20 border border-orange-400/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <div>
                    <h1 class="font-black text-sm text-white tracking-tight leading-tight">3D FARM MANAGER</h1>
                    <span class="text-[10px] font-mono text-orange-400 font-bold uppercase tracking-wider">Estación de Control</span>
                </div>
            </div>

            <!-- Navegación Modular (Router SPA) -->
            <nav class="p-4 space-y-2" id="sidebar-nav">
                <button type="button" data-module="overview" class="nav-tab-btn w-full flex items-center gap-3 px-3.5 py-3 rounded-xl text-xs font-semibold bg-orange-600/20 text-orange-400 border border-orange-500/40 shadow-sm transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    <span>Métricas &amp; Panel General</span>
                </button>

                <button type="button" data-module="cotizador" class="nav-tab-btn w-full flex items-center gap-3 px-3.5 py-3 rounded-xl text-xs font-semibold text-slate-400 hover:text-white hover:bg-slate-800/80 border border-transparent transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    <span>Cotizador Energético 3D</span>
                </button>

                <button type="button" data-module="filamentos" class="nav-tab-btn w-full flex items-center gap-3 px-3.5 py-3 rounded-xl text-xs font-semibold text-slate-400 hover:text-white hover:bg-slate-800/80 border border-transparent transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Stock de Filamentos</span>
                </button>

                <button type="button" data-module="piezas" class="nav-tab-btn w-full flex items-center gap-3 px-3.5 py-3 rounded-xl text-xs font-semibold text-slate-400 hover:text-white hover:bg-slate-800/80 border border-transparent transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <span>Piezas &amp; Ventas</span>
                </button>

                <button type="button" data-module="insights" class="nav-tab-btn w-full flex items-center gap-3 px-3.5 py-3 rounded-xl text-xs font-semibold text-slate-400 hover:text-white hover:bg-slate-800/80 border border-transparent transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    <span>Insights &amp; Rentabilidad</span>
                </button>
            </nav>
        </div>

        <!-- Footer Sidebar (Usuario y Logout) -->
        <div class="p-4 border-t border-slate-800 bg-slate-900/60">
            <div class="flex items-center justify-between">
                <div class="truncate pr-2">
                    <div class="text-xs font-bold text-white truncate"><?= htmlspecialchars($user['nombre'] ?? 'Operador Granja', ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="text-[10px] text-slate-400 font-mono">@<?= htmlspecialchars($user['usuario'] ?? 'admin', ENT_QUOTES, 'UTF-8') ?></div>
                </div>
                <a href="/hub-core-kx92/index.php?action=logout" title="Cerrar sesión"
                   class="p-2 text-slate-400 hover:text-red-400 bg-slate-950 rounded-xl border border-slate-800 hover:border-red-800/80 transition flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </a>
            </div>
            <div class="mt-3 pt-2 border-t border-slate-800/60 flex items-center justify-between text-[10px] font-mono text-slate-500">
                <span>Versión Desktop 1.0</span>
                <span class="text-emerald-400 font-bold">&bull; ONLINE</span>
            </div>
        </div>
    </aside>

    <!-- ÁREA DE CONTENIDO PRINCIPAL (DESPLAZADA POR EL ANCHO FIJO DE LA SIDEBAR) -->
    <div class="ml-64 flex flex-col min-h-screen min-w-0 bg-slate-950">
        <!-- Barra Superior Sticky -->
        <header class="h-16 bg-slate-900/90 border-b border-slate-800 flex items-center justify-between px-8 sticky top-0 z-20 backdrop-blur-md">
            <div>
                <h2 class="text-base font-black text-white tracking-tight" id="current-view-title">Panel de Control General &amp; Métricas</h2>
                <p class="text-xs text-slate-400 font-mono" id="current-view-subtitle">Visión Ejecutiva, Finanzas y Estado de la Granja 3D</p>
            </div>

            <div class="flex items-center gap-4">
                <span class="hidden md:inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-mono bg-emerald-950/80 border border-emerald-800 text-emerald-400">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>MOTOR ACID / OPERATIVO</span>
                </span>
            </div>
        </header>

        <!-- CONTENEDOR DINÁMICO DE MÓDULOS -->
        <main class="flex-1 p-8 overflow-x-hidden">
            <div id="module-container" class="max-w-[1600px] mx-auto w-full">
                <!-- Inyectado dinámicamente vía app.js -->
            </div>
        </main>
    </div>

    <!-- SCRIPTS CORE -->
    <script src="/hub-core-kx92/assets/js/chart.min.js"></script>
    <script src="/hub-core-kx92/assets/js/app.js"></script>
</body>
</html>
