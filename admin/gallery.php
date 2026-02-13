<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';
require_admin();

if (is_post()) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        set_flash('error', 'Invalid request token.');
        redirect('admin/gallery.php');
    }

    $action = (string) ($_POST['action'] ?? '');
    $imageId = (int) ($_POST['image_id'] ?? 0);

    try {
        if ($action === 'add') {
            $title = trim((string) ($_POST['title'] ?? ''));
            if ($title === '') {
                throw new RuntimeException('Image title is required.');
            }

            $imagePath = upload_image($_FILES['image'] ?? [], 'uploads/gallery');
            if (!$imagePath) {
                throw new RuntimeException('Please select an image file.');
            }

            $stmt = db()->prepare('INSERT INTO gallery_images (title, image_path, is_active) VALUES (:title, :image_path, :is_active)');
            $stmt->execute([
                'title' => $title,
                'image_path' => $imagePath,
                'is_active' => 1,
            ]);
            set_flash('success', 'Image uploaded successfully.');
        }

        if ($action === 'toggle' && $imageId > 0) {
            $isActive = (int) ($_POST['is_active'] ?? 0) === 1 ? 1 : 0;
            $stmt = db()->prepare('UPDATE gallery_images SET is_active = :is_active WHERE id = :id');
            $stmt->execute([
                'is_active' => $isActive,
                'id' => $imageId,
            ]);
            set_flash('success', 'Image visibility updated.');
        }

        if ($action === 'delete' && $imageId > 0) {
            $stmt = db()->prepare('SELECT image_path FROM gallery_images WHERE id = :id LIMIT 1');
            $stmt->execute(['id' => $imageId]);
            $image = $stmt->fetch();

            $delete = db()->prepare('DELETE FROM gallery_images WHERE id = :id');
            $delete->execute(['id' => $imageId]);

            if ($image && !empty($image['image_path'])) {
                delete_file_if_exists($image['image_path']);
            }
            set_flash('success', 'Image deleted.');
        }
    } catch (Throwable $exception) {
        set_flash('error', $exception->getMessage());
    }

    redirect('admin/gallery.php');
}

$images = get_gallery_images();

$pageTitle = 'Gallery Management';
include __DIR__ . '/../includes/admin_header.php';
?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h2 class="h5 mb-3">Upload New Image</h2>
        <form method="post" enctype="multipart/form-data" class="row g-3 needs-validation" novalidate>
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="add">
            <div class="col-md-4">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            <div class="col-md-5">
                <label class="form-label">Image</label>
                <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp,.gif" required>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button class="btn btn-primary w-100" type="submit">Upload</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <h2 class="h5 mb-3">All Gallery Images</h2>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Preview</th>
                        <th>Title</th>
                        <th>Uploaded</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$images): ?>
                        <tr>
                            <td colspan="5" class="text-center text-body-secondary">No images uploaded yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($images as $image): ?>
                            <tr>
                                <td><img src="<?= e(url((string) $image['image_path'])) ?>" alt="<?= e($image['title']) ?>" class="rounded border" style="width:80px;height:60px;object-fit:cover;"></td>
                                <td><?= e($image['title']) ?></td>
                                <td><?= e(date('d M Y', strtotime($image['uploaded_at']))) ?></td>
                                <td>
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="action" value="toggle">
                                        <input type="hidden" name="image_id" value="<?= e((string) $image['id']) ?>">
                                        <input type="hidden" name="is_active" value="<?= (int) $image['is_active'] === 1 ? '0' : '1' ?>">
                                        <button type="submit" class="btn btn-sm <?= (int) $image['is_active'] === 1 ? 'btn-outline-success' : 'btn-outline-secondary' ?>">
                                            <?= (int) $image['is_active'] === 1 ? 'Visible' : 'Hidden' ?>
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="image_id" value="<?= e((string) $image['id']) ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" data-confirm="Delete this image?">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
