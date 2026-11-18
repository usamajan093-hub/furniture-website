<?php
declare(strict_types=1);
require __DIR__ . '/inc/guard.php';

$exportPath = CMS_ROOT . '/index.html';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $act = post_str('action');
    if ($act === 'password') {
        $u = col_find('users', (string)($_SESSION['user_id'] ?? ''));
        $cur = (string)post('current');
        $new = (string)post('new');
        if (!$u || !password_verify($cur, $u['password_hash'])) {
            flash('error', 'Current password is wrong.');
        } elseif (strlen($new) < 8) {
            flash('error', 'New password must be at least 8 characters.');
        } elseif ($new !== (string)post('new2')) {
            flash('error', 'New passwords do not match.');
        } else {
            $u['password_hash'] = password_hash($new, PASSWORD_DEFAULT);
            col_save('users', $u);
            log_activity('changed password', 'user');
            flash('success', 'Password changed.');
        }
        redirect('settings.php');
    }
    if ($act === 'username') {
        $u = col_find('users', (string)($_SESSION['user_id'] ?? ''));
        $name = post_str('username');
        if (!$u || !preg_match('/^[a-zA-Z0-9_.-]{3,30}$/', $name)) {
            flash('error', 'Username: 3–30 letters, numbers, dots, dashes or underscores.');
        } else {
            $u['username'] = $name;
            col_save('users', $u);
            $_SESSION['user'] = $name;
            log_activity('changed username', 'user', $name);
            flash('success', 'Username changed.');
        }
        redirect('settings.php');
    }
    if ($act === 'export') {
        $html = render_site(['base' => '']);
        if (@file_put_contents($exportPath, $html) === false) {
            flash('error', 'Could not write index.html. Check file permissions.');
        } else {
            log_activity('published static copy', 'system', 'index.html');
            flash('success', 'index.html regenerated from the current content. Commit and push it to update GitHub Pages.');
        }
        redirect('settings.php');
    }
    if ($act === 'backup') {
        if (!class_exists('ZipArchive')) {
            flash('error', 'ZipArchive is not available on this server.');
            redirect('settings.php');
        }
        $tmp = tempnam(sys_get_temp_dir(), 'bk');
        $zip = new ZipArchive();
        $zip->open($tmp, ZipArchive::OVERWRITE);
        foreach (['data' => cfg('data_dir'), 'uploads' => cfg('upload_dir')] as $label => $dir) {
            if (!is_dir($dir)) {
                continue;
            }
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)) as $f) {
                $rel = substr($f->getPathname(), strlen($dir) + 1);
                if (str_ends_with($rel, '.lock') || str_ends_with($rel, '.tmp') || $rel === 'users.json' || $rel === 'attempts.json') {
                    continue;
                }
                $zip->addFile($f->getPathname(), $label . '/' . str_replace('\\', '/', $rel));
            }
        }
        $zip->close();
        log_activity('downloaded backup', 'system');
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="cms-backup-' . date('Y-m-d') . '.zip"');
        header('Content-Length: ' . filesize($tmp));
        readfile($tmp);
        unlink($tmp);
        exit;
    }
}

$u = col_find('users', (string)($_SESSION['user_id'] ?? '')) ?? [];
$exportTime = is_file($exportPath) ? date('j M Y H:i', (int)filemtime($exportPath)) : 'never';
admin_page_start('Settings', 'settings');
?>
<div class="two-col">
  <div style="display:grid;gap:22px">
    <div class="card"><div class="card-head"><h2>Publish static copy for GitHub Pages</h2></div><div class="card-body">
      <p class="muted" style="margin-bottom:14px">GitHub Pages cannot run the admin panel, but it can host a static snapshot. This button rebuilds <code>index.html</code> from the current content, template, offers and pages. Afterwards commit and push the repository (including <code>uploads/</code>) and the GitHub Pages site updates too.</p>
      <p class="small" style="margin-bottom:14px">Last static copy: <b><?= e($exportTime) ?></b></p>
      <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="export"><button class="btn btn-primary" type="submit"><?= admin_icon('upload') ?> Regenerate index.html</button></form>
    </div></div>
    <div class="card"><div class="card-head"><h2>Backup</h2></div><div class="card-body">
      <p class="muted" style="margin-bottom:14px">Downloads a ZIP of all content, offers, templates, pages, the media index and every uploaded image. To restore, unzip into <code>cms/data/</code> and <code>uploads/</code>.</p>
      <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="backup"><button class="btn btn-light" type="submit">Download backup ZIP</button></form>
    </div></div>
    <div class="card"><div class="card-head"><h2>Security notes</h2></div><div class="card-body small muted">
      <ul style="padding-left:18px;display:grid;gap:6px">
        <li>Delete <code>install.php</code> from the server after setup.</li>
        <li>Passwords are stored as one-way hashes. There is no database and no credentials to leak.</li>
        <li><code>cms/</code> is blocked from the web; <code>uploads/</code> cannot run scripts; every form carries a token; logins are limited to <?= (int)cfg('login_max_attempts') ?> attempts per <?= (int)cfg('login_window_min') ?> minutes.</li>
        <li>Use HTTPS on the hosting so the admin cookie travels encrypted.</li>
      </ul>
    </div></div>
  </div>
  <div style="display:grid;gap:22px">
    <div class="card"><div class="card-head"><h2>Change password</h2></div><div class="card-body">
      <form method="post" style="display:grid;gap:12px"><?= csrf_field() ?><input type="hidden" name="action" value="password">
        <div class="f"><label>Current password</label><input type="password" name="current" required autocomplete="current-password"></div>
        <div class="f"><label>New password</label><input type="password" name="new" required minlength="8" autocomplete="new-password"></div>
        <div class="f"><label>Repeat new password</label><input type="password" name="new2" required minlength="8" autocomplete="new-password"></div>
        <button class="btn btn-primary" type="submit">Update password</button>
      </form>
    </div></div>
    <div class="card"><div class="card-head"><h2>Username</h2></div><div class="card-body">
      <form method="post" style="display:grid;gap:12px"><?= csrf_field() ?><input type="hidden" name="action" value="username">
        <div class="f"><label>Username</label><input type="text" name="username" value="<?= e($u['username'] ?? '') ?>" required></div>
        <button class="btn btn-light" type="submit">Update username</button>
      </form>
      <p class="small muted" style="margin-top:12px">Last login: <?= e($u['last_login'] ?? '—') ?></p>
    </div></div>
  </div>
</div>
<?php admin_page_end();
