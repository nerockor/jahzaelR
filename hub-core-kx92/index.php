<?php
/* [LOCK-MODULE: HUB-LOGIN] - NO MODIFICAR ESTE BLOQUE A MENOS QUE EL USUARIO LO ESPECIFIQUE */

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

// Si ya está autenticado, redirigir directo al dashboard
if (is_authenticated()) {
    header('Location: /hub-core-kx92/dashboard.php');
    exit;
}

$error_message = '';
$info_message = '';

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    logout_user();
    $info_message = 'Sesión finalizada correctamente.';
}

if (isset($_GET['error']) && $_GET['error'] === 'unauthorized') {
    $error_message = 'Acceso restringido: Inicia sesión para continuar.';
}

// Procesar formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted_token = $_POST['csrf_token'] ?? '';
    $usuario = trim((string)($_POST['usuario'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    // Validación CSRF estricta
    if (!verify_csrf_token($submitted_token)) {
        $error_message = 'Error de validación de seguridad (CSRF). Por favor recarga el formulario.';
    } elseif ($usuario === '' || $password === '') {
        $error_message = 'Por favor ingresa tu usuario y contraseña.';
    } else {
        try {
            $pdo = Database::getConnection();
            $auth_user = authenticate_user($usuario, $password, $pdo);

            if ($auth_user !== false) {
                login_user($auth_user);
                header('Location: /hub-core-kx92/dashboard.php');
                exit;
            } else {
                // Mensaje genérico para prevenir enumeración de usuarios
                $error_message = 'Credenciales inválidas o cuenta no autorizada.';
            }
        } catch (\Throwable $e) {
            error_log('[LOGIN-ERR] Excepción: ' . $e->getMessage());
            $error_message = 'Ocurrió un error al verificar las credenciales. Revisa la conexión a la base de datos.';
        }
    }
}

$csrf_token = get_csrf_token();
?>
<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <title>3D Farm Manager | Acceso Restringido</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .glow-box {
            box-shadow: 0 0 40px -10px rgba(249, 115, 22, 0.25);
        }
    </style>
</head>
<body class="h-full flex items-center justify-center p-4 bg-radial from-slate-900 via-slate-950 to-black">
    <div class="w-full max-w-md">
        <!-- Logo y Cabecera -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-amber-600 to-orange-500 text-white font-bold text-2xl shadow-lg shadow-orange-500/20 mb-4 border border-orange-400/30">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-9 h-9" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5M6 18l-2-1v-2.5M18 18l2-1v-2.5" />
                </svg>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-white uppercase">3D Farm Manager</h1>
            <p class="text-xs uppercase tracking-widest text-orange-400 font-semibold mt-1">Acceso a Hub Privado [kx92]</p>
        </div>

        <!-- Tarjeta de Login -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-8 backdrop-blur-xl glow-box">
            <?php if (!empty($error_message)): ?>
                <div class="mb-5 p-3.5 rounded-xl bg-red-950/60 border border-red-800/80 text-red-200 text-sm flex items-center gap-3">
                    <svg class="w-5 h-5 flex-shrink-0 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <span><?= htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($info_message)): ?>
                <div class="mb-5 p-3.5 rounded-xl bg-emerald-950/60 border border-emerald-800/80 text-emerald-200 text-sm flex items-center gap-3">
                    <svg class="w-5 h-5 flex-shrink-0 text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span><?= htmlspecialchars($info_message, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="/hub-core-kx92/index.php" class="space-y-5">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">

                <div>
                    <label for="usuario" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Usuario Operador</label>
                    <div class="relative">
                        <input type="text" id="usuario" name="usuario" required autofocus autocomplete="username"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition text-sm"
                               placeholder="admin">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Contraseña</label>
                    <div class="relative">
                        <input type="password" id="password" name="password" required autocomplete="current-password"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition text-sm"
                               placeholder="••••••••••••">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit"
                            class="w-full bg-gradient-to-r from-orange-600 to-amber-600 hover:from-orange-500 hover:to-amber-500 text-white font-semibold py-3 px-4 rounded-xl shadow-lg shadow-orange-600/30 transition duration-150 ease-in-out transform active:scale-[0.99] flex items-center justify-center gap-2 text-sm uppercase tracking-wide">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                        <span>Ingresar al Sistema</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Footer discreto -->
        <p class="text-center text-xs text-slate-600 mt-6 font-mono">
            &copy; 3D Farm Manager &bull; Namespace Ofuscado Activo
        </p>
    </div>
</body>
</html>
