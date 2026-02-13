<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';
require_admin();

$pages = cms_pages();
$currentPage = (string) ($_GET['page'] ?? 'home');
if (!array_key_exists($currentPage, $pages)) {
    $currentPage = 'home';
}

if (is_post()) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        set_flash('error', 'Invalid request token.');
        redirect('admin/pages.php?page=' . urlencode($currentPage));
    }

    $action = (string) ($_POST['action'] ?? '');
    $sectionId = (int) ($_POST['section_id'] ?? 0);

    if ($sectionId > 0 && $action === 'toggle') {
        $enabled = (int) ($_POST['enabled'] ?? 0) === 1 ? 1 : 0;
        $stmt = db()->prepare('UPDATE page_sections SET is_enabled = :enabled WHERE id = :id');
        $stmt->execute(['enabled' => $enabled, 'id' => $sectionId]);
        set_flash('success', 'Section visibility updated.');
    }

    redirect('admin/pages.php?page=' . urlencode($currentPage));
}

$stmt = db()->prepare(
    'SELECT id, page_slug, section_key, title, content, image_path, is_enabled, sort_order
     FROM page_sections
     WHERE page_slug = :page_slug
     ORDER BY sort_order ASC, id ASC'
);
$stmt->execute(['page_slug' => $currentPage]);
$sections = $stmt->fetchAll();

$pageTitle = 'Page Content';
include __DIR__ . '/../includes/admin_header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div class="btn-group" role="group">
        <?php foreach ($pages as $slug => $label): ?>
            <a href="<?= e(url('admin/pages.php?page=' . urlencode($slug))) ?>" class="btn <?= $slug === $currentPage ? 'btn-primary' : 'btn-outline-primary' ?>">
                <?= e($label) ?>
            </a>
        <?php endforeach; ?>
    </div>
    <a href="<?= e(url('admin/edit_section.php?page=' . urlencode($currentPage))) ?>" class="btn btn-success">
        <i class="bi bi-plus-lg"></i> Add Section
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <h2 class="h5 mb-3"><?= e($pages[$currentPage]) ?> Sections</h2>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Section Key</th>
                        <th>Title</th>
                        <th>Content Preview</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$sections): ?>
                        <tr>
                            <td colspan="6" class="text-center text-body-secondary">No sections found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($sections as $section): ?>
                            <tr>
                                <td><?= e((string) $section['sort_order']) ?></td>
                                <td><code><?= e($section['section_key']) ?></code></td>
                                <td><?= e($section['title']) ?></td>
                                <td><?= e(mb_strimwidth($section['content'], 0, 80, '...')) ?></td>
                                <td>
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="action" value="toggle">
                                        <input type="hidden" name="section_id" value="<?= e((string) $section['id']) ?>">
                                        <input type="hidden" name="enabled" value="<?= (int) $section['is_enabled'] === 1 ? '0' : '1' ?>">
                                        <button class="btn btn-sm <?= (int) $section['is_enabled'] === 1 ? 'btn-outline-success' : 'btn-outline-secondary' ?>" type="submit">
                                            <?= (int) $section['is_enabled'] === 1 ? 'Enabled' : 'Disabled' ?>
                                        </button>
                                    </form>
                                </td>
                                <td class="d-flex gap-2">
                                    <a href="<?= e(url('admin/edit_section.php?id=' . (string) $section['id'])) ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form method="post" action="<?= e(url('admin/delete_section.php')) ?>" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="section_id" value="<?= e((string) $section['id']) ?>">
                                        <input type="hidden" name="page" value="<?= e($currentPage) ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" data-confirm="Delete this section?">Delete</button>
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
