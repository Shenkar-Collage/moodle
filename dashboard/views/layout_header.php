<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($pageTitle ?? APP_TITLE) ?> | <?= h(INSTITUTION) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Heebo:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="d-flex" id="app-wrapper">

  <!-- Sidebar -->
  <nav id="sidebar">
    <div class="sidebar-header">
      <div class="sidebar-logo">
        <i class="fa-solid fa-chart-pie"></i>
      </div>
      <div>
        <div class="sidebar-title"><?= h(APP_TITLE) ?></div>
        <div class="sidebar-sub"><?= h(INSTITUTION) ?></div>
      </div>
    </div>

    <div class="sidebar-user">
      <div class="user-avatar"><?= mb_substr(h(Auth::getUser()['name'] ?? '?'), 0, 1) ?></div>
      <div>
        <div class="user-name"><?= h(Auth::getUser()['name'] ?? '') ?></div>
        <div class="user-role"><?= Auth::isAdmin() ? 'מנהל מערכת' : 'ראש חוג' ?></div>
      </div>
    </div>

    <ul class="sidebar-nav">
      <li>
        <a href="<?= BASE_URL ?>/dashboard.php" class="<?= (basename($_SERVER['PHP_SELF']) === 'dashboard.php') ? 'active' : '' ?>">
          <i class="fa-solid fa-table-columns"></i> לוח בקרה
        </a>
      </li>
      <?php if (Auth::isAdmin()): ?>
      <li>
        <a href="<?= BASE_URL ?>/admin/index.php" class="<?= (strpos($_SERVER['PHP_SELF'], '/admin/') !== false && basename($_SERVER['PHP_SELF']) === 'index.php') ? 'active' : '' ?>">
          <i class="fa-solid fa-users-gear"></i> ניהול משתמשים
        </a>
      </li>
      <li>
        <a href="<?= BASE_URL ?>/admin/settings.php" class="<?= (basename($_SERVER['PHP_SELF']) === 'settings.php') ? 'active' : '' ?>">
          <i class="fa-solid fa-gear"></i> הגדרות
        </a>
      </li>
      <?php endif; ?>
      <li class="mt-auto">
        <a href="<?= BASE_URL ?>/logout.php" class="logout-link">
          <i class="fa-solid fa-right-from-bracket"></i> יציאה
        </a>
      </li>
    </ul>
  </nav>

  <!-- Main content -->
  <div id="main-content">
    <header class="top-bar">
      <button class="btn btn-icon" id="sidebar-toggle" title="תפריט">
        <i class="fa-solid fa-bars"></i>
      </button>
      <div class="top-bar-title"><?= h($pageTitle ?? APP_TITLE) ?></div>
      <div class="top-bar-spacer"></div>
      <?php if (!empty($semesterOptions)): ?>
      <form method="get" class="d-flex align-items-center gap-2">
        <?php foreach ($_GET as $k => $v): if ($k === 'sem') continue; ?>
          <input type="hidden" name="<?= h($k) ?>" value="<?= h($v) ?>">
        <?php endforeach; ?>
        <label class="form-label mb-0 text-muted small">סמסטר:</label>
        <select name="sem" class="form-select form-select-sm sem-select" onchange="this.form.submit()">
          <option value="">כל הסמסטרים</option>
          <?php foreach ($semesterOptions as $opt): ?>
            <option value="<?= h($opt['year_sem']) ?>" <?= ($currentSem === $opt['year_sem']) ? 'selected' : '' ?>>
              <?= h($opt['year']) ?> סמסטר <?= h($opt['semester']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </form>
      <?php endif; ?>
    </header>

    <main class="page-body">
