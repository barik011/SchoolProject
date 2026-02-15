<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Infrastructure';
$metaDescription = 'Learn about classrooms, labs, libraries, sports areas, and campus safety systems.';
$sections = array_values(array_filter(get_sections('infrastructure'), fn(array $s): bool => (int) $s['is_enabled'] === 1));

$fallbackImages = [
    'uploads/gallery/d8a8391c03b184b635c368e36b6dbff3.jpg',
    'uploads/gallery/c6c6c1d60fae4dc1174559e9459936bf.jpg',
    'uploads/gallery/2141b8f4fc1cb49ae649900bd9ef58bd.jpg',
];

$defaultSections = [
    [
        'section_key' => 'safe_green_campus',
        'title' => 'Safe and Green Campus',
        'content' => 'A clean, secure, and eco-conscious campus with monitored entry points and learner-friendly open spaces.',
        'image_path' => $fallbackImages[0],
    ],
    [
        'section_key' => 'labs_and_learning_hubs',
        'title' => 'Labs and Learning Hubs',
        'content' => 'Well-planned science and computer labs with practical learning resources that foster innovation and curiosity.',
        'image_path' => $fallbackImages[1],
    ],
    [
        'section_key' => 'library_and_activity_spaces',
        'title' => 'Library and Activity Spaces',
        'content' => 'Dedicated reading, project, and activity zones to support collaboration, creativity, and independent thinking.',
        'image_path' => $fallbackImages[2],
    ],
];

$infrastructureItems = [];
if (!$sections) {
    foreach ($defaultSections as $section) {
        $section['anchor'] = 'infrastructure-' . slugify((string) $section['section_key']);
        $infrastructureItems[] = $section;
    }
} else {
    foreach ($sections as $index => $section) {
        $anchorBase = trim((string) ($section['section_key'] ?? '')) !== '' ? (string) $section['section_key'] : (string) $section['title'];
        $infrastructureItems[] = [
            'title' => (string) $section['title'],
            'content' => (string) $section['content'],
            'image_path' => !empty($section['image_path']) ? (string) $section['image_path'] : $fallbackImages[$index % count($fallbackImages)],
            'anchor' => 'infrastructure-' . slugify($anchorBase),
        ];
    }
}

include __DIR__ . '/includes/header.php';
?>

<section class="container mb-4">
    <div class="facility-page-intro reveal">
        <p class="facility-intro-kicker mb-2">Campus Infrastructure</p>
        <h1 class="mb-3">Infrastructure</h1>
        <p class="mb-0">Our infrastructure supports safe operations, practical learning, and student development with modern and well-maintained spaces.</p>
    </div>
</section>

<section class="container mb-5">
    <div class="row g-3">
        <?php foreach ($infrastructureItems as $item): ?>
            <div class="col-md-6 col-xl-4 reveal">
                <a href="#<?= e($item['anchor']) ?>" class="facility-jump-link">
                    <img src="<?= e(url((string) $item['image_path'])) ?>" alt="<?= e($item['title']) ?>">
                    <span><?= e($item['title']) ?></span>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="container mb-5">
    <?php foreach ($infrastructureItems as $index => $item): ?>
        <article class="facility-detail-block reveal" id="<?= e($item['anchor']) ?>">
            <div class="row g-4 align-items-center">
                <div class="col-lg-5 <?= $index % 2 === 1 ? 'order-lg-2' : '' ?>">
                    <div class="facility-detail-media">
                        <img src="<?= e(url((string) $item['image_path'])) ?>" alt="<?= e($item['title']) ?>">
                    </div>
                </div>
                <div class="col-lg-7 <?= $index % 2 === 1 ? 'order-lg-1' : '' ?>">
                    <div class="facility-detail-content">
                        <h2><?= e($item['title']) ?></h2>
                        <p class="mb-0"><?= nl2br(e($item['content'])) ?></p>
                    </div>
                </div>
            </div>
        </article>
    <?php endforeach; ?>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
