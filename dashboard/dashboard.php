<?php
require_once __DIR__ . '/src/bootstrap.php';
Auth::requireLogin();

$user        = Auth::getUser();
$isAdmin     = Auth::isAdmin();
$deptCodes   = $isAdmin ? [] : $user['departments'];

// Semester filter
$semesterOptions = MoodleData::getAvailableSemesters();
$currentSem      = $_GET['sem'] ?? ($semesterOptions[0]['year_sem'] ?? '');

// Load course stats
try {
    $courses = MoodleData::getCourseStats($deptCodes, $currentSem);
    $dbError = false;
} catch (Exception $e) {
    $courses = [];
    $dbError = $e->getMessage();
}

// Department label for heading
if (!$isAdmin && count($deptCodes) === 1) {
    $deptLabel = DEPARTMENTS[$deptCodes[0]] ?? 'המחלקה שלי';
} elseif (!$isAdmin) {
    $deptLabel = 'המחלקות שלי';
} else {
    $deptLabel = 'כל המחלקות';
}

// Aggregate stats
$totalStudents = array_sum(array_column($courses, 'total_students'));
$totalActive   = array_sum(array_column($courses, 'active_30d'));
$totalWarn     = array_sum(array_column($courses, 'inactive_30d'));
$totalNever    = array_sum(array_column($courses, 'never_accessed'));

// Pie chart data (active vs inactive vs never)
$pieData = [
    'labels' => ['פעילים (30 יום)', 'לא פעילים (30+ ימים)', 'מעולם לא נכנסו'],
    'values' => [$totalActive, $totalWarn, $totalNever],
    'colors' => ['#10b981', '#f59e0b', '#ef4444'],
];

$pageTitle = $deptLabel . ' — לוח בקרה';
include __DIR__ . '/views/layout_header.php';
?>

<script>var BASE_URL = '<?= BASE_URL ?>';</script>

<?php if ($dbError): ?>
  <div class="alert alert-danger">
    <strong>שגיאת חיבור למסד הנתונים:</strong> <?= h($dbError) ?>
    <br><small>בדוק את הגדרות config.php</small>
  </div>
<?php else: ?>

