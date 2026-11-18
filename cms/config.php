<?php
// Site configuration. No secrets live here: storage is flat JSON files.
declare(strict_types=1);
defined('CMS') or die('No direct access');

return [
    'site_name'     => 'amroyalinteriors.com',
    'timezone'      => 'Asia/Karachi',
    'data_dir'      => dirname(__DIR__) . '/cms/data',
    'seed_dir'      => dirname(__DIR__) . '/cms/seed',
    'upload_dir'    => dirname(__DIR__) . '/uploads',
    'upload_url'    => 'uploads',
    'max_upload_mb' => 8,
    'allowed_types' => [
        'image/jpeg'    => ['jpg', 'jpeg'],
        'image/png'     => ['png'],
        'image/webp'    => ['webp'],
        'image/svg+xml' => ['svg'],
    ],
    'session_name'  => 'amri_admin',
    'login_max_attempts' => 5,
    'login_window_min'   => 15,
];
