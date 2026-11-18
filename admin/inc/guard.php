<?php
declare(strict_types=1);
// Common prologue for every admin screen.
require __DIR__ . '/../../cms/bootstrap.php';
require __DIR__ . '/layout.php';
session_start_secure();
if (!cms_installed()) {
    redirect('../install.php');
}
require_login();
