<?php
require_once '../db.php';
require_once '../includes/auth.php';
$csrfToken = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Upload Media — 19th Episcopal District</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="container mt-4" style="max-width:700px">
  <h5 class="fw-bold text-success mb-4"><i class="fas fa-upload me-2"></i>Upload Media</h5>
  <form method="POST" action="../actions/process_media.php" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
    <div class="mb-3">
      <label class="form-label">Title <span class="text-danger">*</span></label>
      <input type="text" name="title" class="form-control" required>
    </div>
    <div class="row g-3 mb-3">
      <div class="col-md-4">
        <label class="form-label">Media Type <span class="text-danger">*</span></label>
        <select name="media_type" class="form-select" required>
          <option value="image">Image</option>
          <option value="video">Video</option>
          <option value="audio">Audio</option>
        </select>
      </div>
      <div class="col-md-8">
        <label class="form-label">Category</label>
        <input type="text" name="category" class="form-control" placeholder="e.g. Achievements, Leaders, Conferences, Events">
      </div>
    </div>
    <div class="row g-3 mb-3">
      <div class="col-md-4">
        <label class="form-label">Year</label>
        <input type="number" name="media_year" class="form-control" min="1800" max="<?= date('Y') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Event Tag</label>
        <input type="text" name="event_tag" class="form-control" placeholder="e.g. District Convention">
      </div>
      <div class="col-md-4">
        <label class="form-label">Person Tag</label>
        <input type="text" name="person_tag" class="form-control" placeholder="e.g. Rev. Jane Doe">
      </div>
    </div>
    <div class="mb-3">
      <label class="form-label">Tags</label>
      <input type="text" name="tags" class="form-control" placeholder="Comma-separated tags">
    </div>
    <div class="mb-3">
      <label class="form-label">File <span class="text-danger">*</span></label>
      <input type="file" name="media_file" class="form-control" required>
      <div class="small text-muted mt-1">Allowed: image (jpg/png/webp/gif), video (mp4/webm/ogg/mov), audio (mp3/wav/ogg/m4a)</div>
    </div>
    <div class="mb-4">
      <label class="form-label">Description</label>
      <textarea name="description" class="form-control" rows="3"></textarea>
    </div>
    <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i>Save Media</button>
    <a href="../views/media.php" class="btn btn-secondary ms-2">Back</a>
  </form>
</div>

<?php include '../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
