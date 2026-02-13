<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';
require_admin();

if (!is_post() || !verify_csrf_token($_POST['csrf_token'] ?? null)) {
    set_flash('error', 'Invalid request.');
    redirect('admin/pages.php');
}

$sectionId = (int) ($_POST['section_id'] ?? 0);
$page = trim((string) ($_POST['page'] ?? 'home'));

if ($sectionId <= 0) {
    set_flash('error', 'Invalid section selected.');
    redirect('admin/pages.php');
}

$stmt = db()->prepare('SELECT image_path, page_slug FROM page_sections WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $sectionId]);
$section = $stmt->fetch();

if (!$section) {
    set_flash('error', 'Section not found.');
    redirect('admin/pages.php?page=' . urlencode($page));
}

$delete = db()->prepare('DELETE FROM page_sections WHERE id = :id');
$delete->execute(['id' => $sectionId]);

if (!empty($section['image_path'])) {
    delete_file_if_exists($section['image_path']);
}

set_flash('success', 'Section deleted.');
redirect('admin/pages.php?page=' . urlencode((string) $section['page_slug']));