<!-- ── Summary stat cards ── -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-icon blue"><i class="fa-solid fa-book-open"></i></div>
      <div>
        <div class="stat-value"><?= count($courses) ?></div>
        <div class="stat-label">קורסים פעילים</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-icon blue"><i class="fa-solid fa-users"></i></div>
      <div>
        <div class="stat-value"><?= number_format($totalStudents) ?></div>
        <div class="stat-label">סה"כ תלמידים</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-icon green"><i class="fa-solid fa-circle-check"></i></div>
      <div>
        <div class="stat-value"><?= number_format($totalActive) ?></div>
        <div class="stat-label">פעילים (30 יום אחרון)</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-icon red"><i class="fa-solid fa-circle-xmark"></i></div>
      <div>
        <div class="stat-value"><?= number_format($totalNever) ?></div>
        <div class="stat-label">מעולם לא נכנסו</div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4 mb-4">
  <!-- ── Pie chart ── -->
  <div class="col-lg-4">
    <div class="content-card h-100">
      <div class="card-header-bar">
        <h5><i class="fa-solid fa-chart-pie text-primary me-2"></i>פעילות תלמידים</h5>
        <?php if ($currentSem): ?>
          <span class="badge bg-light text-dark border"><?= h(str_replace('_', ' סמסטר ', $currentSem)) ?></span>
        <?php endif; ?>
      </div>
      <div class="p-4">
        <div class="chart-wrapper">
          <canvas id="activityPie"></canvas>
        </div>
        <div class="mt-3">
          <?php
          $pieLabels = $pieData['labels'];
          $pieColors = $pieData['colors'];
          $pieVals   = $pieData['values'];
          $pieTotal  = array_sum($pieVals) ?: 1;
          foreach ($pieLabels as $i => $lbl): ?>
          <div class="d-flex justify-content-between align-items-center mb-1">
            <div class="d-flex align-items-center gap-2">
              <span style="width:10px;height:10px;border-radius:50%;background:<?= $pieColors[$i] ?>;display:inline-block;"></span>
              <span style="font-size:12px;"><?= h($lbl) ?></span>
            </div>
            <span style="font-size:12px;font-weight:600;"><?= number_format($pieVals[$i]) ?>
              <span class="text-muted">(<?= round($pieVals[$i]/$pieTotal*100) ?>%)</span>
            </span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- ── Insights panel ── -->
  <div class="col-lg-8">
    <div class="content-card h-100">
      <div class="card-header-bar">
        <h5><i class="fa-solid fa-lightbulb text-warning me-2"></i>תובנות ודגשים</h5>
      </div>
      <div class="p-4">
        <?php
        // Smart insights
        $insights = [];
        $pctActive = $totalStudents ? round($totalActive / $totalStudents * 100) : 0;
        $pctNever  = $totalStudents ? round($totalNever  / $totalStudents * 100) : 0;

        if ($pctNever > 30) {
            $insights[] = ['danger', 'fa-triangle-exclamation', "{$pctNever}% מהתלמידים לא נכנסו לקורס כלל — כדאי לפנות אליהם אישית."];
        }
        if ($pctActive >= 70) {
            $insights[] = ['success', 'fa-trophy', "שיעור פעילות גבוה! {$pctActive}% מהתלמידים נכנסו ב-30 הימים האחרונים."];
        }
        // Courses with zero activity
        $zeroCourses = array_filter($courses, fn($c) => (int)$c['active_30d'] === 0 && (int)$c['total_students'] > 0);
        if (count($zeroCourses) > 0) {
            $insights[] = ['warning', 'fa-ghost', count($zeroCourses) . ' קורסים ללא אף תלמיד פעיל בחודש האחרון.'];
        }
        // Courses with no components
        $noComp = array_filter($courses, fn($c) => (int)$c['component_count'] === 0);
        if (count($noComp) > 0) {
            $insights[] = ['secondary', 'fa-box-open', count($noComp) . ' קורסים ריקים (ללא תכנים) — ייתכן שצריכים עדכון.'];
        }
        // Best performing course
        if (!empty($courses)) {
            usort($courses, fn($a,$b) => (int)$b['active_30d'] - (int)$a['active_30d']);
            $best = $courses[0];
            if ((int)$best['active_30d'] > 0) {
                $pct = $best['total_students'] ? round($best['active_30d']/$best['total_students']*100) : 0;
                $insights[] = ['info', 'fa-star', "הקורס הפעיל ביותר: <strong>" . h($best['fullname']) . "</strong> — {$pct}% פעילות."];
            }
            // Restore original order
            usort($courses, fn($a,$b) => strcmp($a['fullname'], $b['fullname']));
        }

        if (empty($insights)) {
            $insights[] = ['secondary', 'fa-circle-info', 'אין נתונים מספיקים להצגת תובנות.'];
        }
        ?>
        <div class="d-flex flex-column gap-3">
        <?php foreach ($insights as [$type, $icon, $text]): ?>
          <div class="d-flex align-items-start gap-3 p-3 rounded-3 bg-<?= $type === 'secondary' ? 'light' : $type . '-subtle' ?>">
            <i class="fa-solid <?= $icon ?> text-<?= $type ?> mt-1" style="font-size:18px;flex-shrink:0;"></i>
            <p class="mb-0" style="font-size:13px;line-height:1.5;"><?= $text ?></p>
          </div>
        <?php endforeach; ?>
        </div>

        <!-- Quick stats bar -->
        <div class="mt-4 pt-3 border-top">
          <div class="row text-center g-3">
            <div class="col-4">
              <div style="font-size:22px;font-weight:700;color:var(--primary);"><?= $pctActive ?>%</div>
              <div style="font-size:11px;color:var(--muted);">שיעור פעילות</div>
            </div>
            <div class="col-4">
              <div style="font-size:22px;font-weight:700;color:var(--warning);"><?= $totalWarn ?></div>
              <div style="font-size:11px;color:var(--muted);">דורשים מעקב</div>
            </div>
            <div class="col-4">
              <div style="font-size:22px;font-weight:700;color:var(--success);"><?= count($courses) ?></div>
              <div style="font-size:11px;color:var(--muted);">קורסים</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ── Courses table ── -->
