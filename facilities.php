<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Facilities';
$metaDescription = 'Explore the school facilities designed for academics, sports, and holistic growth.';
$sections = array_values(array_filter(get_sections('facilities'), fn(array $s): bool => (int) $s['is_enabled'] === 1));

$fallbackImages = [
    'uploads/gallery/d8a8391c03b184b635c368e36b6dbff3.jpg',
    'uploads/gallery/c6c6c1d60fae4dc1174559e9459936bf.jpg',
    'uploads/gallery/2141b8f4fc1cb49ae649900bd9ef58bd.jpg',
];

$defaultFacilities = [
    [
        'section_key' => 'smart_classrooms',
        'title' => 'Smart Classrooms',
        'content' => 'Digitally enabled classrooms with interactive learning tools that encourage participation and concept clarity from early grades onward.',
        'image_path' => $fallbackImages[0],
    ],
    [
        'section_key' => 'science_and_innovation_labs',
        'title' => 'Science and Innovation Labs',
        'content' => 'Well-equipped labs for physics, chemistry, biology, and computer science where students engage in practical, skill-based learning.',
        'image_path' => $fallbackImages[1],
    ],
    [
        'section_key' => 'sports_and_activity_arena',
        'title' => 'Sports and Activity Arena',
        'content' => 'Dedicated indoor and outdoor spaces that support fitness, teamwork, leadership, and healthy competition across all age groups.',
        'image_path' => $fallbackImages[2],
    ],
];

$facilityItems = [];
if (!$sections) {
    foreach ($defaultFacilities as $facility) {
        $facility['anchor'] = 'facility-' . slugify((string) $facility['section_key']);
        $facilityItems[] = $facility;
    }
} else {
    foreach ($sections as $index => $section) {
        $anchorBase = trim((string) ($section['section_key'] ?? '')) !== '' ? (string) $section['section_key'] : (string) $section['title'];
        $facilityItems[] = [
            'section_key' => (string) ($section['section_key'] ?? ''),
            'title' => (string) $section['title'],
            'content' => (string) $section['content'],
            'image_path' => !empty($section['image_path']) ? (string) $section['image_path'] : $fallbackImages[$index % count($fallbackImages)],
            'anchor' => 'facility-' . slugify($anchorBase),
        ];
    }
}

include __DIR__ . '/includes/header.php';
?>

<section class="container mb-4">
    <div class="facility-page-intro reveal">
        <p class="facility-intro-kicker mb-2">Our Campus</p>
        <h1 class="mb-3">Facilities</h1>
        <p class="mb-0">Our campus is designed to support academics, sports, creativity, and student well-being through modern, student-friendly infrastructure.</p>
    </div>
</section>

<section class="container mb-5">
    <div class="row g-3">
        <?php foreach ($facilityItems as $item): ?>
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
    <div class="facility-detail-wrap">
        <?php foreach ($facilityItems as $index => $item): ?>
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
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
