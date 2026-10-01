<?php
/* [PUBLIC-LANDING] - LANDING PÚBLICA DESACOPLADA - SIN ENLACES A LA RUTA PRIVADA */
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impresión 3D de Alta Precisión | Fabricación Digital</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-full flex flex-col justify-between bg-slate-950 text-slate-200">
    <header class="border-b border-slate-900 px-6 py-4 flex items-center justify-between max-w-6xl mx-auto w-full">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-orange-600 flex items-center justify-center font-bold text-white">3D</div>
            <span class="font-bold text-lg tracking-tight text-white">Farm Studio</span>
        </div>
        <nav class="text-sm text-slate-400 space-x-6">
            <a href="#servicios" class="hover:text-white transition">Servicios</a>
            <a href="#materiales" class="hover:text-white transition">Materiales</a>
            <a href="#contacto" class="hover:text-white transition">Contacto</a>
        </nav>
    </header>

    <main class="max-w-4xl mx-auto px-6 py-20 text-center">
        <span class="text-xs uppercase font-mono tracking-widest text-orange-500 font-bold">Servicios Industriales &amp; Prototipado</span>
        <h1 class="text-4xl sm:text-6xl font-black text-white tracking-tight mt-3">
            Fabricación Aditiva y Granjas de Impresión 3D
        </h1>
        <p class="text-slate-400 max-w-2xl mx-auto mt-6 text-base sm:text-lg">
            Producción en serie de piezas técnicas en PLA, PETG, ABS, TPU y polímeros reforzados con fibra de carbono (PPS-CF). Presupuestos a medida y entregas a todo el país.
        </p>
        <div class="mt-8 flex justify-center gap-4">
            <a href="mailto:info@farm3d.local" class="px-6 py-3 rounded-xl bg-orange-600 hover:bg-orange-500 text-white font-semibold text-sm transition shadow-lg shadow-orange-600/20">
                Solicitar Cotización
            </a>
        </div>
    </main>

    <footer class="border-t border-slate-900 py-6 text-center text-xs text-slate-600 font-mono">
        &copy; <?= date('Y') ?> Farm Studio. Todos los derechos reservados.
    </footer>
</body>
</html>
