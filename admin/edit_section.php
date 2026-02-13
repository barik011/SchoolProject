<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';
require_admin();

$pages = cms_pages();
$sectionId = (int) ($_GET['id'] ?? 0);
$errors = [];

$section = [
    'id' => 0,
    'page_slug' => (string) ($_GET['page'] ?? 'home'),
    'section_key' => '',
    'title' => '',
    'content' => '',
    'image_path' => null,
    'is_enabled' => 1,
    'sort_order' => 1,
];

if (!array_key_exists($section['page_slug'], $pages)) {
    $section['page_slug'] = 'home';
}

if ($sectionId > 0) {
    $stmt = db()->prepare('SELECT * FROM page_sections WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $sectionId]);
    $dbSection = $stmt->fetch();
    if (!$dbSection) {
        set_flash('error', 'Section not found.');
        redirect('admin/pages.php');
    }
    $section = $dbSection;
}

if (is_post()) {
    $section['page_slug'] = trim((string) ($_POST['page_slug'] ?? 'home'));
    $section['section_key'] = trim((string) ($_POST['section_key'] ?? ''));
    $section['title'] = trim((string) ($_POST['title'] ?? ''));
    $section['content'] = trim((string) ($_POST['content'] ?? ''));
    $section['sort_order'] = max(1, (int) ($_POST['sort_order'] ?? 1));
    $section['is_enabled'] = isset($_POST['is_enabled']) ? 1 : 0;
    $removeImage = isset($_POST['remove_image']);

    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid request token.';
    }
    if (!array_key_exists($section['page_slug'], $pages)) {
        $errors[] = 'Invalid page selected.';
    }
    if ($section['section_key'] === '') {
        $errors[] = 'Section key is required.';
    } elseif (!preg_match('/^[a-z0-9_-]+$/', $section['section_key'])) {
        $errors[] = 'Section key can contain only lowercase letters, numbers, underscores, and dashes.';
    }
    if ($section['title'] === '') {
        $errors[] = 'Section title is required.';
    }
    if ($section['content'] === '') {
        $errors[] = 'Section content is required.';
    }

    if (!$errors) {
        try {
            if ($removeImage && !empty($section['image_path'])) {
                delete_file_if_exists((string) $section['image_path']);
                $section['image_path'] = null;
            }

            if (isset($_FILES['image']) && ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                if (!empty($section['image_path'])) {
                    delete_file_if_exists((string) $section['image_path']);
                }
                $section['image_path'] = upload_image($_FILES['image'], 'uploads/banners');
            }

            if ($sectionId > 0) {
                $stmt = db()->prepare(
                    'UPDATE page_sections
                     SET page_slug = :page_slug, section_key = :section_key, title = :title, content = :content,
                         image_path = :image_path, is_enabled = :is_enabled, sort_order = :sort_order
                     WHERE id = :id'
                );
                $stmt->execute([
                    'id' => $sectionId,
                    'page_slug' => $section['page_slug'],
                    'section_key' => $section['section_key'],
                    'title' => $section['title'],
                    'content' => $section['content'],
                    'image_path' => $section['image_path'],
                    'is_enabled' => $section['is_enabled'],
                    'sort_order' => $section['sort_order'],
                ]);
                set_flash('success', 'Section updated successfully.');
            } else {
                $stmt = db()->prepare(
                    'INSERT INTO page_sections
                    (page_slug, section_key, title, content, image_path, is_enabled, sort_order)
                    VALUES (:page_slug, :section_key, :title, :content, :image_path, :is_enabled, :sort_order)'
                );
                $stmt->execute([
                    'page_slug' => $section['page_slug'],
                    'section_key' => $section['section_key'],
                    'title' => $section['title'],
                    'content' => $section['content'],
                    'image_path' => $section['image_path'],
                    'is_enabled' => $section['is_enabled'],
                    'sort_order' => $section['sort_order'],
                ]);
                set_flash('success', 'Section created successfully.');
            }

            redirect('admin/pages.php?page=' . urlencode((string) $section['page_slug']));
        } catch (Throwable $exception) {
            $errors[] = $exception->getMessage();
        }
    }
}

$pageTitle = $sectionId > 0 ? 'Edit Section' : 'Add Section';
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
                <div class="col-md-4">
                    <label class="form-label">Page</label>
                    <select name="page_slug" class="form-select" required>
                        <?php foreach ($pages as $slug => $label): ?>
                            <option value="<?= e($slug) ?>" <?= $section['page_slug'] === $slug ? 'selected' : '' ?>>
                                <?= e($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Section Key</label>
                    <input type="text" name="section_key" class="form-control" required value="<?= e((string) $section['section_key']) ?>" placeholder="e.g. welcome_note">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" min="1" value="<?= e((string) $section['sort_order']) ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Section Title</label>
                    <input type="text" name="title" class="form-control" required value="<?= e((string) $section['title']) ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Content</label>
                    <textarea name="content" rows="7" class="form-control" required><?= e((string) $section['content']) ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Image (optional)</label>
                    <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp,.gif">
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_enabled" id="is_enabled" <?= (int) $section['is_enabled'] === 1 ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_enabled">Enabled on frontend</label>
                    </div>
                </div>
                <?php if (!empty($section['image_path'])): ?>
                    <div class="col-12">
                        <img src="<?= e(url((string) $section['image_path'])) ?>" alt="Current image" style="max-width: 260px;" class="img-fluid rounded mb-2 border">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="remove_image" name="remove_image">
                            <label class="form-check-label" for="remove_image">Remove current image</label>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Save Section</button>
                <a href="<?= e(url('admin/pages.php?page=' . urlencode((string) $section['page_slug']))) ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
