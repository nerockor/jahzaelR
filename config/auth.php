<?php
/* [LOCK-MODULE: CORE-AUTH] - NO MODIFICAR ESTE BLOQUE A MENOS QUE EL USUARIO LO ESPECIFIQUE */

declare(strict_types=1);

/**
 * Middleware y Utilidades de Autenticación, Sesión y Seguridad CSRF
 */

// Iniciar sesión segura si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    $tempDir = sys_get_temp_dir();
    $currentSavePath = session_save_path();
    if (empty($currentSavePath) || !@is_writable($currentSavePath)) {
        @session_save_path($tempDir);
    }

    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);
    
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);

    @session_start();
}

/**
 * Genera o recupera el token CSRF para el usuario actual.
 */
function get_csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Valida un token CSRF provisto contra la sesión activa.
 */
function verify_csrf_token(?string $token): bool
{
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Autentica un usuario contra la base de datos usando password_verify.
 * Retorna los datos del usuario si las credenciales son válidas, o false.
 */
function authenticate_user(string $usuario, string $password, PDO $pdo): array|false
{
    $stmt = $pdo->prepare('SELECT id, usuario, password_hash, nombre, email, activo FROM usuarios WHERE usuario = :usuario LIMIT 1');
    $stmt->execute([':usuario' => trim($usuario)]);
    $user = $stmt->fetch();

    if ($user && (int)$user['activo'] === 1 && password_verify($password, $user['password_hash'])) {
        // Actualizar marca de último acceso
        $upd = $pdo->prepare('UPDATE usuarios SET ultimo_acceso = CURRENT_TIMESTAMP WHERE id = :id');
        $upd->execute([':id' => $user['id']]);

        // Retornar datos seguros (sin el hash)
        unset($user['password_hash']);
        return $user;
    }

    return false;
}

/**
 * Inicia la sesión autenticada regenerando el ID de sesión para prevenir Session Fixation.
 */
function login_user(array $userData): void
{
    session_regenerate_id(true);
    $_SESSION['auth_user'] = [
        'id'      => $userData['id'],
        'usuario' => $userData['usuario'],
        'nombre'  => $userData['nombre'],
        'email'   => $userData['email'] ?? null,
        'login_at'=> time()
    ];
    // Regenerar token CSRF tras elevación de privilegios
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * Cierra la sesión activa de forma segura.
 */
function logout_user(): void
{
    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    session_destroy();
}

/**
 * Verifica si existe una sesión válida y autenticada.
 */
function is_authenticated(): bool
{
    return !empty($_SESSION['auth_user']) && isset($_SESSION['auth_user']['id']);
}

/**
 * Devuelve el usuario actualmente autenticado o null.
 */
function get_authenticated_user(): ?array
{
    return is_authenticated() ? $_SESSION['auth_user'] : null;
}

/**
 * Guardián de acceso: Redirige al login de la ruta privada si no hay sesión activa.
 */
function require_auth(): void
{
    if (!is_authenticated()) {
        header('Location: /hub-core-kx92/index.php?error=unauthorized');
        exit;
    }
}
