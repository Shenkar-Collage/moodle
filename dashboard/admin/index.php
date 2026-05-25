<?php
require_once __DIR__ . '/../src/bootstrap.php';
Auth::requireAdmin();

$users   = Auth::loadUsers();
$message = '';

// Load categories for dropdown
try {
    $categoriesFlat = MoodleData::getCategoriesFlat();
} catch (Exception $e) {
    $categoriesFlat = [];
}

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['_csrf'] ?? '')) {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete' && isset($_POST['uid'])) {
        $uid   = (int)$_POST['uid'];
        $users = array_filter($users, fn($u) => ($u['id'] ?? 0) !== $uid);
        Auth::saveUsers(array_values($users));
        $message = 'המשתמש נמחק בהצלחה.';

    } elseif ($action === 'toggle' && isset($_POST['uid'])) {
        $uid = (int)$_POST['uid'];
        foreach ($users as &$u) {
            if (($u['id'] ?? 0) === $uid) {
                $u['active'] = !$u['active'];
            }
        }
        unset($u);
        Auth::saveUsers($users);
        $message = 'סטטוס המשתמש עודכן.';

    } elseif ($action === 'add') {
        $username   = trim($_POST['username'] ?? '');
        $password   = $_POST['password'] ?? '';
        $name       = trim($_POST['name'] ?? '');
        $depts      = $_POST['depts'] ?? [];
        $categoryId = (int)($_POST['category_id'] ?? 0);

        if ($username && $password && $name) {
            // Check username unique
            $exists = array_filter($users, fn($u) => $u['username'] === $username);
            if ($exists) {
                $message = 'שם המשתמש כבר קיים במערכת.';
            } else {
                $maxId   = max(array_column($users, 'id') ?: [0]);
                $users[] = [
                    'id'              => $maxId + 1,
                    'username'        => $username,
                    'password_hash'   => password_hash($password, PASSWORD_BCRYPT),
                    'name'            => $name,
                    'department_codes'=> array_values(array_filter($depts)),
                    'category_id'     => $categoryId,
                    'active'          => true,
                ];
                try {
                    Auth::saveUsers($users);
                    $message = "המשתמש {$username} נוסף בהצלחה.";
                } catch (RuntimeException $e) {
                    $message = 'שגיאה: ' . $e->getMessage();
                    $users   = Auth::loadUsers();
                }
            }
        } else {
            $message = 'יש למלא את כל השדות.';
        }

    } elseif ($action === 'edit' && isset($_POST['uid'])) {
        $uid        = (int)$_POST['uid'];
        $name       = trim($_POST['name'] ?? '');
        $depts      = $_POST['depts'] ?? [];
        $categoryId = (int)($_POST['category_id'] ?? 0);
        if ($name) {
            foreach ($users as &$u) {
                if (($u['id'] ?? 0) === $uid) {
                    $u['name']              = $name;
                    $u['department_codes']  = array_values(array_filter($depts));
                    $u['category_id']       = $categoryId;
                }
            }
            unset($u);
            try {
                Auth::saveUsers($users);
                $message = 'פרטי המשתמש עודכנו בהצלחה.';
            } catch (RuntimeException $e) {
                $message = 'שגיאה: ' . $e->getMessage();
            }
        } else {
            $message = 'שם לא יכול להיות ריק.';
        }

    } elseif ($action === 'change_password' && isset($_POST['uid'])) {
        $uid     = (int)$_POST['uid'];
        $newpass = $_POST['new_password'] ?? '';
        if (strlen($newpass) >= 6) {
            foreach ($users as &$u) {
                if (($u['id'] ?? 0) === $uid) {
                    $u['password_hash'] = password_hash($newpass, PASSWORD_BCRYPT);
                }
            }
            unset($u);
            Auth::saveUsers($users);
            $message = 'הסיסמה עודכנה בהצלחה.';
        } else {
            $message = 'הסיסמה חייבת להכיל לפחות 6 תווים.';
        }
    }

    $users = Auth::loadUsers(); // reload
}

$pageTitle       = 'ניהול משתמשים';
$semesterOptions = [];
include __DIR__ . '/../views/layout_header.php';
?>
<script>var BASE_URL = '<?= BASE_URL ?>';</script>

<?php if ($message): ?>
  <div class="alert alert-success alert-auto-hide d-flex align-items-center gap-2">
    <i class="fa-solid fa-circle-check"></i> <?= h($message) ?>
  </div>
<?php endif; ?>

