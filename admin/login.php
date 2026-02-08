<?php
declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';

if (is_admin_logged_in()) {
    header('Location: index.php');
    exit;
}

$next = isset($_GET['next']) && is_string($_GET['next']) ? $_GET['next'] : 'index.php';
if (!str_starts_with($next, '/')) {
    // allow relative within /admin
    $next = 'index.php';
}

$error = null;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $postedNext = isset($_POST['next']) && is_string($_POST['next']) ? $_POST['next'] : 'index.php';

    $user = isset($_POST['username']) && is_string($_POST['username']) ? trim($_POST['username']) : '';
    $pass = isset($_POST['password']) && is_string($_POST['password']) ? $_POST['password'] : '';

    $passOk = false;
    if (defined('ADMIN_PASS')) {
        $passOk = hash_equals(ADMIN_PASS, $pass);
    } elseif (defined('ADMIN_PASS_HASH')) {
        // legacy fallback
        $passOk = password_verify($pass, ADMIN_PASS_HASH);
    }

    if ($user !== ADMIN_USER || !$passOk) {
        $error = 'Špatné jméno nebo heslo.';
        usleep(250_000);
    } else {
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user'] = ADMIN_USER;
        header('Location: ' . $postedNext);
        exit;
    }

    $next = $postedNext;
}

?><!doctype html>
<html lang="cs">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - přihlášení</title>
    <style>
        body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; background: #f5f5f7; margin: 0; }
        .wrap { min-height: 100vh; display: grid; place-items: center; padding: 24px; }
        .card { width: 100%; max-width: 380px; background: #fff; border: 1px solid #e7e7ea; border-radius: 12px; padding: 20px; box-shadow: 0 10px 25px rgba(0,0,0,.06); }
        h1 { font-size: 18px; margin: 0 0 12px; }
        label { display:block; font-size: 13px; margin: 12px 0 6px; color: #333; }
        input { width:100%; padding:10px 12px; border:1px solid #cfd0d7; border-radius: 10px; font-size: 14px; }
        button { margin-top: 14px; width:100%; padding: 10px 12px; border:0; border-radius: 10px; background:#111827; color:#fff; font-size: 14px; cursor: pointer; }
        .error { margin: 10px 0 0; background:#fff1f2; border:1px solid #fecdd3; color:#9f1239; padding:10px 12px; border-radius: 10px; font-size: 13px; }
        .hint { margin-top: 10px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <h1>Administrace – přihlášení</h1>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></div>
        <?php endif; ?>

        <form method="post" action="login.php">
            <input type="hidden" name="next" value="<?= htmlspecialchars($next, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">

            <label for="username">Uživatelské jméno</label>
            <input id="username" name="username" autocomplete="username" required>

            <label for="password">Heslo</label>
            <input id="password" type="password" name="password" autocomplete="current-password" required>

            <button type="submit">Přihlásit</button>
        </form>

        <div class="hint">Přihlašovací údaje jsou v <code>config/admin.secret.php</code> (preferované) nebo <code>admin/.env.php</code>.</div>
    </div>
</div>
</body>
</html>