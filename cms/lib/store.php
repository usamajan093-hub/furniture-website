<?php
declare(strict_types=1);
defined('CMS') or die('No direct access');

/**
 * Flat-file JSON store. One file per collection in cms/data.
 * Writes are atomic (temp file + rename) under an exclusive lock.
 * Reads are cached per request and refreshed after every write.
 */

function store_path(string $name): string
{
    if (!preg_match('/^[a-z_]+$/', $name)) {
        throw new InvalidArgumentException('Bad store name');
    }
    return cfg('data_dir') . '/' . $name . '.json';
}

function store_exists(string $name): bool
{
    return is_file(store_path($name));
}

function store_read(string $name, $default = [])
{
    if (array_key_exists($name, $GLOBALS['__store_cache'] ?? [])) {
        return $GLOBALS['__store_cache'][$name];
    }
    $path = store_path($name);
    $data = $default;
    if (is_file($path)) {
        $decoded = json_decode((string)file_get_contents($path), true);
        if (json_last_error() === JSON_ERROR_NONE && $decoded !== null) {
            $data = $decoded;
        }
    }
    $GLOBALS['__store_cache'][$name] = $data;
    return $data;
}

function store_write_locked(string $path, $data): void
{
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        throw new RuntimeException('Could not encode JSON for ' . basename($path));
    }
    $tmp = $path . '.' . uid() . '.tmp';
    if (file_put_contents($tmp, $json) === false) {
        throw new RuntimeException('Could not write ' . basename($path) . '. Is cms/data writable?');
    }
    if (!@rename($tmp, $path)) {
        copy($tmp, $path);
        unlink($tmp);
    }
}

function store_put(string $name, $data): void
{
    store_update($name, fn() => $data);
}

/** Read-modify-write under lock. $fn receives the current data and returns the new data. */
function store_update(string $name, callable $fn, $default = []): void
{
    $path = store_path($name);
    $dir  = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $lock = fopen($path . '.lock', 'c');
    if ($lock === false) {
        throw new RuntimeException('Could not lock ' . $name);
    }
    flock($lock, LOCK_EX);
    $current = $default;
    if (is_file($path)) {
        $decoded = json_decode((string)file_get_contents($path), true);
        if (json_last_error() === JSON_ERROR_NONE && $decoded !== null) {
            $current = $decoded;
        }
    }
    $new = $fn($current);
    store_write_locked($path, $new);
    flock($lock, LOCK_UN);
    fclose($lock);
    $GLOBALS['__store_cache'][$name] = $new;
}

/* ---------- collection helpers (list of rows with 'id') ---------- */

function col_all(string $name): array
{
    $rows = store_read($name, []);
    return is_array($rows) ? array_values($rows) : [];
}

function col_find(string $name, string $id): ?array
{
    foreach (col_all($name) as $row) {
        if (($row['id'] ?? null) === $id) {
            return $row;
        }
    }
    return null;
}

function col_save(string $name, array $row): array
{
    if (empty($row['id'])) {
        $row['id'] = uid();
        $row['created_at'] = $row['created_at'] ?? now();
    }
    $row['updated_at'] = now();
    store_update($name, function (array $rows) use ($row) {
        $found = false;
        foreach ($rows as $i => $r) {
            if (($r['id'] ?? null) === $row['id']) {
                $rows[$i] = $row;
                $found = true;
                break;
            }
        }
        if (!$found) {
            array_unshift($rows, $row);
        }
        return array_values($rows);
    });
    return $row;
}

function col_delete(string $name, string $id): void
{
    store_update($name, function (array $rows) use ($id) {
        return array_values(array_filter($rows, fn($r) => ($r['id'] ?? null) !== $id));
    });
}
