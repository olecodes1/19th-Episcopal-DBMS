<?php
define('ALLOW_GUEST', true);
require_once '../db.php';
require_once '../includes/feature_tables.php';

ensure_feature_tables($pdo);

function h(?string $value): string
{
    return htmlspecialchars((string)$value);
}

$page = $_GET['page'] ?? 'home';
$allowedPages = ['home', 'events', 'event', 'media', 'stories', 'story'];
if (!in_array($page, $allowedPages, true)) {
    $page = 'home';
}

// Shared nav active helper.
$isActive = fn(string $p): string => $page === $p ? 'active' : '';

// Shared lightweight summaries.
$totalMembers = (int)$pdo->query("SELECT COUNT(*) FROM members")->fetchColumn();
$totalChurches = (int)$pdo->query("SELECT COUNT(*) FROM churches WHERE status='active'")->fetchColumn();
$totalAreas = (int)$pdo->query("SELECT COUNT(*) FROM areas")->fetchColumn();
$totalConfs = (int)$pdo->query("SELECT COUNT(*) FROM conferences")->fetchColumn();
$totalEvents = (int)$pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
$totalMedia = (int)$pdo->query("SELECT COUNT(*) FROM media_items WHERE deleted_at IS NULL")->fetchColumn();

$bestEvent = $pdo->query("
    SELECT e.event_name, COALESCE(SUM(b.attendance_count), 0) AS total_attendance
    FROM events e
    LEFT JOIN event_attendance_breakdowns b ON b.event_id = e.event_id
    GROUP BY e.event_id, e.event_name
    ORDER BY total_attendance DESC, e.event_name
    LIMIT 1
")->fetch();

$lowestEvent = $pdo->query("
    SELECT e.event_name, COALESCE(SUM(b.attendance_count), 0) AS total_attendance
    FROM events e
    LEFT JOIN event_attendance_breakdowns b ON b.event_id = e.event_id
    GROUP BY e.event_id, e.event_name
    ORDER BY total_attendance ASC, e.event_name
    LIMIT 1
")->fetch();

$latestMedia = $pdo->query("
    SELECT title, media_type, uploaded_at
    FROM media_items
    WHERE deleted_at IS NULL
    ORDER BY uploaded_at DESC
    LIMIT 3
")->fetchAll();

$latestStories = $pdo->query("
    SELECT title, slug, status, created_at
    FROM story_pages
    WHERE deleted_at IS NULL
    ORDER BY created_at DESC
    LIMIT 3
")->fetchAll();

// New field stats (safe — only run if columns exist)
$hasJoinedYpd  = (bool)$pdo->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='members' AND COLUMN_NAME='joined_ypd'")->fetchColumn();
$hasFullChurch = (bool)$pdo->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='members' AND COLUMN_NAME='full_member_of_church'")->fetchColumn();
$hasOccStatus  = (bool)$pdo->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='members' AND COLUMN_NAME='occupational_status'")->fetchColumn();

$ypdYes = $hasJoinedYpd  ? (int)$pdo->query("SELECT SUM(joined_ypd='Yes') FROM members")->fetchColumn() : 0;
$ypdNo  = $hasJoinedYpd  ? (int)$pdo->query("SELECT SUM(joined_ypd='No' OR joined_ypd IS NULL) FROM members")->fetchColumn() : 0;

$fullYes = $hasFullChurch ? (int)$pdo->query("SELECT SUM(full_member_of_church='Yes') FROM members")->fetchColumn() : 0;
$fullNo  = $hasFullChurch ? (int)$pdo->query("SELECT SUM(full_member_of_church='No' OR full_member_of_church IS NULL) FROM members")->fetchColumn() : 0;

$occRows = [];
if ($hasOccStatus) {
    $occRows = $pdo->query("
        SELECT occupational_status, COUNT(*) AS cnt
        FROM members
        WHERE occupational_status IS NOT NULL AND occupational_status != ''
        GROUP BY occupational_status
        ORDER BY cnt DESC
        LIMIT 7
    ")->fetchAll(PDO::FETCH_ASSOC);
}

// Component & gender aggregates (read-only summaries for public dashboard)
$components = $pdo->query(
    "SELECT
        SUM(component = 'MB') AS MB,
        SUM(component = 'AS') AS AS_,
        SUM(component = 'Y')  AS Y,
        SUM(component = 'YA') AS YA
    FROM members
    "
)->fetch();

$genders = $pdo->query(
    "SELECT
        SUM(gender = 'M') AS Male,
        SUM(gender = 'F') AS Female
    FROM members
    "
)->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>19th Episcopal District — Public Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/styles.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm">
    <div class="container-fluid">
      <a class="navbar-brand d-flex align-items-center" href="?page=home">
        <img src="../19thDistrict.png" width="36" height="36" class="me-2" alt="19th District Logo">
        <span class="fw-semibold">19th Episcopal District</span>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#publicNav" aria-controls="publicNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="publicNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link <?= $isActive('home') ?>" href="?page=home">Home</a></li>
          <li class="nav-item"><a class="nav-link <?= $isActive('events') ?>" href="?page=events">Events</a></li>
          <li class="nav-item"><a class="nav-link <?= $isActive('media') ?>" href="?page=media">Media</a></li>
          <li class="nav-item"><a class="nav-link <?= $isActive('stories') ?>" href="?page=stories">Stories</a></li>
          <li class="nav-item ms-lg-2">
            <a class="btn btn-light btn-sm mt-1 mt-lg-0" href="../login.php"><i class="fas fa-lock me-1"></i>Admin Login</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container-fluid mt-4 px-4">
    <?php if ($page === 'home'): ?>
      <?php
      $latestEvents = $pdo->query("SELECT event_id, event_name, event_date, location FROM events ORDER BY event_date DESC LIMIT 5")->fetchAll();
      $latestStories = $pdo->query("SELECT title, slug, story_year FROM story_pages WHERE deleted_at IS NULL AND status='published' ORDER BY COALESCE(story_year, 0) DESC, created_at DESC LIMIT 5")->fetchAll();
      ?>
      <h4 class="fw-bold text-success mb-1">District Public Dashboard</h4>
      <p class="text-muted small mb-4">Read-only summaries for members and visitors.</p>

      <!-- Charts row (public read-only parity with AdminDash) -->
      <div class="row g-3 mb-4">
        <div class="col-lg-6">
          <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-semibold text-success">Members by Component</div>
            <div class="card-body">
              <div class="row text-center mb-3">
                <div class="col-6 col-md-3 mb-3">
                  <div class="fs-2 fw-bold text-primary"><?= (int)($components['MB'] ?? 0) ?></div>
                  <div class="small text-muted">Mother Sunbeam</div>
                </div>
                <div class="col-6 col-md-3 mb-3">
                  <div class="fs-2 fw-bold text-success"><?= (int)($components['AS_'] ?? 0) ?></div>
                  <div class="small text-muted">Allen Stars</div>
                </div>
                <div class="col-6 col-md-3 mb-3">
                  <div class="fs-2 fw-bold text-warning"><?= (int)($components['Y'] ?? 0) ?></div>
                  <div class="small text-muted">Youth</div>
                </div>
                <div class="col-6 col-md-3 mb-3">
                  <div class="fs-2 fw-bold text-info"><?= (int)($components['YA'] ?? 0) ?></div>
                  <div class="small text-muted">Young Adults</div>
                </div>
              </div>
              <canvas id="componentChart" height="120"></canvas>
            </div>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-semibold text-success">Members by Gender</div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center">
              <canvas id="genderChart" height="160"></canvas>
              <div class="d-flex gap-4 mt-3">
                <div class="text-center">
                  <div class="fs-4 fw-bold text-primary"><?= (int)($genders['Male'] ?? 0) ?></div>
                  <div class="small text-muted">Male</div>
                </div>
                <div class="text-center">
                  <div class="fs-4 fw-bold text-danger"><?= (int)($genders['Female'] ?? 0) ?></div>
                  <div class="small text-muted">Female</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="row g-3 mb-4">
        <div class="col-xl-2 col-md-4 col-sm-6">
          <div class="card border-start border-success border-4 shadow-sm h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
              <div>
                <div class="text-muted small">Total Members</div>
                <div class="fs-3 fw-bold"><?= $totalMembers ?></div>
              </div>
              <i class="fas fa-users fa-2x text-success opacity-50"></i>
            </div>
          </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
          <div class="card border-start border-primary border-4 shadow-sm h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
              <div>
                <div class="text-muted small">Active Churches</div>
                <div class="fs-3 fw-bold"><?= $totalChurches ?></div>
              </div>
              <i class="fas fa-church fa-2x text-primary opacity-50"></i>
            </div>
          </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
          <div class="card border-start border-warning border-4 shadow-sm h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
              <div>
                <div class="text-muted small">Areas</div>
                <div class="fs-3 fw-bold"><?= $totalAreas ?></div>
              </div>
              <i class="fas fa-map-marked-alt fa-2x text-warning opacity-50"></i>
            </div>
          </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
          <div class="card border-start border-info border-4 shadow-sm h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
              <div>
                <div class="text-muted small">Conferences</div>
                <div class="fs-3 fw-bold"><?= $totalConfs ?></div>
              </div>
              <i class="fas fa-sitemap fa-2x text-info opacity-50"></i>
            </div>
          </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
          <div class="card border-start border-danger border-4 shadow-sm h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
              <div>
                <div class="text-muted small">Events</div>
                <div class="fs-3 fw-bold"><?= $totalEvents ?></div>
              </div>
              <i class="fas fa-calendar-alt fa-2x text-danger opacity-50"></i>
            </div>
          </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
          <div class="card border-start border-secondary border-4 shadow-sm h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
              <div>
                <div class="text-muted small">Media Items</div>
                <div class="fs-3 fw-bold"><?= $totalMedia ?></div>
              </div>
              <i class="fas fa-photo-video fa-2x text-secondary opacity-50"></i>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-lg-4">
          <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-semibold text-success">Attendance Snapshot</div>
            <div class="card-body small">
              <div class="mb-2"><span class="text-muted">Top event:</span> <strong><?= htmlspecialchars($bestEvent['event_name'] ?? '—') ?></strong> (<?= (int)($bestEvent['total_attendance'] ?? 0) ?>)</div>
              <div><span class="text-muted">Lowest event:</span> <strong><?= htmlspecialchars($lowestEvent['event_name'] ?? '—') ?></strong> (<?= (int)($lowestEvent['total_attendance'] ?? 0) ?>)</div>
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-semibold text-success">Latest Media Uploads</div>
            <div class="card-body small">
              <?php if (!$latestMedia): ?>
                <div class="text-muted">No media uploads yet.</div>
              <?php else: foreach ($latestMedia as $m): ?>
                <div class="mb-1"><strong><?= htmlspecialchars($m['title']) ?></strong> <span class="text-muted">(<?= htmlspecialchars($m['media_type']) ?>)</span></div>
              <?php endforeach; endif; ?>
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-semibold text-success">Latest Story Pages</div>
            <div class="card-body small">
              <?php if (!$latestStories): ?>
                <div class="text-muted">No stories yet.</div>
              <?php else: foreach ($latestStories as $s): ?>
                <div class="mb-1">
                  <a href="?page=story&slug=<?= urlencode($s['slug']) ?>" class="text-decoration-none"><?= htmlspecialchars($s['title']) ?></a>
                  <span class="badge bg-<?= ($s['status'] ?? 'draft') === 'published' ? 'success' : 'secondary' ?> ms-1"><?= htmlspecialchars($s['status'] ?? 'draft') ?></span>
                </div>
              <?php endforeach; endif; ?>
            </div>
          </div>
        </div>
      </div>

      <?php if ($hasJoinedYpd || $hasFullChurch || $hasOccStatus): ?>
      <!-- ── New Charts Row ── -->
      <div class="row g-3 mb-4">

        <?php if ($hasJoinedYpd): ?>
        <div class="col-lg-3 col-md-6">
          <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-semibold text-success">Joined YPD</div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center">
              <canvas id="ypdChart" height="160"></canvas>
              <div class="d-flex gap-4 mt-3">
                <div class="text-center"><div class="fs-4 fw-bold text-success"><?= $ypdYes ?></div><div class="small text-muted">Yes</div></div>
                <div class="text-center"><div class="fs-4 fw-bold text-secondary"><?= $ypdNo ?></div><div class="small text-muted">No / Unknown</div></div>
              </div>
            </div>
          </div>
        </div>
        <?php endif; ?>

        <?php if ($hasFullChurch): ?>
        <div class="col-lg-3 col-md-6">
          <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-semibold text-success">Full Church Member</div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center">
              <canvas id="fullChurchChart" height="160"></canvas>
              <div class="d-flex gap-4 mt-3">
                <div class="text-center"><div class="fs-4 fw-bold text-primary"><?= $fullYes ?></div><div class="small text-muted">Yes</div></div>
                <div class="text-center"><div class="fs-4 fw-bold text-secondary"><?= $fullNo ?></div><div class="small text-muted">No / Unknown</div></div>
              </div>
            </div>
          </div>
        </div>
        <?php endif; ?>

        <?php if ($hasOccStatus && !empty($occRows)): ?>
        <div class="col-lg-6">
          <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-semibold text-success">Occupational Status</div>
            <div class="card-body">
              <canvas id="occChart" height="120"></canvas>
            </div>
          </div>
        </div>
        <?php endif; ?>

      </div>
      <?php endif; ?>

      <div class="row g-3">
        <div class="col-lg-6">
          <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-semibold text-success">Latest Events</div>
            <div class="card-body">
              <?php if (!$latestEvents): ?>
                <div class="text-muted">No events available.</div>
              <?php else: foreach ($latestEvents as $e): ?>
                <div class="mb-2">
                  <a class="text-decoration-none fw-semibold" href="?page=event&id=<?= (int)$e['event_id'] ?>"><?= h($e['event_name']) ?></a>
                  <div class="small text-muted"><?= h($e['event_date']) ?><?= !empty($e['location']) ? ' • ' . h($e['location']) : '' ?></div>
                </div>
              <?php endforeach; endif; ?>
            </div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-semibold text-success">Latest Published Stories</div>
            <div class="card-body">
              <?php if (!$latestStories): ?>
                <div class="text-muted">No published stories available.</div>
              <?php else: foreach ($latestStories as $s): ?>
                <div class="mb-2">
                  <a class="text-decoration-none fw-semibold" href="?page=story&slug=<?= urlencode($s['slug']) ?>"><?= h($s['title']) ?></a>
                  <div class="small text-muted"><?= h((string)($s['story_year'] ?? '')) ?></div>
                </div>
              <?php endforeach; endif; ?>
            </div>
          </div>
        </div>
      </div>

    <?php elseif ($page === 'events'): ?>
      <?php
      $q = trim($_GET['q'] ?? '');
      $sql = "SELECT event_id, event_name, event_date, location, description FROM events WHERE 1=1";
      $params = [];
      if ($q !== '') {
          $sql .= " AND (event_name LIKE ? OR location LIKE ? OR description LIKE ?)";
          $params = ["%$q%", "%$q%", "%$q%"];
      }
      $sql .= " ORDER BY event_date DESC";
      $stmt = $pdo->prepare($sql);
      $stmt->execute($params);
      $eventRows = $stmt->fetchAll();
      ?>
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold text-success mb-0">Events</h4>
      </div>
      <form class="row g-2 mb-3" method="GET">
        <input type="hidden" name="page" value="events">
        <div class="col-md-4"><input type="text" name="q" class="form-control form-control-sm" placeholder="Search event/location" value="<?= h($q) ?>"></div>
        <div class="col-auto">
          <button type="submit" class="btn btn-primary btn-sm">Search</button>
          <a href="?page=events" class="btn btn-secondary btn-sm">Reset</a>
        </div>
      </form>
      <div class="card shadow-sm">
        <div class="card-body p-0">
          <table class="table table-bordered table-sm mb-0">
            <thead class="table-success">
              <tr><th>Event</th><th>Date</th><th>Location</th><th>Details</th></tr>
            </thead>
            <tbody>
              <?php if (!$eventRows): ?>
                <tr><td colspan="4" class="text-center text-muted py-3">No events found.</td></tr>
              <?php else: foreach ($eventRows as $e): ?>
                <tr>
                  <td><?= h($e['event_name']) ?></td>
                  <td><?= h($e['event_date']) ?></td>
                  <td><?= h($e['location'] ?? '—') ?></td>
                  <td><a href="?page=event&id=<?= (int)$e['event_id'] ?>" class="btn btn-outline-success btn-sm">View</a></td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    <?php elseif ($page === 'event'): ?>
      <?php
      $eventId = (int)($_GET['id'] ?? 0);
      $stmt = $pdo->prepare("SELECT event_id, event_name, event_date, location, description, attendance_count FROM events WHERE event_id = ? LIMIT 1");
      $stmt->execute([$eventId]);
      $event = $stmt->fetch();
      ?>
      <?php if (!$event): ?>
        <div class="alert alert-warning">Event not found.</div>
      <?php else: ?>
        <h4 class="fw-bold text-success mb-3"><?= h($event['event_name']) ?></h4>
        <div class="card shadow-sm">
          <div class="card-body">
            <div class="mb-2"><strong>Date:</strong> <?= h($event['event_date']) ?></div>
            <div class="mb-2"><strong>Location:</strong> <?= h($event['location'] ?? '—') ?></div>
            <div class="mb-2"><strong>Attendance:</strong> <?= (int)($event['attendance_count'] ?? 0) ?></div>
            <div><strong>Description:</strong><br><?= nl2br(h($event['description'] ?? '')) ?></div>
          </div>
        </div>
      <?php endif; ?>
      <a href="?page=events" class="btn btn-secondary btn-sm mt-3">Back to Events</a>

    <?php elseif ($page === 'media'): ?>
      <?php
      $type = $_GET['type'] ?? '';
      $category = trim($_GET['category'] ?? '');
      $year = (int)($_GET['year'] ?? 0) ?: null;

      $mediaSql = "SELECT media_id, title, media_type, category, media_year, file_path, description FROM media_items WHERE deleted_at IS NULL";
      $mediaParams = [];
      if (in_array($type, ['image', 'video', 'audio'], true)) {
          $mediaSql .= " AND media_type = ?";
          $mediaParams[] = $type;
      }
      if ($category !== '') {
          $mediaSql .= " AND category = ?";
          $mediaParams[] = $category;
      }
      if ($year !== null) {
          $mediaSql .= " AND media_year = ?";
          $mediaParams[] = $year;
      }
      $mediaSql .= " ORDER BY uploaded_at DESC";
      $mediaStmt = $pdo->prepare($mediaSql);
      $mediaStmt->execute($mediaParams);
      $mediaRows = $mediaStmt->fetchAll();
      $categories = $pdo->query("SELECT DISTINCT category FROM media_items WHERE deleted_at IS NULL AND category IS NOT NULL AND category <> '' ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
      ?>
      <h4 class="fw-bold text-success mb-3">Media Gallery</h4>
      <form class="row g-2 mb-3" method="GET">
        <input type="hidden" name="page" value="media">
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
              <option value="<?= h($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>><?= h($cat) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2"><input type="number" name="year" class="form-control form-control-sm" placeholder="Year" value="<?= h($year !== null ? (string)$year : '') ?>"></div>
        <div class="col-auto">
          <button type="submit" class="btn btn-primary btn-sm">Filter</button>
          <a href="?page=media" class="btn btn-secondary btn-sm">Reset</a>
        </div>
      </form>
      <div class="row g-3">
        <?php if (!$mediaRows): ?>
          <div class="col-12"><div class="alert alert-light border">No media found.</div></div>
        <?php else: foreach ($mediaRows as $m): ?>
          <div class="col-md-4">
            <div class="card shadow-sm h-100">
              <div class="card-body">
                <?php if ($m['media_type'] === 'image'): ?>
                  <img src="../<?= h($m['file_path']) ?>" alt="<?= h($m['title']) ?>" class="media-thumb mb-2">
                <?php elseif ($m['media_type'] === 'video'): ?>
                  <video controls class="media-thumb mb-2"><source src="../<?= h($m['file_path']) ?>"></video>
                <?php else: ?>
                  <audio controls class="w-100 mb-2"><source src="../<?= h($m['file_path']) ?>"></audio>
                <?php endif; ?>
                <h6 class="mb-1"><?= h($m['title']) ?></h6>
                <div class="small text-muted"><?= h($m['category'] ?? 'General') ?><?= !empty($m['media_year']) ? ' • ' . (int)$m['media_year'] : '' ?></div>
                <div class="small mt-2"><?= h($m['description'] ?? '') ?></div>
              </div>
            </div>
          </div>
        <?php endforeach; endif; ?>
      </div>

    <?php elseif ($page === 'stories'): ?>
      <?php
      $storyRows = $pdo->query("
        SELECT title, slug, story_year, content
        FROM story_pages
        WHERE deleted_at IS NULL AND status = 'published'
        ORDER BY COALESCE(story_year, 0) DESC, created_at DESC
      ")->fetchAll();
      ?>
      <h4 class="fw-bold text-success mb-3">Stories & History</h4>
      <div class="card shadow-sm">
        <div class="card-body">
          <?php if (!$storyRows): ?>
            <div class="text-muted">No published stories available.</div>
          <?php else: foreach ($storyRows as $s): ?>
            <div class="border rounded p-2 mb-2">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <a class="fw-semibold text-decoration-none" href="?page=story&slug=<?= urlencode($s['slug']) ?>"><?= h($s['title']) ?></a>
                  <div class="small text-muted"><?= h((string)($s['story_year'] ?? '')) ?></div>
                </div>
              </div>
              <div class="small text-muted mt-1"><?= h(mb_strimwidth($s['content'] ?? '', 0, 180, '…')) ?></div>
            </div>
          <?php endforeach; endif; ?>
        </div>
      </div>

    <?php elseif ($page === 'story'): ?>
      <?php
      $slug = trim($_GET['slug'] ?? '');
      $stmt = $pdo->prepare("SELECT title, story_year, content, status, cover_media_id, media_ids_json FROM story_pages WHERE slug = ? AND deleted_at IS NULL AND status='published' LIMIT 1");
      $stmt->execute([$slug]);
      $story = $stmt->fetch(PDO::FETCH_ASSOC);
      $cover = null;
      $storyMedia = [];
      if ($story && !empty($story['cover_media_id'])) {
          $c = $pdo->prepare("SELECT file_path, media_type, title FROM media_items WHERE media_id = ? AND deleted_at IS NULL LIMIT 1");
          $c->execute([(int)$story['cover_media_id']]);
          $cover = $c->fetch(PDO::FETCH_ASSOC) ?: null;
      }
      if ($story) {
          $ids = json_decode((string)($story['media_ids_json'] ?? '[]'), true);
          if (is_array($ids) && !empty($ids)) {
              $ids = array_values(array_filter(array_map('intval', $ids), fn($v) => $v > 0));
              if ($ids) {
                  $placeholders = implode(',', array_fill(0, count($ids), '?'));
                  $orderExpr = implode(',', $ids);
                  $m = $pdo->prepare("SELECT media_id, title, media_type, file_path FROM media_items WHERE media_id IN ($placeholders) AND deleted_at IS NULL ORDER BY FIELD(media_id, $orderExpr)");
                  $m->execute($ids);
                  $storyMedia = $m->fetchAll(PDO::FETCH_ASSOC);
              }
          }
      }
      ?>
      <?php if (!$story): ?>
        <div class="alert alert-warning">Published story not found.</div>
      <?php else: ?>
        <h4 class="fw-bold text-success mb-2"><?= h($story['title']) ?></h4>
        <div class="small text-muted mb-3">Year: <?= h((string)($story['story_year'] ?? '—')) ?></div>
        <?php if ($cover && ($cover['media_type'] ?? '') === 'image'): ?>
          <div class="card shadow-sm mb-3"><div class="card-body"><img src="../<?= h($cover['file_path']) ?>" class="media-thumb" alt="<?= h($cover['title']) ?>"></div></div>
        <?php endif; ?>
        <div class="card shadow-sm mb-3"><div class="card-body" style="white-space: pre-wrap;"><?= h($story['content'] ?? '') ?></div></div>
        <?php if ($storyMedia): ?>
          <div class="row g-3 mb-3">
            <?php foreach ($storyMedia as $m): ?>
              <div class="col-md-4">
                <div class="card shadow-sm h-100">
                  <div class="card-body">
                    <h6><?= h($m['title']) ?></h6>
                    <?php if ($m['media_type'] === 'image'): ?>
                      <img src="../<?= h($m['file_path']) ?>" class="media-thumb" alt="<?= h($m['title']) ?>">
                    <?php elseif ($m['media_type'] === 'video'): ?>
                      <video controls class="media-thumb"><source src="../<?= h($m['file_path']) ?>"></video>
                    <?php else: ?>
                      <audio controls class="w-100"><source src="../<?= h($m['file_path']) ?>"></audio>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      <?php endif; ?>
      <a href="?page=stories" class="btn btn-secondary btn-sm">Back to Stories</a>
    <?php endif; ?>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <script>
  // Component bar chart
  new Chart(document.getElementById('componentChart'), {
    type: 'bar',
    data: {
      labels: ['Mother Sunbeam', 'Allen Stars', 'Youth', 'Young Adults'],
      datasets: [{
        data: [<?= (int)($components['MB']??0) ?>, <?= (int)($components['AS_']??0) ?>, <?= (int)($components['Y']??0) ?>, <?= (int)($components['YA']??0) ?>],
        backgroundColor: ['#0d6efd','#198754','#ffc107','#0dcaf0']
      }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
  });

  // Gender doughnut chart
  new Chart(document.getElementById('genderChart'), {
    type: 'doughnut',
    data: {
      labels: ['Male', 'Female'],
      datasets: [{
        data: [<?= (int)($genders['Male']??0) ?>, <?= (int)($genders['Female']??0) ?>],
        backgroundColor: ['#0d6efd','#dc3545']
      }]
    },
    options: { plugins: { legend: { display: false } } }
  });

  <?php if ($hasJoinedYpd): ?>
  // Joined YPD doughnut
  new Chart(document.getElementById('ypdChart'), {
    type: 'doughnut',
    data: {
      labels: ['Yes', 'No / Unknown'],
      datasets: [{
        data: [<?= $ypdYes ?>, <?= $ypdNo ?>],
        backgroundColor: ['#198754', '#adb5bd']
      }]
    },
    options: { plugins: { legend: { display: false } } }
  });
  <?php endif; ?>

  <?php if ($hasFullChurch): ?>
  // Full Church Member doughnut
  new Chart(document.getElementById('fullChurchChart'), {
    type: 'doughnut',
    data: {
      labels: ['Yes', 'No / Unknown'],
      datasets: [{
        data: [<?= $fullYes ?>, <?= $fullNo ?>],
        backgroundColor: ['#0d6efd', '#adb5bd']
      }]
    },
    options: { plugins: { legend: { display: false } } }
  });
  <?php endif; ?>

  <?php if ($hasOccStatus && !empty($occRows)): ?>
  // Occupational Status bar chart
  new Chart(document.getElementById('occChart'), {
    type: 'bar',
    data: {
      labels: [<?= implode(',', array_map(fn($r) => json_encode($r['occupational_status']), $occRows)) ?>],
      datasets: [{
        label: 'Members',
        data: [<?= implode(',', array_column($occRows, 'cnt')) ?>],
        backgroundColor: ['#0d6efd','#198754','#ffc107','#0dcaf0','#dc3545','#6f42c1','#fd7e14']
      }]
    },
    options: {
      indexAxis: 'y',
      plugins: { legend: { display: false } },
      scales: { x: { beginAtZero: true } }
    }
  });
  <?php endif; ?>
  </script>
</body>
</html>

