<?php
require_once __DIR__ . '/src/bootstrap.php';
Auth::requireLogin();

$courseId  = (int)($_GET['course_id'] ?? 0);
$currentSem = $_GET['sem'] ?? '';

if ($courseId <= 0) {
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}

// Verify permission
$deptCode = '';
try {
    $course = MoodleData::getCourse($courseId);
    if (!$course) {
        header('Location: ' . BASE_URL . '/dashboard.php');
        exit;
    }
    $deptCode = get_dept_code_from_shortname($course['shortname']);
    if (!Auth::canViewDepartment($deptCode)) {
        header('Location: ' . BASE_URL . '/dashboard.php');
        exit;
    }
    $students = MoodleData::getCourseStudents($courseId);
    $dbError  = false;
} catch (Exception $e) {
    $students = [];
    $dbError  = $e->getMessage();
}

// Counts for header
$total  = count($students);
$active = count(array_filter($students, fn($s) => $s['days_since_access'] !== null && (int)$s['days_since_access'] <= 30));
$warn   = count(array_filter($students, fn($s) => $s['days_since_access'] !== null && (int)$s['days_since_access'] > 30 && (int)$s['days_since_access'] <= 90));
$never  = count(array_filter($students, fn($s) => $s['days_since_access'] === null));
$inactive = count(array_filter($students, fn($s) => $s['days_since_access'] !== null && (int)$s['days_since_access'] > 90));

$pageTitle = 'תלמידים — ' . ($course['fullname'] ?? '');
$semesterOptions = MoodleData::getAvailableSemesters();
include __DIR__ . '/views/layout_header.php';
?>

<script>var BASE_URL = '<?= BASE_URL ?>';</script>

<!-- Breadcrumb -->
<nav style="font-size:13px;color:var(--muted);margin-bottom:20px;">
  <a href="<?= BASE_URL ?>/dashboard.php?sem=<?= urlencode($currentSem) ?>" style="color:var(--primary);text-decoration:none;">
    <i class="fa-solid fa-arrow-right"></i> חזרה ללוח בקרה
  </a>
  <span class="mx-2">/</span>
  <span><?= h($course['fullname'] ?? '') ?></span>
</nav>

<?php if ($dbError): ?>
  <div class="alert alert-danger"><?= h($dbError) ?></div>
<?php else: ?>

<!-- Course info banner -->
<div class="content-card mb-4 p-4" style="border-right:4px solid var(--primary);">
  <div class="row align-items-center">
    <div class="col">
      <h4 class="mb-1 fw-700"><?= h($course['fullname'] ?? '') ?></h4>
      <div class="text-muted" style="font-size:13px;">
        <span class="me-3"><i class="fa-solid fa-tag me-1"></i><?= h($course['shortname'] ?? '') ?></span>
        <span><i class="fa-solid fa-building me-1"></i><?= h(DEPARTMENTS[$deptCode] ?? $deptCode) ?></span>
      </div>
    </div>
    <div class="col-auto d-flex gap-2">
      <button onclick="exportCsv()" class="btn-outline-custom">
        <i class="fa-solid fa-file-csv"></i> ייצוא CSV
      </button>
      <button onclick="window.print()" class="btn-sm-ghost">
        <i class="fa-solid fa-print"></i> הדפסה
      </button>
    </div>
  </div>
</div>

<!-- Mini stats row -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-icon blue"><i class="fa-solid fa-users"></i></div>
      <div><div class="stat-value"><?= $total ?></div><div class="stat-label">סה"כ תלמידים</div></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-icon green"><i class="fa-solid fa-circle-check"></i></div>
      <div><div class="stat-value"><?= $active ?></div><div class="stat-label">פעילים (0–30 ימים)</div></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-icon yellow"><i class="fa-solid fa-clock"></i></div>
      <div><div class="stat-value"><?= $warn ?></div><div class="stat-label">דורשים מעקב (31–90)</div></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-icon red"><i class="fa-solid fa-circle-xmark"></i></div>
      <div><div class="stat-value"><?= $never + $inactive ?></div><div class="stat-label">לא פעילים / לא נכנסו</div></div>
    </div>
  </div>
</div>

