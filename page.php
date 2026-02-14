<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$slug = trim((string) ($_GET['slug'] ?? ''));
$customPage = $slug !== '' ? get_custom_page_by_slug($slug, true) : null;

if (!$customPage) {
    http_response_code(404);
    $pageTitle = 'Page Not Found';
    $metaDescription = 'The requested page is unavailable.';
    include __DIR__ . '/includes/header.php';
    ?>
    <section class="container">
        <div class="content-card">
            <h1 class="h3">Page not found</h1>
            <p class="mb-3">The page you requested is unavailable or disabled.</p>
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

include __DIR__ . '/includes/header.php';
?>

<section class="hero-shell py-5">
    <div class="container">
        <div class="hero-box">
            <p class="hero-kicker">Custom Page</p>
            <h1 class="hero-title"><?= e($customPage['title']) ?></h1>
            <?php if (!empty($customPage['excerpt'])): ?>
                <p class="hero-copy mb-0"><?= e((string) $customPage['excerpt']) ?></p>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="container">
    <article class="content-card">
        <?php if (!empty($customPage['hero_image'])): ?>
            <img src="<?= e(url((string) $customPage['hero_image'])) ?>" alt="<?= e($customPage['title']) ?>" class="section-image mb-4">
        <?php endif; ?>
        <div class="page-content"><?= nl2br(e((string) $customPage['content'])) ?></div>
    </article>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