<div class="content-card">
  <div class="card-header-bar">
    <h5><i class="fa-solid fa-list-check text-primary me-2"></i>רשימת קורסים
      <span class="badge bg-light text-dark border ms-2"><?= count($courses) ?></span>
    </h5>
    <div class="d-flex gap-2">
      <button onclick="window.print()" class="btn-sm-ghost">
        <i class="fa-solid fa-print"></i> הדפסה
      </button>
    </div>
  </div>
  <div class="table-responsive">
    <table class="data-table table table-hover mb-0" id="courses-table">
      <thead>
        <tr>
          <th>שם קורס</th>
          <?php if ($isAdmin): ?><th>מחלקה</th><?php endif; ?>
          <th class="text-center">סה"כ תלמידים</th>
          <th class="text-center">פעילים<br><small class="fw-normal text-muted">30 ימים</small></th>
          <th class="text-center">לא פעילים<br><small class="fw-normal text-muted">30+ ימים</small></th>
          <th class="text-center">מעולם<br><small class="fw-normal text-muted">לא נכנסו</small></th>
          <th class="text-center">רכיבים</th>
          <th class="text-center">פעולות</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($courses as $c):
        $total  = (int)$c['total_students'];
        $active = (int)$c['active_30d'];
        $warn   = (int)$c['inactive_30d'];
        $never  = (int)$c['never_accessed'];
        $pct    = $total ? round($active / $total * 100) : 0;
        $pctBar = min($pct, 100);
      ?>
        <tr>
          <td>
            <div style="font-weight:600;font-size:13px;"><?= h($c['fullname']) ?></div>
            <div style="font-size:11px;color:var(--muted);"><?= h($c['shortname']) ?></div>
          </td>
          <?php if ($isAdmin): ?>
          <td><span class="dept-tag"><?= h(DEPARTMENTS[$c['dept_code']] ?? $c['dept_code']) ?></span></td>
          <?php endif; ?>
          <td class="text-center">
            <strong><?= $total ?></strong>
          </td>
          <td class="text-center">
            <div><?= $active ?> <span class="text-muted small">(<?= $pct ?>%)</span></div>
            <div class="mini-progress mt-1" style="width:60px;margin:0 auto;">
              <div class="mini-progress-bar" style="width:<?= $pctBar ?>%;background:var(--success);"></div>
            </div>
          </td>
          <td class="text-center">
            <span class="<?= $warn > 0 ? 'text-warning fw-bold' : 'text-muted' ?>"><?= $warn ?></span>
          </td>
          <td class="text-center">
            <span class="<?= $never > 0 ? 'text-danger fw-bold' : 'text-muted' ?>"><?= $never ?></span>
          </td>
          <td class="text-center">
            <button class="comp-badge"
                    data-course-id="<?= (int)$c['id'] ?>"
                    data-course-name="<?= h($c['fullname']) ?>">
              <i class="fa-solid fa-puzzle-piece"></i>
              <?= (int)$c['component_count'] ?>
            </button>
          </td>
          <td class="text-center">
            <a href="<?= BASE_URL ?>/students.php?course_id=<?= (int)$c['id'] ?>&sem=<?= urlencode($currentSem) ?>"
               class="btn-primary-custom" style="font-size:12px;padding:5px 10px;">
              <i class="fa-solid fa-users"></i> הצגת תלמידים
            </a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($courses)): ?>
        <tr><td colspan="<?= $isAdmin ? 8 : 7 ?>" class="text-center py-5 text-muted">
          <i class="fa-solid fa-inbox fa-2x mb-2 d-block"></i>
          לא נמצאו קורסים<?= $currentSem ? ' לסמסטר זה' : '' ?>
        </td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ── Components Modal ── -->
<div class="modal fade" id="componentsModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="componentsModalLabel">רכיבים בקורס</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <ul class="component-list" id="components-list"></ul>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">סגור</button>
      </div>
    </div>
  </div>
</div>

<?php endif; // dbError ?>

<?php
$extraJs = <<<JS
<script>
(function(){
  var ctx = document.getElementById('activityPie');
  if (!ctx) return;
  new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: <?= json_encode($pieData['labels'], JSON_UNESCAPED_UNICODE) ?>,
      datasets: [{
        data:            <?= json_encode($pieData['values']) ?>,
        backgroundColor: <?= json_encode($pieData['colors']) ?>,
        borderWidth: 2,
        borderColor: '#fff',
        hoverOffset: 6,
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      cutout: '65%',
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: function(ctx) {
              var total = ctx.dataset.data.reduce((a,b)=>a+b,0) || 1;
              return ' ' + ctx.label + ': ' + ctx.parsed + ' (' + Math.round(ctx.parsed/total*100) + '%)';
            }
          }
        }
      }
    }
  });
})();
</script>
JS;
include __DIR__ . '/views/layout_footer.php';
