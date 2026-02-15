<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$fallbackImage = 'uploads/gallery/d8a8391c03b184b635c368e36b6dbff3.jpg';
$slug = trim((string) ($_GET['slug'] ?? ''));
$customPage = $slug !== '' ? get_custom_page_by_slug($slug, true) : null;

if (!$customPage) {
    http_response_code(404);
    $pageTitle = 'Page Not Found';
    $metaDescription = 'The requested page is unavailable.';
    include __DIR__ . '/includes/header.php';
    ?>
    <section class="container mb-4">
        <div class="facility-page-intro reveal">
            <p class="facility-intro-kicker mb-2">404</p>
            <h1 class="mb-3">Page Not Found</h1>
            <p class="mb-0">The page you requested is unavailable or disabled.</p>
        </div>
    </section>
    <section class="container">
        <div class="content-card reveal">
            <a href="<?= e(url('index.php')) ?>" class="btn btn-primary">Back to Home</a>
        </div>
    </section>
    <?php
    include __DIR__ . '/includes/footer.php';
    return;
}

$pageTitle = (string) $customPage['title'];
$metaDescription = (string) ($customPage['excerpt'] ?? 'Dynamic custom page');
$hideDefaultHero = true;
$heroImage = !empty($customPage['hero_image']) ? (string) $customPage['hero_image'] : $fallbackImage;

include __DIR__ . '/includes/header.php';
?>

<section class="container mb-5">
    <div class="facility-page-intro reveal">
        <p class="facility-intro-kicker mb-2">Custom Page</p>
        <h1 class="mb-3"><?= e($customPage['title']) ?></h1>
        <?php if (!empty($customPage['excerpt'])): ?>
            <p class="mb-0"><?= e((string) $customPage['excerpt']) ?></p>
        <?php else: ?>
            <p class="mb-0">Explore the full details shared on this page.</p>
        <?php endif; ?>
    </div>
</section>

<section class="container mb-4">
    <article class="welcome-about-card reveal">
        <div class="row g-0 align-items-stretch">
            <div class="col-lg-5">
                <div class="welcome-about-media h-100">
                    <img src="<?= e(url($heroImage)) ?>" alt="<?= e($customPage['title']) ?>">
                </div>
            </div>
            <div class="col-lg-7">
                <div class="welcome-about-content">
                    <div class="page-content"><?= nl2br(e((string) $customPage['content'])) ?></div>
                </div>
            </div>
        </div>
    </article>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
