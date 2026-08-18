<?php
require_once __DIR__ . '/auth.php';
require_auth();
$authUser = current_auth_user();

// Determine active section for nav highlight
$currentPath = $_SERVER['PHP_SELF'] ?? '';

if (!function_exists('nav_active')) {
    function nav_active(string $path, array $patterns): string {
        foreach ($patterns as $p) {
            if (strpos($path, $p) !== false) return 'active';
        }
        return '';
    }
}
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm">
  <div class="container-fluid">
    <a class="navbar-brand d-flex align-items-center" href="/PhpstormProjects/19thepiscopaldistrict/AdminDash/index.php">
      <img src="/PhpstormProjects/19thepiscopaldistrict/AdminDash/19thDistrict.png" width="38" height="38" class="me-2" alt="19th District Logo">
      <span class="fw-semibold">19th Episcopal District</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <!-- Global search -->
      <form class="d-flex ms-2 me-3" method="GET" action="/PhpstormProjects/19thepiscopaldistrict/AdminDash/views/search.php">
        <input class="form-control form-control-sm me-2" type="search" name="q" placeholder="Global search…" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
        <button class="btn btn-light btn-sm" type="submit"><i class="fas fa-search"></i></button>
      </form>

      <ul class="navbar-nav ms-auto align-items-lg-center">

        <!-- Dashboard -->
        <li class="nav-item">
          <a class="nav-link <?= nav_active($currentPath, ['index.php']) ?>" href="/PhpstormProjects/19thepiscopaldistrict/AdminDash/index.php">
            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
          </a>
        </li>

        <!-- Members dropdown -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle <?= nav_active($currentPath, ['members']) ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-users me-1"></i>Members
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="/PhpstormProjects/19thepiscopaldistrict/AdminDash/views/members.php"><i class="fas fa-users fa-fw me-2 text-success"></i>All Members</a></li>
            <li><a class="dropdown-item" href="/PhpstormProjects/19thepiscopaldistrict/AdminDash/forms/add_member.php"><i class="fas fa-user-plus fa-fw me-2 text-success"></i>Add Member</a></li>
          </ul>
        </li>

        <!-- Structure dropdown -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle <?= nav_active($currentPath, ['conferences', 'areas', 'churches']) ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-sitemap me-1"></i>Structure
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="/PhpstormProjects/19thepiscopaldistrict/AdminDash/views/conferences.php"><i class="fas fa-sitemap fa-fw me-2 text-success"></i>Conferences</a></li>
            <li><a class="dropdown-item" href="/PhpstormProjects/19thepiscopaldistrict/AdminDash/views/areas.php"><i class="fas fa-map-marked-alt fa-fw me-2 text-success"></i>Areas</a></li>
            <li><a class="dropdown-item" href="/PhpstormProjects/19thepiscopaldistrict/AdminDash/views/churches.php"><i class="fas fa-church fa-fw me-2 text-success"></i>Churches</a></li>
          </ul>
        </li>

        <!-- Events dropdown -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle <?= nav_active($currentPath, ['events', 'event_attendance']) ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-calendar-alt me-1"></i>Events
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="/PhpstormProjects/19thepiscopaldistrict/AdminDash/views/events.php"><i class="fas fa-calendar-alt fa-fw me-2 text-success"></i>All Events</a></li>
            <li><a class="dropdown-item" href="/PhpstormProjects/19thepiscopaldistrict/AdminDash/forms/add_event.php"><i class="fas fa-calendar-plus fa-fw me-2 text-success"></i>Add Event</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="/PhpstormProjects/19thepiscopaldistrict/AdminDash/views/event_attendance.php"><i class="fas fa-user-check fa-fw me-2 text-primary"></i>Attendance</a></li>
          </ul>
        </li>

        <!-- Content dropdown -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle <?= nav_active($currentPath, ['media', 'story_page']) ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-photo-video me-1"></i>Content
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="/PhpstormProjects/19thepiscopaldistrict/AdminDash/views/media.php"><i class="fas fa-photo-video fa-fw me-2 text-success"></i>Media Library</a></li>
            <li><a class="dropdown-item" href="/PhpstormProjects/19thepiscopaldistrict/AdminDash/views/story_pages.php"><i class="fas fa-book-open fa-fw me-2 text-success"></i>Story Pages</a></li>
          </ul>
        </li>

        <!-- Reports & Admin dropdown -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle <?= nav_active($currentPath, ['statistical_reports', 'recycle_bin', 'backup']) ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-chart-bar me-1"></i>Reports &amp; Admin
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="/PhpstormProjects/19thepiscopaldistrict/AdminDash/views/statistical_reports.php"><i class="fas fa-chart-bar fa-fw me-2 text-success"></i>Reports</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="/PhpstormProjects/19thepiscopaldistrict/AdminDash/views/recycle_bin.php"><i class="fas fa-trash-restore fa-fw me-2 text-danger"></i>Recycle Bin</a></li>
            <li><a class="dropdown-item" href="/PhpstormProjects/19thepiscopaldistrict/AdminDash/actions/backup_bundle.php"><i class="fas fa-file-archive fa-fw me-2 text-secondary"></i>Backup</a></li>
          </ul>
        </li>

        <li class="nav-item ms-lg-2">
          <span class="navbar-text small text-light me-2">
            <i class="fas fa-user-shield me-1"></i><?= htmlspecialchars($authUser['username'] ?? 'Admin') ?>
          </span>
        </li>
        <li class="nav-item">
          <a class="btn btn-light btn-sm" href="/PhpstormProjects/19thepiscopaldistrict/AdminDash/actions/logout.php">
            <i class="fas fa-sign-out-alt me-1"></i>Logout
          </a>
        </li>

      </ul>
    </div>
  </div>
</nav>
