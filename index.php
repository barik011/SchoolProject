<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Home';
$metaDescription = 'Discover our academic environment, values, facilities, and admissions.';
$hideDefaultHero = true;
$banners = get_home_banners();
$sections = array_values(array_filter(get_sections('home'), fn(array $s): bool => (int) $s['is_enabled'] === 1));

include __DIR__ . '/includes/header.php';
?>

<section class="home-banner-wrap mb-5">
    <?php if (!$banners): ?>
        <div class="home-banner-empty">
            <div class="container">
                <div class="home-banner-empty-card">
                    <p class="small text-uppercase fw-bold mb-2">Home Banner</p>
                    <h2 class="mb-2">Add your first homepage slider image</h2>
                    <p class="mb-0">Go to Admin Panel -> Home Banners to upload slides with title and subtitle.</p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div id="homeBannerCarousel" class="carousel slide carousel-fade home-banner-carousel" data-bs-ride="carousel" data-bs-interval="4500">
            <div class="carousel-indicators">
                <?php foreach ($banners as $index => $banner): ?>
                    <button type="button" data-bs-target="#homeBannerCarousel" data-bs-slide-to="<?= e((string) $index) ?>" class="<?= $index === 0 ? 'active' : '' ?>" aria-current="<?= $index === 0 ? 'true' : 'false' ?>" aria-label="Slide <?= e((string) ($index + 1)) ?>"></button>
                <?php endforeach; ?>
            </div>

            <div class="carousel-inner">
                <?php foreach ($banners as $index => $banner): ?>
                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                        <img src="<?= e(url((string) $banner['image_path'])) ?>" class="d-block w-100 home-banner-image" alt="<?= e($banner['title']) ?>">
                        <div class="home-banner-overlay"></div>
                        <div class="carousel-caption home-banner-caption">
                            <div class="container">
                                <div class="home-banner-content<?= $index === 0 ? ' is-visible' : '' ?>">
                                    <h2 class="display-5 fw-bold"><?= e($banner['title']) ?></h2>
                                    <?php if (!empty($banner['subtitle'])): ?>
                                        <p class="lead mb-0"><?= e($banner['subtitle']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <button class="carousel-control-prev" type="button" data-bs-target="#homeBannerCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#homeBannerCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    <?php endif; ?>
</section>

<section class="container mb-5">
    <div class="row g-4">
        <div class="col-md-4 reveal">
            <div class="stat-card">
                <div class="small text-body-secondary">Students</div>
                <div class="display-6 fw-bold">800+</div>
                <div class="small text-body-secondary">from nursery to senior secondary</div>
            </div>
        </div>
        <div class="col-md-4 reveal">
            <div class="stat-card">
                <div class="small text-body-secondary">Faculty</div>
                <div class="display-6 fw-bold">65+</div>
                <div class="small text-body-secondary">experienced teachers and mentors</div>
            </div>
        </div>
        <div class="col-md-4 reveal">
            <div class="stat-card">
                <div class="small text-body-secondary">Campus</div>
                <div class="display-6 fw-bold">4 Acres</div>
                <div class="small text-body-secondary">safe, green, and modern infrastructure</div>
            </div>
        </div>
    </div>
</section>

<section class="container">
    <div class="row g-4">
        <?php if (!$sections): ?>
            <div class="col-12">
                <div class="content-card">
                    <h2>Welcome to our school</h2>
                    <p class="mb-0">Add homepage sections from the admin panel to display dynamic content here.</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($sections as $section): ?>
                <div class="col-lg-6 reveal">
                    <article class="content-card h-100">
                        <h3 class="h4"><?= e($section['title']) ?></h3>
                        <p><?= nl2br(e($section['content'])) ?></p>
                        <?php if (!empty($section['image_path'])): ?>
                            <img src="<?= e(url((string) $section['image_path'])) ?>" alt="<?= e($section['title']) ?>" class="section-image mt-2">
                        <?php endif; ?>
                    </article>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<section class="container mt-5">
    <div class="row g-4">
        <div class="col-md-6 reveal">
            <div class="content-card h-100">
                <h3 class="h4">Admissions Open</h3>
                <p>Submit an inquiry to connect with our admission team and explore the right class for your child.</p>
                <a href="<?= e(url('admission.php')) ?>" class="btn btn-primary">Start Inquiry</a>
            </div>
        </div>
        <div class="col-md-6 reveal">
            <div class="content-card h-100">
                <h3 class="h4">Visit the Campus</h3>
                <p>Book a campus visit and experience our learning environment, labs, and co-curricular spaces.</p>
                <a href="<?= e(url('contact.php')) ?>" class="btn btn-outline-primary">Contact Us</a>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
