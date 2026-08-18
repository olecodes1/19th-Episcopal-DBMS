<?php
require_once '../db.php';
require_once '../includes/feature_tables.php';

ensure_feature_tables($pdo);

$type = $_GET['type'] ?? '';
$category = trim($_GET['category'] ?? '');
$tag = trim($_GET['tag'] ?? '');
$eventTag = trim($_GET['event_tag'] ?? '');
$personTag = trim($_GET['person_tag'] ?? '');
$year = (int)($_GET['year'] ?? 0) ?: null;

$sql = "SELECT * FROM media_items WHERE 1=1";
$params = [];
if (column_exists($pdo, 'media_items', 'deleted_at')) {
    $sql .= " AND deleted_at IS NULL";
}
if (in_array($type, ['image', 'video', 'audio'], true)) {
    $sql .= " AND media_type = ?";
    $params[] = $type;
}
if ($category !== '') {
    $sql .= " AND category = ?";
    $params[] = $category;
}
if ($tag !== '' && column_exists($pdo, 'media_items', 'tags')) {
    $sql .= " AND tags LIKE ?";
    $params[] = '%' . $tag . '%';
}
if ($eventTag !== '' && column_exists($pdo, 'media_items', 'event_tag')) {
    $sql .= " AND event_tag LIKE ?";
    $params[] = '%' . $eventTag . '%';
}
if ($personTag !== '' && column_exists($pdo, 'media_items', 'person_tag')) {
    $sql .= " AND person_tag LIKE ?";
    $params[] = '%' . $personTag . '%';
}
if ($year !== null && column_exists($pdo, 'media_items', 'media_year')) {
    $sql .= " AND media_year = ?";
    $params[] = $year;
}
$sql .= " ORDER BY uploaded_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$media = $stmt->fetchAll();