<!-- ── Add user card ── -->
<div class="content-card mb-4">
  <div class="card-header-bar">
    <h5><i class="fa-solid fa-user-plus text-primary me-2"></i>הוספת ראש חוג חדש</h5>
    <button class="btn-sm-ghost" type="button" data-bs-toggle="collapse" data-bs-target="#addUserForm">
      <i class="fa-solid fa-chevron-down"></i> הצג/הסתר
    </button>
  </div>
  <div class="collapse" id="addUserForm">
    <div class="p-4">
      <form method="POST">
        <input type="hidden" name="_csrf"   value="<?= h(csrf_token()) ?>">
        <input type="hidden" name="action"  value="add">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label fw-semibold">שם משתמש <span class="text-danger">*</span></label>
            <input type="text" name="username" class="form-control" placeholder="לדוגמה: head_cs" required>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold">סיסמה <span class="text-danger">*</span></label>
            <input type="password" name="password" class="form-control" placeholder="לפחות 6 תווים" required minlength="6">
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold">שם מלא <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" placeholder="שם ראש החוג" required>
          </div>

          <!-- Moodle category -->
          <div class="col-12">
            <label class="form-label fw-semibold">
              <i class="fa-solid fa-folder-tree me-1 text-primary"></i>קטגוריית Moodle
            </label>
            <select name="category_id" class="form-select">
              <option value="0">— ללא קטגוריה (השתמש בקודי מחלקה בלבד) —</option>
              <?php foreach ($categoriesFlat as $cat): ?>
                <option value="<?= (int)$cat['id'] ?>"><?= h($cat['name']) ?></option>
              <?php endforeach; ?>
            </select>
            <div class="form-text text-muted">אם תבחר קטגוריה, לוח הבקרה של ראש החוג יסנן לפי קטגוריה זו ותת-הקטגוריות שלה.</div>
          </div>

          <div class="col-12">
            <label class="form-label fw-semibold">מחלקות (לפי קידוד shortname)</label>
            <div class="row g-2">
              <?php foreach (DEPARTMENTS as $code => $name): ?>
              <div class="col-6 col-md-4 col-lg-3">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="depts[]"
                         value="<?= h($code) ?>" id="dept_<?= h($code) ?>">
                  <label class="form-check-label small" for="dept_<?= h($code) ?>">
                    <strong><?= h($code) ?></strong> — <?= h($name) ?>
                  </label>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="col-12">
            <button type="submit" class="btn-primary-custom">
              <i class="fa-solid fa-plus"></i> הוסף משתמש
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ── Users list ── -->
<div class="content-card">
  <div class="card-header-bar">
    <h5><i class="fa-solid fa-users text-primary me-2"></i>ראשי חוג
      <span class="badge bg-light text-dark border ms-2"><?= count($users) ?></span>
    </h5>
  </div>
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead>
        <tr>
          <th>שם מלא</th>
          <th>שם משתמש</th>
          <th>מחלקות</th>
          <th>קטגוריה</th>
          <th class="text-center">סטטוס</th>
          <th class="text-center">פעולות</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($users as $u): ?>
        <tr>
          <td><strong><?= h($u['name']) ?></strong></td>
          <td><code><?= h($u['username']) ?></code></td>
          <td>
            <?php foreach ($u['department_codes'] ?? [] as $dc): ?>
              <span class="dept-tag"><?= h($dc) ?> — <?= h(DEPARTMENTS[$dc] ?? $dc) ?></span>
            <?php endforeach; ?>
            <?php if (empty($u['department_codes'])): ?>
              <span class="text-muted small">—</span>
            <?php endif; ?>
          </td>
          <td>
            <?php
            $uCatId   = (int)($u['category_id'] ?? 0);
            $uCatName = '';
            if ($uCatId > 0) {
                foreach ($categoriesFlat as $cat) {
                    if ($cat['id'] === $uCatId) { $uCatName = $cat['name']; break; }
                }
            }
            ?>
            <?php if ($uCatId > 0): ?>
              <span class="small"><i class="fa-solid fa-folder-open text-primary me-1"></i><?= h($uCatName ?: '#' . $uCatId) ?></span>
            <?php else: ?>
              <span class="text-muted small">—</span>
            <?php endif; ?>
          </td>
          <td class="text-center">
            <?php if ($u['active'] ?? false): ?>
              <span class="status-badge status-active">פעיל</span>
            <?php else: ?>
              <span class="status-badge status-inactive">מושבת</span>
            <?php endif; ?>
          </td>
          <td class="text-center">
            <div class="d-flex gap-1 justify-content-center">
              <!-- Edit -->
              <button class="btn-sm-ghost" title="עריכה"
                      data-bs-toggle="modal" data-bs-target="#editModal<?= (int)($u['id'] ?? 0) ?>">
                <i class="fa-solid fa-pen-to-square"></i>
              </button>
              <!-- Toggle active -->
              <form method="POST" class="d-inline">
                <input type="hidden" name="_csrf"   value="<?= h(csrf_token()) ?>">
                <input type="hidden" name="action"  value="toggle">
                <input type="hidden" name="uid"     value="<?= (int)($u['id'] ?? 0) ?>">
                <button type="submit" class="btn-sm-ghost" title="<?= $u['active'] ? 'השבת' : 'הפעל' ?>">
                  <i class="fa-solid <?= $u['active'] ? 'fa-ban' : 'fa-circle-play' ?>"></i>
                </button>
              </form>
              <!-- Change password -->
              <button class="btn-sm-ghost" title="שינוי סיסמה"
                      data-bs-toggle="modal" data-bs-target="#pwModal<?= (int)($u['id'] ?? 0) ?>">
                <i class="fa-solid fa-key"></i>
              </button>
              <!-- Delete -->
              <form method="POST" class="d-inline"
                    onsubmit="return confirm('האם למחוק את המשתמש <?= h(addslashes($u['name'])) ?>?')">
                <input type="hidden" name="_csrf"  value="<?= h(csrf_token()) ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="uid"    value="<?= (int)($u['id'] ?? 0) ?>">
                <button type="submit" class="btn-sm-ghost" style="color:var(--danger);" title="מחיקה">
                  <i class="fa-solid fa-trash"></i>
                </button>
              </form>
            </div>
          </td>
        </tr>

        <!-- Edit modal for this user -->
        <div class="modal fade" id="editModal<?= (int)($u['id'] ?? 0) ?>" tabindex="-1">
          <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h6 class="modal-title"><i class="fa-solid fa-pen-to-square me-2"></i>עריכת משתמש — <?= h($u['name']) ?></h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <form method="POST">
                <div class="modal-body">
                  <input type="hidden" name="_csrf"  value="<?= h(csrf_token()) ?>">
                  <input type="hidden" name="action" value="edit">
                  <input type="hidden" name="uid"    value="<?= (int)($u['id'] ?? 0) ?>">
                  <div class="mb-3">
                    <label class="form-label fw-semibold">שם מלא</label>
                    <input type="text" name="name" class="form-control" value="<?= h($u['name']) ?>" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label fw-semibold"><i class="fa-solid fa-folder-tree me-1 text-primary"></i>קטגוריית Moodle</label>
                    <select name="category_id" class="form-select">
                      <option value="0">— ללא קטגוריה —</option>
                      <?php foreach ($categoriesFlat as $cat): ?>
                        <option value="<?= (int)$cat['id'] ?>" <?= (int)($u['category_id'] ?? 0) === $cat['id'] ? 'selected' : '' ?>>
                          <?= h($cat['name']) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="mb-1">
                    <label class="form-label fw-semibold">מחלקות</label>
                    <div class="row g-2">
                      <?php foreach (DEPARTMENTS as $code => $dname): ?>
                      <div class="col-6 col-md-4">
                        <div class="form-check">
                          <input class="form-check-input" type="checkbox" name="depts[]"
                                 value="<?= h($code) ?>"
                                 id="ed_<?= (int)($u['id'] ?? 0) ?>_<?= h($code) ?>"
                                 <?= in_array($code, $u['department_codes'] ?? []) ? 'checked' : '' ?>>
                          <label class="form-check-label small" for="ed_<?= (int)($u['id'] ?? 0) ?>_<?= h($code) ?>">
                            <strong><?= h($code) ?></strong> — <?= h($dname) ?>
                          </label>
                        </div>
                      </div>
                      <?php endforeach; ?>
                    </div>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">ביטול</button>
                  <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-floppy-disk me-1"></i>שמור</button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- Password modal for this user -->
        <div class="modal fade" id="pwModal<?= (int)($u['id'] ?? 0) ?>" tabindex="-1">
          <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
              <div class="modal-header"><h6 class="modal-title">שינוי סיסמה — <?= h($u['name']) ?></h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <form method="POST">
                <div class="modal-body">
                  <input type="hidden" name="_csrf"        value="<?= h(csrf_token()) ?>">
                  <input type="hidden" name="action"       value="change_password">
                  <input type="hidden" name="uid"          value="<?= (int)($u['id'] ?? 0) ?>">
                  <label class="form-label">סיסמה חדשה</label>
                  <input type="password" name="new_password" class="form-control" minlength="6" required>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">ביטול</button>
                  <button type="submit" class="btn btn-primary btn-sm">שמור</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      <?php endforeach; ?>

      <?php if (empty($users)): ?>
        <tr><td colspan="6" class="text-center py-5 text-muted">
          <i class="fa-solid fa-users-slash fa-2x mb-2 d-block"></i>
          אין ראשי חוג רשומים עדיין
        </td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Admin tip box -->
<div class="content-card mt-4 p-4" style="border-right:4px solid var(--warning);background:#fffbeb;">
  <h6 class="fw-700 mb-2"><i class="fa-solid fa-circle-info text-warning me-2"></i>מידע למנהל</h6>
  <ul class="mb-0 small text-muted">
    <li>ראשי חוג יוכלו לראות רק את המחלקות / הקטגוריות שהוגדרו להם.</li>
    <li>ניתן להצמיד ראש חוג למספר מחלקות בו-זמנית, וגם לקטגוריית Moodle.</li>
    <li>אם מוגדרת קטגוריית Moodle — הסינון יהיה לפי הקטגוריה (כולל תת-קטגוריות), ללא תלות בקודי המחלקה.</li>
    <li>לשינוי סיסמת האדמין — ערוך את <code>config.php</code> ועדכן את <code>password_hash</code>.</li>
    <li>כדי לייצר hash חדש: <code>php -r "echo password_hash('PASSWORD', PASSWORD_BCRYPT);"</code></li>
  </ul>
</div>

<?php include __DIR__ . '/../views/layout_footer.php'; ?>
