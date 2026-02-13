<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Gallery';
$metaDescription = 'School gallery featuring academics, celebrations, sports, and campus life.';
$images = array_values(array_filter(get_gallery_images(), fn(array $i): bool => (int) $i['is_active'] === 1));

include __DIR__ . '/includes/header.php';
?>

<section class="container">
    <?php if (!$images): ?>
        <div class="content-card">
            <h2>Gallery is being updated</h2>
            <p class="mb-0">Add images from the admin panel to showcase school activities.</p>
        </div>
    <?php else: ?>
        <div class="gallery-grid">
            <?php foreach ($images as $image): ?>
                <article class="gallery-item reveal">
                    <img src="<?= e(url((string) $image['image_path'])) ?>" alt="<?= e($image['title']) ?>">
                    <div class="caption"><?= e($image['title']) ?></div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
