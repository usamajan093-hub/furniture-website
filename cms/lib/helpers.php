<?php
declare(strict_types=1);
defined('CMS') or die('No direct access');

function e(?string $s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function cfg(string $key, $default = null)
{
    static $cfg = null;
    if ($cfg === null) {
        $cfg = require __DIR__ . '/../config.php';
    }
    return $cfg[$key] ?? $default;
}

function slugify(string $s): string
{
    $s = strtolower(trim($s));
    $s = preg_replace('/[^a-z0-9]+/', '-', $s) ?? '';
    $s = trim($s, '-');
    return $s === '' ? 'item' : $s;
}

function now(): string
{
    return date('Y-m-d H:i');
}

function uid(): string
{
    return bin2hex(random_bytes(6));
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function json_response($data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function post(string $key, $default = '')
{
    return $_POST[$key] ?? $default;
}

function post_str(string $key, string $default = ''): string
{
    $v = $_POST[$key] ?? $default;
    return is_string($v) ? trim($v) : $default;
}

function post_int(string $key, int $default = 0): int
{
    return (int)($_POST[$key] ?? $default);
}

function post_float(string $key): ?float
{
    $v = trim((string)($_POST[$key] ?? ''));
    return $v === '' ? null : (float)str_replace(',', '', $v);
}

/** Deep-clean a nested array of strings from a submitted form. */
function clean_tree($v)
{
    if (is_array($v)) {
        $out = [];
        foreach ($v as $k => $x) {
            $out[$k] = clean_tree($x);
        }
        return $out;
    }
    return is_string($v) ? trim($v) : $v;
}

/** Turn a numeric-keyed array (possibly with gaps) into a list. */
function as_list($v): array
{
    if (!is_array($v)) {
        return [];
    }
    ksort($v);
    return array_values($v);
}

function money(?float $v): string
{
    if ($v === null) {
        return '';
    }
    return 'Rs ' . number_format($v, (floor($v) == $v) ? 0 : 2);
}

function nice_date(?string $d): string
{
    if (!$d) {
        return '';
    }
    $t = strtotime($d);
    return $t ? date('j M Y', $t) : $d;
}

function log_activity(string $action, string $entity = '', string $label = ''): void
{
    $user = $_SESSION['user'] ?? 'system';
    store_update('activity', function (array $rows) use ($action, $entity, $label, $user) {
        array_unshift($rows, [
            'at'     => now(),
            'user'   => $user,
            'action' => $action,
            'entity' => $entity,
            'label'  => $label,
        ]);
        return array_slice($rows, 0, 200);
    });
}

/** Flash messages across a redirect. */
function flash(string $type, string $msg): void
{
    $_SESSION['flash'][] = ['type' => $type, 'msg' => $msg];
}

function take_flash(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}
