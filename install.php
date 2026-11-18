<?php
declare(strict_types=1);
require __DIR__ . '/cms/bootstrap.php';
session_start_secure();

$dataDir   = cfg('data_dir');
$uploadDir = cfg('upload_dir');
$seedDir   = cfg('seed_dir');
$errors    = [];
$done      = false;

if (cms_installed()) {
    $already = true;
} else {
    $already = false;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_check();
        $user = post_str('username');
        $pass = (string)post('password');
        $pass2 = (string)post('password2');
        if (!preg_match('/^[a-zA-Z0-9_.-]{3,30}$/', $user)) {
            $errors[] = 'Username: 3–30 letters, numbers, dots, dashes or underscores.';
        }
        if (strlen($pass) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        }
        if ($pass !== $pass2) {
            $errors[] = 'Passwords do not match.';
        }
        foreach ([$dataDir, $uploadDir] as $d) {
            if (!is_dir($d) && !@mkdir($d, 0755, true)) {
                $errors[] = 'Cannot create ' . $d;
            } elseif (!is_writable($d)) {
                $errors[] = $d . ' is not writable. Set its permissions to 755 (or 775).';
            }
        }
        if (!$errors) {
            foreach (['content', 'templates', 'media', 'offers', 'pages'] as $name) {
                if (!store_exists($name)) {
                    $seed = $seedDir . '/' . $name . '.json';
                    $data = is_file($seed) ? json_decode((string)file_get_contents($seed), true) : [];
                    store_put($name, $data ?? []);
                }
            }
            store_put('users', [[
                'id' => uid(), 'username' => $user,
                'password_hash' => password_hash($pass, PASSWORD_DEFAULT),
                'created_at' => now(), 'last_login' => null,
            ]]);
            store_put('activity', [['at' => now(), 'user' => $user, 'action' => 'installed the CMS', 'entity' => 'system', 'label' => '']]);
            foreach ([$dataDir, $uploadDir] as $d) {
                if (!is_file($d . '/.htaccess')) {
                    file_put_contents($d . '/.htaccess', $d === $dataDir ? "Require all denied\n" : "<FilesMatch \"\\.(php|phtml|php[0-9]|phar|cgi|pl|py)$\">\nRequire all denied\n</FilesMatch>\nOptions -ExecCGI\n");
                }
            }
            $done = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Set up the CMS</title>
<link rel="stylesheet" href="admin/assets/admin.css"></head>
<body class="auth-body">
<div class="auth-card">
  <div class="auth-logo"><span>R</span></div>
  <h1>Set up your website admin</h1>
<?php if ($already): ?>
  <p class="muted">The CMS is already installed. For safety, delete <code>install.php</code> from the server.</p>
  <a class="btn btn-primary" href="admin/login.php">Go to login</a>
<?php elseif ($done): ?>
  <p class="muted">Done. Your content, templates and photos were imported from the existing site. Delete <code>install.php</code> from the server now.</p>
  <a class="btn btn-primary" href="admin/login.php">Log in to the admin</a>
<?php else: ?>
  <p class="muted">Create the first admin account. The site's current text and photos will be imported automatically.</p>
  <?php foreach ($errors as $er): ?><div class="alert alert-error"><?= e($er) ?></div><?php endforeach; ?>
  <form method="post" autocomplete="off">
    <?= csrf_field() ?>
    <label>Admin username<input name="username" value="<?= e(post_str('username')) ?>" required></label>
    <label>Password (8+ characters)<input type="password" name="password" required minlength="8"></label>
    <label>Repeat password<input type="password" name="password2" required minlength="8"></label>
    <button class="btn btn-primary btn-block" type="submit">Install</button>
  </form>
  <p class="muted small">Requirements: PHP 8.0+, write access to <code>cms/data/</code> and <code>uploads/</code>.</p>
<?php endif; ?>
</div>
</body></html>
