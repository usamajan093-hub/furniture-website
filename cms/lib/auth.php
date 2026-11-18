<?php
declare(strict_types=1);
defined('CMS') or die('No direct access');

function session_start_secure(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_name(cfg('session_name', 'amri_admin'));
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function is_logged_in(): bool
{
    return !empty($_SESSION['user']);
}

function require_login(): void
{
    if (!is_logged_in()) {
        $qs = (string)($_SERVER['QUERY_STRING'] ?? '');
        $next = basename((string)($_SERVER['SCRIPT_NAME'] ?? 'index.php')) . ($qs !== '' ? '?' . $qs : '');
        redirect('login.php?next=' . rawurlencode($next));
    }
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    $sent = $_POST['csrf'] ?? ($_SERVER['HTTP_X_CSRF'] ?? '');
    if (!is_string($sent) || !hash_equals(csrf_token(), $sent)) {
        http_response_code(403);
        die('Invalid or expired form token. Go back and try again.');
    }
}

function client_ip(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function login_blocked(): bool
{
    $ip = client_ip();
    $attempts = store_read('attempts', []);
    $row = $attempts[$ip] ?? null;
    if (!$row) {
        return false;
    }
    $window = cfg('login_window_min', 15) * 60;
    if (time() - ($row['last'] ?? 0) > $window) {
        return false;
    }
    return ($row['count'] ?? 0) >= cfg('login_max_attempts', 5);
}

function login_record_failure(): void
{
    $ip = client_ip();
    $window = cfg('login_window_min', 15) * 60;
    store_update('attempts', function (array $a) use ($ip, $window) {
        $row = $a[$ip] ?? ['count' => 0, 'last' => 0];
        if (time() - $row['last'] > $window) {
            $row['count'] = 0;
        }
        $row['count']++;
        $row['last'] = time();
        $a[$ip] = $row;
        // prune old entries
        foreach ($a as $k => $r) {
            if (time() - ($r['last'] ?? 0) > $window * 4) {
                unset($a[$k]);
            }
        }
        return $a;
    });
}

function login_clear_failures(): void
{
    $ip = client_ip();
    store_update('attempts', function (array $a) use ($ip) {
        unset($a[$ip]);
        return $a;
    });
}

function attempt_login(string $username, string $password): bool
{
    foreach (col_all('users') as $u) {
        if (strcasecmp($u['username'] ?? '', $username) === 0 && password_verify($password, $u['password_hash'] ?? '')) {
            session_regenerate_id(true);
            $_SESSION['user'] = $u['username'];
            $_SESSION['user_id'] = $u['id'];
            unset($_SESSION['csrf']);
            $u['last_login'] = now();
            col_save('users', $u);
            login_clear_failures();
            return true;
        }
    }
    login_record_failure();
    return false;
}

function logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}
