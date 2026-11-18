<?php
declare(strict_types=1);
require __DIR__ . '/../cms/bootstrap.php';
session_start_secure();
if (is_logged_in()) {
    log_activity('logged out', 'user');
}
logout();
redirect('login.php');
