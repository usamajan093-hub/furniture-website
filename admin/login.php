<?php
declare(strict_types=1);
require __DIR__ . '/../cms/bootstrap.php';
session_start_secure();

if (!cms_installed()) {
    redirect('../install.php');
}
if (is_logged_in()) {
    redirect('index.php');
}
$error = '';
$next = (string)($_GET['next'] ?? $_POST['next'] ?? 'index.php');
if (!preg_match('#^[a-z_]+\.php(\?[^\s]*)?$#i', $next)) {
    $next = 'index.php';
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (login_blocked()) {
        $error = 'Too many failed attempts. Try again in ' . cfg('login_window_min') . ' minutes.';
    } elseif (attempt_login(post_str('username'), (string)post('password'))) {
        log_activity('logged in', 'user');
        redirect($next);
    } else {
        $error = 'Wrong username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin login</title>
<link rel="icon" href="../assets/favicon.svg" type="image/svg+xml">
<link rel="stylesheet" href="assets/admin.css"></head>
<body class="auth-body">
<div class="auth-card">
  <div class="auth-logo"><span>R</span></div>
  <h1>Admin login</h1>
  <p class="muted">Sign in to manage amroyalinteriors.com</p>
  <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
  <form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="next" value="<?= e($next) ?>">
    <label>Username<input name="username" autocomplete="username" required autofocus></label>
    <label>Password<input type="password" name="password" autocomplete="current-password" required></label>
    <button class="btn btn-primary btn-block" type="submit">Log in</button>
  </form>
</div>
</body></html>