<!-- Filter tabs -->
<div class="d-flex gap-2 mb-3 flex-wrap">
  <button class="btn btn-sm btn-outline-secondary active filter-btn" data-filter="all">הכל (<?= $total ?>)</button>
  <button class="btn btn-sm filter-btn" data-filter="active" style="background:#d1fae5;color:#065f46;border-color:#a7f3d0;">פעילים (<?= $active ?>)</button>
  <button class="btn btn-sm filter-btn" data-filter="warning" style="background:#fef3c7;color:#92400e;border-color:#fde68a;">דורש מעקב (<?= $warn ?>)</button>
  <button class="btn btn-sm filter-btn" data-filter="inactive" style="background:#fee2e2;color:#991b1b;border-color:#fca5a5;">לא פעיל (<?= $inactive ?>)</button>
  <button class="btn btn-sm filter-btn" data-filter="never" style="background:#f1f5f9;color:#475569;border-color:#cbd5e1;">לא נכנסו (<?= $never ?>)</button>
</div>

<!-- Students table -->
<div class="content-card">
  <div class="table-responsive">
    <table class="data-table table table-hover mb-0" id="students-table">
      <thead>
        <tr>
          <th>#</th>
          <th>שם פרטי</th>
          <th>שם משפחה</th>
          <th>ימים מאז כניסה אחרונה</th>
          <th>זמן שימוש טוטאלי</th>
          <th>סטטוס פעילות</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($students as $i => $s):
        $days   = ($s['days_since_access'] !== null) ? (int)$s['days_since_access'] : null;
        $status = get_activity_status($days);
        $mins   = (float)($s['total_minutes'] ?? 0);

        // Determine row data-filter value
        if ($days === null) $filterVal = 'never';
        elseif ($days <= 30) $filterVal = 'active';
        elseif ($days <= 90) $filterVal = 'warning';
        else $filterVal = 'inactive';

        // Days color class
        $daysClass = match(true) {
            $days === null => 'days-bad',
            $days <= 30    => 'days-ok',
            $days <= 90    => 'days-warn',
            default        => 'days-bad',
        };
      ?>
        <tr data-filter="<?= $filterVal ?>">
          <td class="text-muted"><?= $i + 1 ?></td>
          <td><?= h($s['firstname']) ?></td>
          <td><?= h($s['lastname']) ?></td>
          <td>
            <span class="days-value <?= $daysClass ?>">
              <?= $days !== null ? $days . ' ימים' : 'מעולם לא נכנס/ה' ?>
            </span>
          </td>
          <td><strong><?= h(format_minutes($mins)) ?></strong></td>
          <td>
            <span class="status-badge <?= h($status['class']) ?>">
              <?= h($status['label']) ?>
            </span>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($students)): ?>
        <tr><td colspan="6" class="text-center py-5 text-muted">
          <i class="fa-solid fa-users-slash fa-2x mb-2 d-block"></i>
          אין תלמידים רשומים לקורס זה
        </td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php endif; ?>

<?php
$extraJs = <<<'JS'
<script>
// Filter tabs
$('.filter-btn').on('click', function () {
  $('.filter-btn').removeClass('active btn-outline-secondary').addClass('btn-outline-light');
  $(this).addClass('active btn-outline-secondary');
  var f = $(this).data('filter');
  var table = $('#students-table').DataTable();
  table.rows().every(function () {
    var node = $(this.node());
    if (f === 'all' || node.data('filter') === f) {
      node.show();
    } else {
      node.hide();
    }
  });
  table.draw();
});

// CSV export
function exportCsv() {
  var rows = [['שם פרטי','שם משפחה','ימים מאז כניסה','זמן שימוש (דקות)','סטטוס']];
  $('#students-table tbody tr').each(function () {
    var tds = $(this).find('td');
    if (tds.length < 6) return;
    rows.push([
      tds.eq(1).text().trim(),
      tds.eq(2).text().trim(),
      tds.eq(3).text().trim(),
      tds.eq(4).text().trim(),
      tds.eq(5).text().trim(),
    ]);
  });
  var csv = '﻿' + rows.map(r => r.map(c => '"' + c.replace(/"/g,'""') + '"').join(',')).join('\n');
  var a   = document.createElement('a');
  a.href  = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);
  a.download = 'students.csv';
  a.click();
}
</script>
JS;
include __DIR__ . '/views/layout_footer.php';