$leaders = $pdo->query("SELECT * FROM legacy_leaders ORDER BY COALESCE(start_year, 9999), full_name")->fetchAll();
$milestones = $pdo->query("SELECT * FROM milestones ORDER BY milestone_year")->fetchAll();
$foundedYear = $pdo->query("SELECT MIN(start_year) FROM legacy_leaders")->fetchColumn();
$districtAge = ($foundedYear && (int)$foundedYear > 0) ? ((int)date('Y') - (int)$foundedYear) : null;
$categories = $pdo->query("SELECT DISTINCT category FROM media_items WHERE category IS NOT NULL AND category <> '' ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
$timeline = [];
foreach ($leaders as $l) {
    $timeline[] = ['year' => (int)($l['start_year'] ?? 0), 'type' => 'Leader', 'title' => $l['full_name'], 'meta' => $l['role_type'], 'text' => $l['descriptions'] ?? $l['achievements']];
}
foreach ($milestones as $m) {
    $timeline[] = ['year' => (int)($m['milestone_year'] ?? 0), 'type' => 'Milestone', 'title' => $m['title'], 'meta' => 'District', 'text' => $m['descriptions'] ?? $m['achievements']];
}
usort($timeline, fn($a, $b) => $a['year'] <=> $b['year']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Media & History — 19th Episcopal District</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="container-fluid mt-4 px-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold text-success mb-0"><i class="fas fa-photo-video me-2"></i>Multimedia, History & Insights</h5>
    <a href="../forms/add_media.php" class="btn btn-success btn-sm"><i class="fas fa-upload me-1"></i>Upload Media</a>
  </div>

  <?php if (isset($_GET['uploaded'])): ?><div class="alert alert-success alert-dismissible py-2">Media uploaded.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
  <?php if (isset($_GET['leader_added'])): ?><div class="alert alert-success alert-dismissible py-2">Legacy leader added.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
  <?php if (isset($_GET['milestone_added'])): ?><div class="alert alert-success alert-dismissible py-2">Milestone added.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
  <?php if (isset($_GET['deleted']) && isset($_GET['deleted_item_id'])): ?><div class="alert alert-warning py-2">Item deleted. <a href="../actions/restore_deleted.php?id=<?= (int)$_GET['deleted_item_id'] ?>">Undo</a></div><?php endif; ?>
  <?php if (isset($_GET['error'])): ?><div class="alert alert-danger alert-dismissible py-2"><?= htmlspecialchars($_GET['error']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

  <div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card shadow-sm"><div class="card-body"><div class="small text-muted">Media Items</div><div class="fs-4 fw-bold"><?= count($media) ?></div></div></div></div>
    <div class="col-md-3"><div class="card shadow-sm"><div class="card-body"><div class="small text-muted">Legacy Leaders</div><div class="fs-4 fw-bold"><?= count($leaders) ?></div></div></div></div>
    <div class="col-md-3"><div class="card shadow-sm"><div class="card-body"><div class="small text-muted">District Start Year</div><div class="fs-4 fw-bold"><?= $foundedYear ? (int)$foundedYear : '—' ?></div></div></div></div>
    <div class="col-md-3"><div class="card shadow-sm"><div class="card-body"><div class="small text-muted">District Age</div><div class="fs-4 fw-bold"><?= $districtAge !== null ? $districtAge . ' yrs' : '—' ?></div></div></div></div>
  </div>

  <div class="card shadow-sm mb-3">
    <div class="card-body">
      <form method="GET" class="row g-2">
        <div class="col-md-2">
          <select name="type" class="form-select form-select-sm">
            <option value="">-- Type --</option>
            <option value="image" <?= $type === 'image' ? 'selected' : '' ?>>Image</option>
            <option value="video" <?= $type === 'video' ? 'selected' : '' ?>>Video</option>
            <option value="audio" <?= $type === 'audio' ? 'selected' : '' ?>>Audio</option>
          </select>
        </div>
        <div class="col-md-3">
          <select name="category" class="form-select form-select-sm">
            <option value="">-- Category --</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= htmlspecialchars($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2"><input type="text" name="tag" class="form-control form-control-sm" placeholder="Tag" value="<?= htmlspecialchars($tag) ?>"></div>
        <div class="col-md-2"><input type="text" name="event_tag" class="form-control form-control-sm" placeholder="Event" value="<?= htmlspecialchars($eventTag) ?>"></div>
        <div class="col-md-2"><input type="text" name="person_tag" class="form-control form-control-sm" placeholder="Person" value="<?= htmlspecialchars($personTag) ?>"></div>
        <div class="col-md-1"><input type="number" name="year" class="form-control form-control-sm" placeholder="Year" value="<?= htmlspecialchars((string)($year ?? '')) ?>"></div>
        <div class="col-auto">
          <button type="submit" class="btn btn-primary btn-sm">Filter</button>
          <a href="media.php" class="btn btn-secondary btn-sm">Reset</a>
        </div>
      </form>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <?php if (empty($media)): ?>
      <div class="col-12"><div class="alert alert-light border">No media uploaded yet.</div></div>
    <?php else: foreach ($media as $m): ?>
      <div class="col-md-4">
        <div class="card h-100 shadow-sm">
          <div class="card-body">
            <?php if ($m['media_type'] === 'image'): ?>
              <img src="../<?= htmlspecialchars($m['file_path']) ?>" alt="<?= htmlspecialchars($m['title']) ?>" class="media-thumb mb-2">
            <?php elseif ($m['media_type'] === 'video'): ?>
              <video controls class="media-thumb mb-2"><source src="../<?= htmlspecialchars($m['file_path']) ?>"></video>
            <?php else: ?>
              <audio controls class="w-100 mb-2"><source src="../<?= htmlspecialchars($m['file_path']) ?>"></audio>
            <?php endif; ?>
            <h6 class="mb-1"><?= htmlspecialchars($m['title']) ?></h6>
            <div class="small text-muted mb-2"><?= htmlspecialchars($m['category'] ?? 'General') ?> • <?= htmlspecialchars($m['media_type']) ?></div>
            <div class="small text-muted mb-2">
              <?php if (!empty($m['media_year'])): ?>Year: <?= (int)$m['media_year'] ?> • <?php endif; ?>
              <?= htmlspecialchars($m['event_tag'] ?? 'General Event') ?> • <?= htmlspecialchars($m['person_tag'] ?? 'General Person') ?>
            </div>
            <?php if (!empty($m['tags'])): ?><div class="small mb-2"><span class="badge bg-secondary"><?= htmlspecialchars($m['tags']) ?></span></div><?php endif; ?>
            <div class="small"><?= htmlspecialchars($m['description'] ?? '') ?></div>
            <div class="mt-2">
              <a href="../actions/delete_media.php?id=<?= (int)$m['media_id'] ?>" class="btn btn-sm btn-outline-danger js-confirm-delete">Delete</a>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; endif; ?>
  </div>

  <div class="row g-3">
    <div class="col-lg-5">
      <div class="card shadow-sm">
        <div class="card-header bg-white fw-semibold">Add Legacy Leader</div>
        <div class="card-body">
          <form method="POST" action="../actions/process_legacy_leader.php" class="row g-2">
            <div class="col-md-6">
              <label class="form-label">Role</label>
              <select name="role_type" class="form-select form-select-sm">
                <option>Bishop</option>
                <option>Director</option>
                <option>President</option>
                <option>Mother Director</option>
                <option selected>Other</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Conference</label>
              <input type="text" name="conference_name" class="form-control form-control-sm" placeholder="Optional">
            </div>
            <div class="col-12">
              <label class="form-label">Full Name</label>
              <input type="text" name="full_name" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Start Year</label>
              <input type="number" name="start_year" class="form-control form-control-sm" min="1800" max="<?= date('Y') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">End Year</label>
              <input type="number" name="end_year" class="form-control form-control-sm" min="1800" max="<?= date('Y') ?>">
            </div>
            <div class="col-12">
              <label class="form-label">Descriptions</label>
              <textarea name="descriptions" class="form-control form-control-sm" rows="2" placeholder="Historical context, tenure notes, etc."></textarea>
            </div>
            <div class="col-12">
              <label class="form-label">Achievements</label>
              <textarea name="achievements" class="form-control form-control-sm" rows="2" placeholder="Major milestones"></textarea>
            </div>
            <div class="col-12">
              <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-plus me-1"></i>Add Leader</button>
            </div>
          </form>
          <hr>
          <form method="POST" action="../actions/process_milestone.php" class="row g-2">
            <div class="col-12"><label class="form-label">Milestone Title</label><input type="text" name="title" class="form-control form-control-sm" required></div>
            <div class="col-md-4"><label class="form-label">Year</label><input type="number" name="milestone_year" class="form-control form-control-sm" min="1800" max="<?= date('Y') ?>" required></div>
            <div class="col-md-8"><label class="form-label">Descriptions</label><input type="text" name="descriptions" class="form-control form-control-sm"></div>
            <div class="col-12"><label class="form-label">Achievements</label><textarea name="achievements" class="form-control form-control-sm" rows="2"></textarea></div>
            <div class="col-12"><button type="submit" class="btn btn-outline-success btn-sm">Add Milestone</button></div>
          </form>
        </div>
      </div>
    </div>
    <div class="col-lg-7">
      <div class="card shadow-sm">
        <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
          <span>Timeline (Leaders + Milestones)</span>
          <a href="story_pages.php" class="btn btn-sm btn-outline-primary">Story Pages</a>
        </div>
        <div class="card-body p-0">
          <table class="table table-sm table-bordered mb-0">
            <thead class="table-light">
              <tr><th>Year</th><th>Type</th><th>Title</th><th>Meta</th><th>Notes</th></tr>
            </thead>
            <tbody>
              <?php if (empty($timeline)): ?>
                <tr><td colspan="5" class="text-center text-muted py-3">No legacy entries yet.</td></tr>
              <?php else: foreach ($timeline as $t): ?>
                <tr>
                  <td><?= $t['year'] > 0 ? (int)$t['year'] : '—' ?></td>
                  <td><?= htmlspecialchars($t['type']) ?></td>
                  <td><?= htmlspecialchars($t['title']) ?></td>
                  <td><?= htmlspecialchars($t['meta']) ?></td>
                  <td><?= htmlspecialchars($t['text'] ?? '—') ?></td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
