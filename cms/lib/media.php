<?php
declare(strict_types=1);
defined('CMS') or die('No direct access');

/**
 * Upload handling for the media library. Validates type by content (finfo),
 * extension by whitelist, size by config, and sanitizes SVG files.
 */

function media_validate_and_store(array $file): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'error' => 'Upload failed (code ' . ($file['error'] ?? '?') . ').'];
    }
    $max = (int)cfg('max_upload_mb', 8) * 1024 * 1024;
    if (($file['size'] ?? 0) > $max) {
        return ['ok' => false, 'error' => 'File is larger than ' . cfg('max_upload_mb') . ' MB.'];
    }
    $tmp = $file['tmp_name'];
    if (!is_uploaded_file($tmp)) {
        return ['ok' => false, 'error' => 'Invalid upload.'];
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = (string)$finfo->file($tmp);
    $allowed = cfg('allowed_types');
    // Some SVGs are detected as text/xml or text/plain.
    $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
    if ($ext === 'svg' && in_array($mime, ['text/xml', 'text/plain', 'application/xml', 'image/svg'], true)) {
        $mime = 'image/svg+xml';
    }
    if (!isset($allowed[$mime]) || !in_array($ext, $allowed[$mime], true)) {
        return ['ok' => false, 'error' => 'Only JPG, PNG, WebP and SVG files are allowed.'];
    }

    $width = $height = null;
    if ($mime === 'image/svg+xml') {
        $svg = (string)file_get_contents($tmp);
        $svg = media_sanitize_svg($svg);
        if ($svg === null) {
            return ['ok' => false, 'error' => 'SVG contains scripts or external references and was rejected.'];
        }
        file_put_contents($tmp, $svg);
    } else {
        $info = @getimagesize($tmp);
        if ($info === false) {
            return ['ok' => false, 'error' => 'File is not a valid image.'];
        }
        [$width, $height] = $info;
    }

    $dir = cfg('upload_dir');
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $base = slugify(pathinfo($file['name'] ?? 'image', PATHINFO_FILENAME));
    $base = substr($base, 0, 60);
    $name = $base . '.' . ($ext === 'jpeg' ? 'jpg' : $ext);
    $i = 1;
    while (file_exists($dir . '/' . $name)) {
        $name = $base . '-' . (++$i) . '.' . ($ext === 'jpeg' ? 'jpg' : $ext);
    }
    if (!move_uploaded_file($tmp, $dir . '/' . $name)) {
        return ['ok' => false, 'error' => 'Could not save the file. Check that uploads/ is writable.'];
    }
    @chmod($dir . '/' . $name, 0644);

    $row = [
        'id'         => uid(),
        'url'        => cfg('upload_url') . '/' . $name,
        'title'      => ucwords(str_replace('-', ' ', $base)),
        'category'   => '',
        'mime'       => $mime,
        'size'       => (int)$file['size'],
        'width'      => $width,
        'height'     => $height,
        'created_at' => now(),
        'managed'    => true,   // file lives in uploads/ and may be deleted
    ];
    return ['ok' => true, 'row' => $row];
}

/** Strip scripting from SVG. Returns null if it cannot be made safe. */
function media_sanitize_svg(string $svg): ?string
{
    if (stripos($svg, '<svg') === false) {
        return null;
    }
    $svg = preg_replace('/<\?xml[^>]*\?>/i', '', $svg) ?? $svg;
    $svg = preg_replace('/<!DOCTYPE[^>]*>/i', '', $svg) ?? $svg;
    $svg = preg_replace('#<script\b[^>]*>.*?</script>#is', '', $svg) ?? $svg;
    $svg = preg_replace('#<(foreignObject|iframe|embed|object)\b[^>]*>.*?</\1>#is', '', $svg) ?? $svg;
    $svg = preg_replace('/\son[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $svg) ?? $svg;
    $svg = preg_replace('/(xlink:href|href)\s*=\s*("|\')\s*(javascript|data):[^"\']*\2/i', '$1=""', $svg) ?? $svg;
    if (preg_match('/<script|javascript:|<foreignObject/i', $svg)) {
        return null;
    }
    return trim($svg);
}

function media_delete_row(array $row): void
{
    if (!empty($row['managed']) && !empty($row['url'])) {
        $path = dirname(__DIR__, 2) . '/' . $row['url'];
        $real = realpath($path);
        $updir = realpath(cfg('upload_dir'));
        if ($real && $updir && strpos($real, $updir) === 0 && is_file($real)) {
            unlink($real);
        }
    }
    col_delete('media', $row['id']);
}

function media_categories(): array
{
    $cats = [];
    foreach (col_all('media') as $m) {
        if (!empty($m['category'])) {
            $cats[$m['category']] = true;
        }
    }
    $cats = array_keys($cats);
    sort($cats);
    return $cats;
}
