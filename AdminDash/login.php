<?php
define('ALLOW_GUEST', true);
require_once 'db.php';
require_once 'includes/feature_tables.php';
require_once 'includes/auth.php';

ensure_feature_tables($pdo);
ensure_session_started();

if (isset($_SESSION['auth_user'])) {
    header('Location: /PhpstormProjects/19thepiscopaldistrict/AdminDash/index.php');
    exit;
}

$error = $_GET['error'] ?? '';
$csrfToken = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Administrator Login — 19th Episcopal District</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="bg-light">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
          <div class="card-body p-4">
            <div class="text-center mb-4">
              <h4 class="fw-bold text-success mb-1">19th Episcopal District</h4>
              <div class="text-muted small">Administrator Login</div>
            </div>

            <?php if ($error !== ''): ?>
              <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="actions/process_login.php">
              <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
              <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required autocomplete="username">
              </div>
              <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required autocomplete="current-password">
              </div>
              <button type="submit" class="btn btn-success w-100">
                <i class="fas fa-sign-in-alt me-1"></i>Login
              </button>
            </form>

            <div class="small text-muted mt-3">
              Contact your administrator for login credentials.
            </div>
            <div class="small mt-2">
              <a href="public_website/index.php" class="text-decoration-none"><i class="fas fa-globe me-1"></i>Go to public website</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
