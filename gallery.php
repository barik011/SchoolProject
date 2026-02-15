<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Gallery';
$metaDescription = 'School gallery featuring academics, celebrations, sports, and campus life.';
$images = array_values(array_filter(get_gallery_images(), fn(array $i): bool => (int) $i['is_active'] === 1));

$fallbackImages = [
    ['title' => 'Classroom Learning Session', 'image_path' => 'uploads/gallery/d8a8391c03b184b635c368e36b6dbff3.jpg'],
    ['title' => 'School Sports Activities', 'image_path' => 'uploads/gallery/c6c6c1d60fae4dc1174559e9459936bf.jpg'],
    ['title' => 'Cultural Event Highlights', 'image_path' => 'uploads/gallery/2141b8f4fc1cb49ae649900bd9ef58bd.jpg'],
];

$galleryItems = [];
if (!$images) {
    foreach ($fallbackImages as $item) {
        $galleryItems[] = [
            'title' => $item['title'],
            'image_path' => $item['image_path'],
        ];
    }
} else {
    foreach ($images as $image) {
        $galleryItems[] = [
            'title' => (string) $image['title'],
            'image_path' => (string) $image['image_path'],
        ];
    }
}

include __DIR__ . '/includes/header.php';
?>

<section class="container mb-4">
    <div class="facility-page-intro reveal">
        <p class="facility-intro-kicker mb-2">School Life</p>
        <h1 class="mb-3">Gallery</h1>
        <p class="mb-0">Explore moments from classrooms, events, activities, and student achievements across our campus journey.</p>
    </div>
</section>

<section class="container mb-5">
    <?php if (!$images): ?>
        <div class="alert alert-info reveal">Showing sample gallery items. Add your own images from <strong>Admin Panel - Gallery</strong>.</div>
    <?php endif; ?>

    <div class="gallery-grid">
        <?php foreach ($galleryItems as $index => $image): ?>
            <?php $imageUrl = url((string) $image['image_path']); ?>
            <article class="gallery-item reveal">
                <a href="<?= e($imageUrl) ?>" class="gallery-lightbox-trigger" data-gallery-lightbox="true" data-title="<?= e($image['title']) ?>" data-index="<?= e((string) $index) ?>">
                    <img src="<?= e($imageUrl) ?>" alt="<?= e($image['title']) ?>">
                </a>
                <div class="caption"><?= e($image['title']) ?></div>
            </article>
        <?php endforeach; ?>
    </div>

    <div class="gallery-lightbox" id="galleryLightbox" aria-hidden="true">
        <button type="button" class="gallery-lightbox-close" id="galleryLightboxClose" aria-label="Close image preview">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <figure class="gallery-lightbox-figure">
            <img src="" alt="" id="galleryLightboxImage">
            <figcaption id="galleryLightboxCaption"></figcaption>
        </figure>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
