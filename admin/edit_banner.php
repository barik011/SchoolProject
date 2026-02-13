<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';
require_admin();

$bannerId = (int) ($_GET['id'] ?? 0);
if ($bannerId <= 0) {
    set_flash('error', 'Invalid banner selected.');
    redirect('admin/banners.php');
}

$stmt = db()->prepare('SELECT id, title, subtitle, image_path, is_active, sort_order FROM home_banners WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $bannerId]);
$banner = $stmt->fetch();

if (!$banner) {
    set_flash('error', 'Banner not found.');
    redirect('admin/banners.php');
}

$errors = [];

if (is_post()) {
    $banner['title'] = trim((string) ($_POST['title'] ?? ''));
    $banner['subtitle'] = trim((string) ($_POST['subtitle'] ?? ''));
    $banner['sort_order'] = max(1, (int) ($_POST['sort_order'] ?? 1));
    $banner['is_active'] = isset($_POST['is_active']) ? 1 : 0;
    $removeImage = isset($_POST['remove_image']);

    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid request token.';
    }
    if ($banner['title'] === '') {
        $errors[] = 'Banner title is required.';
    }

    if (!$errors) {
        try {
            if ($removeImage && !empty($banner['image_path'])) {
                delete_file_if_exists((string) $banner['image_path']);
                $banner['image_path'] = '';
            }

            if (isset($_FILES['image']) && ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                if (!empty($banner['image_path'])) {
                    delete_file_if_exists((string) $banner['image_path']);
                }
                $banner['image_path'] = upload_image($_FILES['image'], 'uploads/banners');
            }

            if ($banner['image_path'] === '') {
                throw new RuntimeException('Banner image is required. Upload a new image before saving.');
            }

            $update = db()->prepare(
                'UPDATE home_banners
                 SET title = :title, subtitle = :subtitle, image_path = :image_path, is_active = :is_active, sort_order = :sort_order
                 WHERE id = :id'
            );
            $update->execute([
                'title' => $banner['title'],
                'subtitle' => $banner['subtitle'],
                'image_path' => $banner['image_path'],
                'is_active' => $banner['is_active'],
                'sort_order' => $banner['sort_order'],
                'id' => $bannerId,
            ]);

            set_flash('success', 'Banner updated successfully.');
            redirect('admin/banners.php');
        } catch (Throwable $exception) {
            $errors[] = $exception->getMessage();
        }
    }
}

$pageTitle = 'Edit Banner';
include __DIR__ . '/../includes/admin_header.php';
?>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <?php if ($errors): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control" required value="<?= e((string) $banner['title']) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Subtitle</label>
                    <input type="text" name="subtitle" class="form-control" maxlength="255" value="<?= e((string) $banner['subtitle']) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" min="1" value="<?= e((string) $banner['sort_order']) ?>">
                </div>
                <div class="col-md-8">
                    <label class="form-label">Replace Image (optional)</label>
                    <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp,.gif">
                </div>
                <div class="col-12">
                    <img src="<?= e(url((string) $banner['image_path'])) ?>" alt="<?= e((string) $banner['title']) ?>" class="img-fluid rounded border" style="max-width: 350px;">
                </div>
                <div class="col-md-6">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" <?= (int) $banner['is_active'] === 1 ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">Show on homepage</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="remove_image" name="remove_image">
                        <label class="form-check-label" for="remove_image">Remove current image</label>
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Save Banner</button>
                <a href="<?= e(url('admin/banners.php')) ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>

