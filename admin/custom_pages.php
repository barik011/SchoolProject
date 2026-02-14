<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';
require_admin();

$pageTitle = 'Custom Pages';
$extensionsReady = cms_extensions_available();
$errors = [];

$form = [
    'id' => 0,
    'title' => '',
    'slug' => '',
    'excerpt' => '',
    'content' => '',
    'hero_image' => null,
    'sort_order' => 1,
    'is_enabled' => 1,
];

if ($extensionsReady && is_post()) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        set_flash('error', 'Invalid request token.');
        redirect('admin/custom_pages.php');
    }

    $action = (string) ($_POST['action'] ?? '');
    $pageId = (int) ($_POST['page_id'] ?? 0);

    try {
        if ($action === 'toggle' && $pageId > 0) {
            $enabled = (int) ($_POST['enabled'] ?? 0) === 1 ? 1 : 0;
            $stmt = db()->prepare('UPDATE custom_pages SET is_enabled = :enabled WHERE id = :id');
            $stmt->execute([
                'enabled' => $enabled,
                'id' => $pageId,
            ]);
            set_flash('success', 'Page visibility updated.');
            redirect('admin/custom_pages.php');
        }

        if ($action === 'delete' && $pageId > 0) {
            $page = get_custom_page_by_id($pageId);
            if (!$page) {
                throw new RuntimeException('Page not found.');
            }

            $stmt = db()->prepare('DELETE FROM custom_pages WHERE id = :id');
            $stmt->execute(['id' => $pageId]);

            if (!empty($page['hero_image'])) {
                delete_file_if_exists((string) $page['hero_image']);
            }

            set_flash('success', 'Page deleted.');
            redirect('admin/custom_pages.php');
        }

        if ($action === 'save') {
            $form = [
                'id' => $pageId,
                'title' => trim((string) ($_POST['title'] ?? '')),
                'slug' => trim((string) ($_POST['slug'] ?? '')),
                'excerpt' => trim((string) ($_POST['excerpt'] ?? '')),
                'content' => trim((string) ($_POST['content'] ?? '')),
                'hero_image' => null,
                'sort_order' => max(1, (int) ($_POST['sort_order'] ?? 1)),
                'is_enabled' => isset($_POST['is_enabled']) ? 1 : 0,
            ];

            $existing = $form['id'] > 0 ? get_custom_page_by_id((int) $form['id']) : null;
            if ($form['id'] > 0 && !$existing) {
                throw new RuntimeException('Page not found.');
            }

            $form['hero_image'] = $existing['hero_image'] ?? null;

            if ($form['title'] === '') {
                $errors[] = 'Title is required.';
            }
            if ($form['content'] === '') {
                $errors[] = 'Content is required.';
            }

            $slug = $form['slug'] !== '' ? slugify($form['slug']) : slugify($form['title']);
            if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
                $errors[] = 'Slug can contain only lowercase letters, numbers, and dashes.';
            }

            $unique = db()->prepare('SELECT id FROM custom_pages WHERE slug = :slug AND id <> :id LIMIT 1');
            $unique->execute([
                'slug' => $slug,
                'id' => (int) $form['id'],
            ]);
            if ($unique->fetch()) {
                $errors[] = 'Slug already exists. Please choose another.';
            }

            if (!$errors) {
                $removeImage = isset($_POST['remove_image']);
                if ($removeImage && !empty($form['hero_image'])) {
                    delete_file_if_exists((string) $form['hero_image']);
                    $form['hero_image'] = null;
                }

                if (isset($_FILES['hero_image']) && ($_FILES['hero_image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                    if (!empty($form['hero_image'])) {
                        delete_file_if_exists((string) $form['hero_image']);
                    }
                    $form['hero_image'] = upload_image($_FILES['hero_image'], 'uploads/banners');
                }

                if ((int) $form['id'] > 0) {
                    $stmt = db()->prepare(
                        'UPDATE custom_pages
                         SET title = :title, slug = :slug, excerpt = :excerpt, content = :content,
                             hero_image = :hero_image, sort_order = :sort_order, is_enabled = :is_enabled
                         WHERE id = :id'
                    );
                    $stmt->execute([
                        'id' => (int) $form['id'],
                        'title' => $form['title'],
                        'slug' => $slug,
                        'excerpt' => $form['excerpt'] !== '' ? $form['excerpt'] : null,
                        'content' => $form['content'],
                        'hero_image' => $form['hero_image'],
                        'sort_order' => (int) $form['sort_order'],
                        'is_enabled' => (int) $form['is_enabled'],
                    ]);
                    set_flash('success', 'Custom page updated.');
                } else {
                    $stmt = db()->prepare(
                        'INSERT INTO custom_pages (title, slug, excerpt, content, hero_image, sort_order, is_enabled)
                         VALUES (:title, :slug, :excerpt, :content, :hero_image, :sort_order, :is_enabled)'
                    );
                    $stmt->execute([
                        'title' => $form['title'],
                        'slug' => $slug,
                        'excerpt' => $form['excerpt'] !== '' ? $form['excerpt'] : null,
                        'content' => $form['content'],
                        'hero_image' => $form['hero_image'],
                        'sort_order' => (int) $form['sort_order'],
                        'is_enabled' => (int) $form['is_enabled'],
                    ]);
                    set_flash('success', 'Custom page created.');
                }

                redirect('admin/custom_pages.php');
            }
        }
    } catch (Throwable $exception) {
        $errors[] = $exception->getMessage();
    }
}

