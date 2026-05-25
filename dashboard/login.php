<?php
require_once __DIR__ . '/src/bootstrap.php';

if (Auth::isLoggedIn()) {
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['_csrf'] ?? '')) {
        $error = 'שגיאת אבטחה – נסה שוב.';
    } elseif (Auth::login(trim($_POST['username'] ?? ''), $_POST['password'] ?? '')) {
        $next = $_GET['next'] ?? BASE_URL . '/dashboard.php';
        // Prevent open-redirect
        if (!str_starts_with($next, '/')) $next = BASE_URL . '/dashboard.php';
        header('Location: ' . $next);
        exit;
    } else {
        $error = 'שם משתמש או סיסמה שגויים.';
    }
}
?>
<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>כניסה | <?= h(APP_TITLE) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Heebo:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="login-page">
  <div class="login-card">
    <div class="login-logo">
      <i class="fa-solid fa-chart-pie"></i>
    </div>
    <h2><?= h(APP_TITLE) ?></h2>
    <p class="sub"><?= h(INSTITUTION) ?></p>

    <?php if ($error): ?>
      <div class="alert alert-danger py-2 text-center" role="alert">
        <i class="fa-solid fa-circle-exclamation me-1"></i> <?= h($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST">
      <input type="hidden" name="_csrf" value="<?= h(csrf_token()) ?>">

      <div class="mb-3">
        <label class="form-label fw-semibold">שם משתמש</label>
        <div class="input-group">
          <span class="input-group-text"><i class="fa-solid fa-user text-muted"></i></span>
          <input type="text" name="username" class="form-control" autofocus autocomplete="username"
                 value="<?= h($_POST['username'] ?? '') ?>" placeholder="הכנס שם משתמש" required>
        </div>
      </div>

      <div class="mb-4">
        <label class="form-label fw-semibold">סיסמה</label>
        <div class="input-group">
          <span class="input-group-text"><i class="fa-solid fa-lock text-muted"></i></span>
          <input type="password" name="password" class="form-control" autocomplete="current-password"
                 placeholder="הכנס סיסמה" required>
        </div>
      </div>

      <button type="submit" class="btn w-100 btn-primary-custom justify-content-center py-2" style="font-size:15px;">
        <i class="fa-solid fa-right-to-bracket"></i> כניסה למערכת
      </button>
    </form>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
