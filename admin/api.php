<?php
declare(strict_types=1);
require __DIR__ . '/../cms/bootstrap.php';
session_start_secure();
if (!is_logged_in()) {
    json_response(['error' => 'Not logged in'], 401);
}
$action = (string)($_GET['action'] ?? '');
$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
if ($isPost) {
    csrf_check();
}
$body = [];
if ($isPost && str_starts_with((string)($_SERVER['CONTENT_TYPE'] ?? ''), 'application/json')) {
    $body = json_decode((string)file_get_contents('php://input'), true) ?: [];
}

switch ($action) {
    case 'media_list':
        $q = strtolower((string)($_GET['q'] ?? ''));
        $cat = (string)($_GET['cat'] ?? '');
        $items = array_filter(col_all('media'), function ($m) use ($q, $cat) {
            if ($cat !== '' && ($m['category'] ?? '') !== $cat) {
                return false;
            }
            return $q === '' || str_contains(strtolower(($m['title'] ?? '') . ' ' . ($m['url'] ?? '')), $q);
        });
        json_response(['items' => array_values($items), 'categories' => media_categories()]);

    case 'media_upload':
        if (!$isPost || empty($_FILES['files'])) {
            json_response(['error' => 'No files received'], 400);
        }
        $files = $_FILES['files'];
        $items = $errors = [];
        $n = is_array($files['name']) ? count($files['name']) : 0;
        for ($i = 0; $i < $n; $i++) {
            $f = ['name' => $files['name'][$i], 'type' => $files['type'][$i], 'tmp_name' => $files['tmp_name'][$i], 'error' => $files['error'][$i], 'size' => $files['size'][$i]];
            $res = media_validate_and_store($f);
            if ($res['ok']) {
                $row = $res['row'];
                $cat = trim((string)($_POST['category'] ?? ''));
                if ($cat !== '') {
                    $row['category'] = $cat;
                }
                col_save('media', $row);
                $items[] = $row;
            } else {
                $errors[] = $f['name'] . ': ' . $res['error'];
            }
        }
        if ($items) {
            log_activity('uploaded ' . count($items) . ' image(s)', 'media', $items[0]['title'] ?? '');
        }
        json_response(['items' => $items, 'errors' => $errors]);

    case 'media_update':
        $row = col_find('media', (string)($body['id'] ?? ''));
        if (!$row) {
            json_response(['error' => 'Not found'], 404);
        }
        $row['title'] = trim((string)($body['title'] ?? $row['title']));
        $row['category'] = trim((string)($body['category'] ?? $row['category']));
        col_save('media', $row);
        log_activity('updated image', 'media', $row['title']);
        json_response(['ok' => true, 'item' => $row]);

    case 'media_delete':
        $row = col_find('media', (string)($body['id'] ?? ''));
        if (!$row) {
            json_response(['error' => 'Not found'], 404);
        }
        media_delete_row($row);
        log_activity('deleted image', 'media', $row['title'] ?? '');
        json_response(['ok' => true]);

    case 'offer_status':
        $o = col_find('offers', (string)($body['id'] ?? ''));
        if (!$o) {
            json_response(['error' => 'Not found'], 404);
        }
        $o['status'] = ($body['status'] ?? '') === 'published' ? 'published' : 'draft';
        col_save('offers', $o);
        log_activity($o['status'] === 'published' ? 'published offer' : 'unpublished offer', 'offer', $o['title']);
        json_response(['ok' => true, 'state' => offer_state($o)]);

    default:
        json_response(['error' => 'Unknown action'], 404);
}
