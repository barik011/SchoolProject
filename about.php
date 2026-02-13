<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'About the School';
$metaDescription = 'Know our vision, values, leadership, and educational approach.';
$sections = array_values(array_filter(get_sections('about'), fn(array $s): bool => (int) $s['is_enabled'] === 1));

include __DIR__ . '/includes/header.php';
?>

<section class="container">
    <div class="row g-4">
        <?php if (!$sections): ?>
            <div class="col-12">
                <div class="content-card">
                    <h2>About content coming soon</h2>
                    <p class="mb-0">Add about sections from the admin panel to personalize this page.</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($sections as $section): ?>
                <div class="col-lg-6 reveal">
                    <article class="content-card h-100">
                        <h2 class="h4"><?= e($section['title']) ?></h2>
                        <?php if (!empty($section['image_path'])): ?>
                            <img src="<?= e(url((string) $section['image_path'])) ?>" alt="<?= e($section['title']) ?>" class="section-image mb-3">
                        <?php endif; ?>
                        <p class="mb-0"><?= nl2br(e($section['content'])) ?></p>
                    </article>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
