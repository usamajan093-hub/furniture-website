<?php
declare(strict_types=1);
define('CMS', true);
define('CMS_ROOT', dirname(__DIR__));

require __DIR__ . '/lib/helpers.php';
require __DIR__ . '/lib/store.php';
require __DIR__ . '/lib/auth.php';
require __DIR__ . '/lib/media.php';
require __DIR__ . '/lib/schema.php';
require __DIR__ . '/lib/site.php';
require __DIR__ . '/lib/render.php';

date_default_timezone_set(cfg('timezone', 'Asia/Karachi'));
mb_internal_encoding('UTF-8');

function cms_installed(): bool
{
    return store_exists('users') && count(col_all('users')) > 0;
}