if ($extensionsReady && empty($errors)) {
    $editId = (int) ($_GET['id'] ?? 0);
    if ($editId > 0) {
        $editPage = get_custom_page_by_id($editId);
        if ($editPage) {
            $form = [
                'id' => (int) $editPage['id'],
                'title' => (string) $editPage['title'],
                'slug' => (string) $editPage['slug'],
                'excerpt' => (string) ($editPage['excerpt'] ?? ''),
                'content' => (string) $editPage['content'],
                'hero_image' => $editPage['hero_image'],
                'sort_order' => (int) $editPage['sort_order'],
                'is_enabled' => (int) $editPage['is_enabled'],
            ];
        }
    }
}

$pages = $extensionsReady ? get_custom_pages(false) : [];

include __DIR__ . '/../includes/admin_header.php';
?>

<?php if (!$extensionsReady): ?>
    <div class="alert alert-danger mb-0">
        Unable to initialize custom page tables. Check database permissions.
    </div>
<?php else: ?>
    <div class="row g-4">
        <div class="col-xl-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2 class="h5 mb-3"><?= (int) $form['id'] > 0 ? 'Edit Page' : 'Create New Page' ?></h2>

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
                        <input type="hidden" name="action" value="save">
                        <input type="hidden" name="page_id" value="<?= e((string) $form['id']) ?>">

                        <div class="mb-3">
                            <label class="form-label">Page Title</label>
                            <input type="text" name="title" class="form-control" required value="<?= e((string) $form['title']) ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Slug (optional)</label>
                            <input type="text" name="slug" class="form-control" value="<?= e((string) $form['slug']) ?>" placeholder="example-page">
                            <small class="text-body-secondary">If empty, slug is auto-generated from title.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Short Excerpt</label>
                            <textarea name="excerpt" rows="3" class="form-control"><?= e((string) $form['excerpt']) ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Page Content</label>
                            <textarea name="content" rows="8" class="form-control" required><?= e((string) $form['content']) ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Hero Image (optional)</label>
                            <input type="file" name="hero_image" class="form-control" accept=".jpg,.jpeg,.png,.webp,.gif">
                        </div>

                        <?php if (!empty($form['hero_image'])): ?>
                            <div class="mb-3">
                                <img src="<?= e(url((string) $form['hero_image'])) ?>" alt="Hero image" class="img-fluid rounded border mb-2" style="max-height: 140px;">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="remove_image" name="remove_image">
                                    <label class="form-check-label" for="remove_image">Remove current image</label>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label">Sort Order</label>
                                <input type="number" name="sort_order" class="form-control" min="1" value="<?= e((string) $form['sort_order']) ?>">
                            </div>
                            <div class="col-6 d-flex align-items-end">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="is_enabled" name="is_enabled" <?= (int) $form['is_enabled'] === 1 ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_enabled">Enabled</label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button class="btn btn-primary" type="submit"><?= (int) $form['id'] > 0 ? 'Update Page' : 'Create Page' ?></button>
                            <?php if ((int) $form['id'] > 0): ?>
                                <a href="<?= e(url('admin/custom_pages.php')) ?>" class="btn btn-outline-secondary">Cancel Edit</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2 class="h5 mb-3">All Custom Pages</h2>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Slug</th>
                                    <th>URL</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!$pages): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-body-secondary">No custom pages created yet.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($pages as $page): ?>
                                        <tr>
                                            <td><?= e($page['title']) ?></td>
                                            <td><code><?= e($page['slug']) ?></code></td>
                                            <td><a href="<?= e(url('page.php?slug=' . urlencode((string) $page['slug']))) ?>" target="_blank"><?= e(url('page.php?slug=' . (string) $page['slug'])) ?></a></td>
                                            <td>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                                    <input type="hidden" name="action" value="toggle">
                                                    <input type="hidden" name="page_id" value="<?= e((string) $page['id']) ?>">
                                                    <input type="hidden" name="enabled" value="<?= (int) $page['is_enabled'] === 1 ? '0' : '1' ?>">
                                                    <button type="submit" class="btn btn-sm <?= (int) $page['is_enabled'] === 1 ? 'btn-outline-success' : 'btn-outline-secondary' ?>">
                                                        <?= (int) $page['is_enabled'] === 1 ? 'Enabled' : 'Disabled' ?>
                                                    </button>
                                                </form>
                                            </td>
                                            <td class="d-flex gap-2">
                                                <a href="<?= e(url('admin/custom_pages.php?id=' . (string) $page['id'])) ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="page_id" value="<?= e((string) $page['id']) ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" data-confirm="Delete this page?">Delete</button>
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
        </div>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
