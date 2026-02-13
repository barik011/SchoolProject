<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';
require_admin();

$dbError = null;

if (is_post()) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        set_flash('error', 'Invalid request token.');
        redirect('admin/banners.php');
    }

    $action = (string) ($_POST['action'] ?? '');
    $bannerId = (int) ($_POST['banner_id'] ?? 0);

    try {
        if ($action === 'add') {
            $title = trim((string) ($_POST['title'] ?? ''));
            $subtitle = trim((string) ($_POST['subtitle'] ?? ''));
            $sortOrder = max(1, (int) ($_POST['sort_order'] ?? 1));

            if ($title === '') {
                throw new RuntimeException('Banner title is required.');
            }

            $imagePath = upload_image($_FILES['image'] ?? [], 'uploads/banners');
            if (!$imagePath) {
                throw new RuntimeException('Please select a banner image.');
            }

            $stmt = db()->prepare(
                'INSERT INTO home_banners (title, subtitle, image_path, is_active, sort_order)
                 VALUES (:title, :subtitle, :image_path, :is_active, :sort_order)'
            );
            $stmt->execute([
                'title' => $title,
                'subtitle' => $subtitle,
                'image_path' => $imagePath,
                'is_active' => 1,
                'sort_order' => $sortOrder,
            ]);
            set_flash('success', 'Banner added successfully.');
        }

        if ($action === 'toggle' && $bannerId > 0) {
            $isActive = (int) ($_POST['is_active'] ?? 0) === 1 ? 1 : 0;
            $stmt = db()->prepare('UPDATE home_banners SET is_active = :is_active WHERE id = :id');
            $stmt->execute([
                'is_active' => $isActive,
                'id' => $bannerId,
            ]);
            set_flash('success', 'Banner visibility updated.');
        }

        if ($action === 'delete' && $bannerId > 0) {
            $stmt = db()->prepare('SELECT image_path FROM home_banners WHERE id = :id LIMIT 1');
            $stmt->execute(['id' => $bannerId]);
            $banner = $stmt->fetch();

            $delete = db()->prepare('DELETE FROM home_banners WHERE id = :id');
            $delete->execute(['id' => $bannerId]);

            if ($banner && !empty($banner['image_path'])) {
                delete_file_if_exists((string) $banner['image_path']);
            }
            set_flash('success', 'Banner deleted.');
        }
    } catch (Throwable $exception) {
        set_flash('error', $exception->getMessage());
    }

    redirect('admin/banners.php');
}

try {
    $banners = db()->query(
        'SELECT id, title, subtitle, image_path, is_active, sort_order, created_at
         FROM home_banners
         ORDER BY sort_order ASC, id DESC'
    )->fetchAll();
} catch (Throwable $exception) {
    $banners = [];
    $dbError = 'home_banners table is missing. Run database/add_home_banners_table.sql once.';
}

$pageTitle = 'Home Banners';
include __DIR__ . '/../includes/admin_header.php';
?>

<?php if ($dbError): ?>
    <div class="alert alert-warning"><?= e($dbError) ?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h2 class="h5 mb-3">Add New Banner Slide</h2>
        <form method="post" enctype="multipart/form-data" class="row g-3 needs-validation" novalidate>
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="add">
            <div class="col-md-4">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Subtitle</label>
                <input type="text" name="subtitle" class="form-control" maxlength="255">
            </div>
            <div class="col-md-2">
                <label class="form-label">Sort Order</label>
                <input type="number" name="sort_order" class="form-control" min="1" value="1">
            </div>
            <div class="col-md-2">
                <label class="form-label">Image</label>
                <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp,.gif" required>
            </div>
            <div class="col-12">
                <button class="btn btn-primary" type="submit">Add Banner</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <h2 class="h5 mb-3">All Banner Slides</h2>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Preview</th>
                        <th>Title / Subtitle</th>
                        <th>Order</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$banners): ?>
                        <tr>
                            <td colspan="5" class="text-center text-body-secondary">No banners found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($banners as $banner): ?>
                            <tr>
                                <td>
                                    <img src="<?= e(url((string) $banner['image_path'])) ?>" alt="<?= e($banner['title']) ?>" class="rounded border" style="width:120px;height:65px;object-fit:cover;">
                                </td>
                                <td>
                                    <div class="fw-semibold"><?= e($banner['title']) ?></div>
                                    <small class="text-body-secondary"><?= e((string) $banner['subtitle']) ?></small>
                                </td>
                                <td><?= e((string) $banner['sort_order']) ?></td>
                                <td>
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="action" value="toggle">
                                        <input type="hidden" name="banner_id" value="<?= e((string) $banner['id']) ?>">
                                        <input type="hidden" name="is_active" value="<?= (int) $banner['is_active'] === 1 ? '0' : '1' ?>">
                                        <button type="submit" class="btn btn-sm <?= (int) $banner['is_active'] === 1 ? 'btn-outline-success' : 'btn-outline-secondary' ?>">
                                            <?= (int) $banner['is_active'] === 1 ? 'Visible' : 'Hidden' ?>
                                        </button>
                                    </form>
                                </td>
                                <td class="d-flex gap-2">
                                    <a href="<?= e(url('admin/edit_banner.php?id=' . (string) $banner['id'])) ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="banner_id" value="<?= e((string) $banner['id']) ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" data-confirm="Delete this banner slide?">Delete</button>
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

