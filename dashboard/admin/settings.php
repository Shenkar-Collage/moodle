<?php
require_once __DIR__ . '/../src/bootstrap.php';
Auth::requireAdmin();

$message     = '';
$messageType = 'success';

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['_csrf'] ?? '')) {
    $categoryId = (int)($_POST['category_id'] ?? 0);
    Settings::set('admin_category_id', $categoryId);
    $message = 'ההגדרות נשמרו בהצלחה.';
}

// Load current values
$currentCategoryId = (int)Settings::get('admin_category_id', 0);

// Build category list for dropdown
try {
    $categoriesFlat = MoodleData::getCategoriesFlat();
    $dbError        = false;
} catch (Exception $e) {
    $categoriesFlat = [];
    $dbError        = $e->getMessage();
}

// Find current category name
$currentCategoryName = '';
foreach ($categoriesFlat as $cat) {
    if ($cat['id'] === $currentCategoryId) {
        $currentCategoryName = $cat['name'];
        break;
    }
}

$pageTitle       = 'הגדרות מערכת';
$semesterOptions = [];
include __DIR__ . '/../views/layout_header.php';
?>
<script>var BASE_URL = '<?= BASE_URL ?>';</script>

<?php if ($message): ?>
  <div class="alert alert-<?= $messageType === 'danger' ? 'danger' : 'success' ?> alert-auto-hide d-flex align-items-center gap-2">
    <i class="fa-solid fa-circle-check"></i> <?= h($message) ?>
  </div>
<?php endif; ?>

<?php if ($dbError): ?>
  <div class="alert alert-warning">
    <i class="fa-solid fa-triangle-exclamation me-2"></i>
    <strong>שגיאה בטעינת קטגוריות:</strong> <?= h($dbError) ?>
    <br><small>בדוק את הגדרות config.php וחיבור למסד הנתונים.</small>
  </div>
<?php endif; ?>

<!-- Settings form card -->
<div class="content-card mb-4">
  <div class="card-header-bar">
    <h5><i class="fa-solid fa-gear text-primary me-2"></i>הגדרות מערכת — ברירת מחדל למנהל</h5>
  </div>
  <div class="p-4">
    <form method="POST">
      <input type="hidden" name="_csrf"  value="<?= h(csrf_token()) ?>">

      <div class="row g-4">
        <!-- Admin default category -->
        <div class="col-md-8">
          <label class="form-label fw-semibold">
            <i class="fa-solid fa-folder-tree me-1 text-primary"></i>
            קטגוריית Moodle ברירת מחדל (עבור מנהל)
          </label>
          <select name="category_id" class="form-select">
            <option value="0">— כל הקורסים (ללא סינון קטגוריה) —</option>
            <?php foreach ($categoriesFlat as $cat): ?>
              <option value="<?= (int)$cat['id'] ?>" <?= $cat['id'] === $currentCategoryId ? 'selected' : '' ?>>
                <?= h($cat['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <div class="form-text text-muted mt-1">
            כאשר מנהל המערכת נכנס ללוח הבקרה, יוצגו קורסים מקטגוריה זו ומתת-הקטגוריות שלה.
            בחירת "ללא סינון" תציג את כל הקורסים הפעילים.
          </div>
        </div>

        <!-- Current category display -->
        <div class="col-md-4">
          <label class="form-label fw-semibold">קטגוריה נוכחית</label>
          <div class="p-3 rounded-3 bg-light border" style="min-height:42px;">
            <?php if ($currentCategoryId > 0 && $currentCategoryName): ?>
              <i class="fa-solid fa-folder-open text-primary me-1"></i>
              <strong><?= h($currentCategoryName) ?></strong>
              <div class="text-muted small mt-1">מזהה: <?= $currentCategoryId ?></div>
            <?php elseif ($currentCategoryId > 0): ?>
              <span class="text-warning"><i class="fa-solid fa-triangle-exclamation me-1"></i>קטגוריה #<?= $currentCategoryId ?> (לא נמצאה)</span>
            <?php else: ?>
              <span class="text-muted"><i class="fa-solid fa-globe me-1"></i>כל הקורסים</span>
            <?php endif; ?>
          </div>
        </div>

        <div class="col-12">
          <button type="submit" class="btn-primary-custom">
            <i class="fa-solid fa-floppy-disk me-1"></i> שמור הגדרות
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Info box -->
<div class="content-card p-4" style="border-right:4px solid var(--primary-light, #6366f1);background:#f0f1ff;">
  <h6 class="fw-700 mb-2"><i class="fa-solid fa-circle-info text-primary me-2"></i>הסבר על הגדרות קטגוריה</h6>
  <ul class="mb-0 small text-muted">
    <li>הגדרת קטגוריה ברירת מחדל למנהל משפיעה רק על תצוגת לוח הבקרה של המנהל.</li>
    <li>ראשי חוג מוגדרים עם קטגוריה ספציפית דרך ניהול המשתמשים.</li>
    <li>הקטגוריה פועלת על כל תת-הקטגוריות שלה — ניתן לבחור קטגוריה עליונה לקבל את כל הקורסים שתחתיה.</li>
    <li>השינויים נשמרים בקובץ <code>data/settings.json</code> ונכנסים לתוקף מיידית.</li>
  </ul>
</div>

<?php include __DIR__ . '/../views/layout_footer.php'; ?>
