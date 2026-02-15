<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Home';
$metaDescription = 'Discover our academic environment, values, facilities, and admissions.';
$hideDefaultHero = true;
$banners = get_home_banners();
$schoolName = get_setting('school_name', 'Greenfield Public School');
$homeSections = array_values(array_filter(get_sections('home'), fn(array $s): bool => (int) $s['is_enabled'] === 1));
$sections = count($homeSections) > 1 ? array_slice($homeSections, 1) : [];
$facilitySections = array_values(array_filter(get_sections('facilities'), fn(array $s): bool => (int) $s['is_enabled'] === 1));

$fallbackImages = [
    'uploads/gallery/d8a8391c03b184b635c368e36b6dbff3.jpg',
    'uploads/gallery/c6c6c1d60fae4dc1174559e9459936bf.jpg',
    'uploads/gallery/2141b8f4fc1cb49ae649900bd9ef58bd.jpg',
];

$homeFeatures = [
    ['icon' => 'fa-solid fa-graduation-cap', 'title' => 'Academic Excellence', 'text' => 'Structured learning with strong focus on outcomes and concept clarity.'],
    ['icon' => 'fa-solid fa-futbol', 'title' => 'Sports and Activities', 'text' => 'Balanced curriculum with sports, clubs, arts, and annual celebrations.'],
    ['icon' => 'fa-solid fa-user-shield', 'title' => 'Safe Campus', 'text' => 'Monitored and student-friendly campus with caring teachers and support staff.'],
    ['icon' => 'fa-solid fa-chalkboard-user', 'title' => 'Mentor Support', 'text' => 'Dedicated faculty mentorship and guidance for each child\'s growth journey.'],
];

$campusMoments = [
    ['title' => 'Hands-on Science Experiments', 'image' => $fallbackImages[0]],
    ['title' => 'Sports Day and Team Spirit', 'image' => $fallbackImages[1]],
    ['title' => 'Creative Arts and Events', 'image' => $fallbackImages[2]],
];

$defaultFacilities = [
    [
        'title' => 'Smart Classrooms',
        'content' => 'Interactive boards, digital resources, and student-centered learning spaces for every grade level.',
        'image' => $fallbackImages[0],
        'anchor' => 'facility-smart-classrooms',
    ],
    [
        'title' => 'Science and Computer Labs',
        'content' => 'Hands-on practical labs designed for experimentation, coding, and applied STEM learning.',
        'image' => $fallbackImages[1],
        'anchor' => 'facility-science-and-computer-labs',
    ],
    [
        'title' => 'Sports and Activity Zone',
        'content' => 'Dedicated grounds and activity spaces to support fitness, teamwork, discipline, and confidence.',
        'image' => $fallbackImages[2],
        'anchor' => 'facility-sports-and-activity-zone',
    ],
];

$welcomeSection = $homeSections[0] ?? null;
$welcomeTitle = $welcomeSection['title'] ?? ('Welcome to ' . $schoolName);
$welcomeContent = $welcomeSection['content'] ?? 'Our school empowers students through modern learning, strong values, and vibrant co-curricular opportunities. We help every child discover confidence, creativity, and leadership in a safe campus environment.';
$welcomeImage = !empty($welcomeSection['image_path']) ? (string) $welcomeSection['image_path'] : $fallbackImages[0];

$homeFacilities = [];
if (!$facilitySections) {
    $homeFacilities = $defaultFacilities;
} else {
    foreach ($facilitySections as $index => $facility) {
        $anchorBase = trim((string) ($facility['section_key'] ?? '')) !== '' ? (string) $facility['section_key'] : (string) $facility['title'];
        $homeFacilities[] = [
            'title' => (string) $facility['title'],
            'content' => (string) $facility['content'],
            'image' => !empty($facility['image_path']) ? (string) $facility['image_path'] : $fallbackImages[$index % count($fallbackImages)],
            'anchor' => 'facility-' . slugify($anchorBase),
        ];
    }
}

include __DIR__ . '/includes/header.php';
?>

<section class="home-banner-wrap mb-5">
    <?php if (!$banners): ?>
        <div class="hero-fallback py-5">
            <div class="container">
                <div class="row g-4 align-items-center">
                    <div class="col-lg-6 reveal">
                        <div class="hero-fallback-copy">
                            <p class="hero-chip mb-3"><i class="fa-solid fa-bullhorn me-2"></i>Admissions Open</p>
                            <h1 class="display-5 fw-bold mb-3">A modern school experience for every learner</h1>
                            <p class="lead text-body-secondary mb-4">Strong academics, value-based education, sports, and real growth opportunities in one campus.</p>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="<?= e(url('admission.php')) ?>" class="btn btn-primary">
                                    <i class="fa-solid fa-file-signature me-2"></i>Start Admission Inquiry
                                </a>
                                <a href="<?= e(url('contact.php')) ?>" class="btn btn-outline-primary">
                                    <i class="fa-regular fa-calendar-check me-2"></i>Book Campus Visit
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 reveal">
                        <div class="hero-fallback-media">
                            <img src="<?= e(url($fallbackImages[0])) ?>" alt="Students learning in class" class="img-fluid">
                        </div>
                    </div>
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
                                    <p class="banner-pill"><i class="fa-solid fa-star me-2"></i>Trusted by Families</p>
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
    <article class="welcome-about-card reveal">
        <div class="row g-0 align-items-stretch">
            <div class="col-lg-5">
                <div class="welcome-about-media h-100">
                    <img src="<?= e(url($welcomeImage)) ?>" alt="<?= e($welcomeTitle) ?>">
                </div>
            </div>
            <div class="col-lg-7">
                <div class="welcome-about-content">
                    <p class="welcome-tag mb-2">Welcome</p>
                    <h2 class="mb-3"><?= e($welcomeTitle) ?></h2>
                    <p class="mb-4"><?= e(mb_strimwidth(strip_tags((string) $welcomeContent), 0, 360, '...')) ?></p>
                    <a href="<?= e(url('about.php')) ?>" class="btn btn-primary">
                        Read More About Us
                        <i class="fa-solid fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </article>
</section>

<section class="container mb-5">
    <div class="row g-4">
        <div class="col-md-4 reveal">
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-user-graduate"></i></div>
                <div class="small text-body-secondary">Students</div>
                <div class="display-6 fw-bold">800+</div>
                <div class="small text-body-secondary">from nursery to senior secondary</div>
            </div>
        </div>
        <div class="col-md-4 reveal">
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-chalkboard-user"></i></div>
                <div class="small text-body-secondary">Faculty</div>
                <div class="display-6 fw-bold">65+</div>
                <div class="small text-body-secondary">experienced teachers and mentors</div>
            </div>
        </div>
        <div class="col-md-4 reveal">
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-building"></i></div>
                <div class="small text-body-secondary">Campus</div>
                <div class="display-6 fw-bold">4 Acres</div>
                <div class="small text-body-secondary">safe, green, and modern infrastructure</div>
            </div>
        </div>
    </div>
</section>

<section class="container mb-5">
    <div class="section-head d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h2 class="h3 mb-0">Campus Facilities</h2>
        <a href="<?= e(url('facilities.php')) ?>" class="btn btn-sm btn-outline-primary">
            <i class="fa-solid fa-arrow-right me-2"></i>See All Facilities
        </a>
    </div>
    <div class="row g-4">
        <?php foreach ($homeFacilities as $facility): ?>
            <div class="col-md-6 col-xl-4 reveal">
                <article class="facility-card h-100">
                    <a href="<?= e(url('facilities.php#' . $facility['anchor'])) ?>" class="facility-card-link">
                        <img src="<?= e(url($facility['image'])) ?>" alt="<?= e($facility['title']) ?>">
                        <div class="p-3">
                            <h3 class="h5 mb-2"><?= e($facility['title']) ?></h3>
                            <p class="mb-2"><?= e(mb_strimwidth(strip_tags((string) $facility['content']), 0, 140, '...')) ?></p>
                            <span class="facility-more">Explore Details <i class="fa-solid fa-arrow-right ms-1"></i></span>
                        </div>
                    </a>
                </article>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="container mb-5">
    <div class="section-head d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h2 class="h3 mb-0">Why Families Choose Us</h2>
    </div>
    <div class="row g-4">
        <?php foreach ($homeFeatures as $feature): ?>
            <div class="col-md-6 col-xl-3 reveal">
                <article class="content-card feature-card h-100">
                    <div class="feature-icon"><i class="<?= e($feature['icon']) ?>"></i></div>
                    <h3 class="h5 mb-2"><?= e($feature['title']) ?></h3>
                    <p class="mb-0"><?= e($feature['text']) ?></p>
                </article>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="container">
    <div class="section-head d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h2 class="h3 mb-0">What We Offer</h2>
        <a href="<?= e(url('about.php')) ?>" class="btn btn-sm btn-outline-primary">
            <i class="fa-solid fa-arrow-right me-2"></i>Learn More
        </a>
    </div>
    <div class="row g-4">
        <?php if (!$sections): ?>
            <?php
            $defaultSections = [
                ['title' => 'Strong Academic Foundation', 'content' => 'Our curriculum helps students build confidence, curiosity, and clear fundamentals at every stage.'],
                ['title' => 'Holistic Student Development', 'content' => 'Clubs, sports, projects, and leadership activities prepare students for real-world growth.'],
            ];
            ?>
            <?php foreach ($defaultSections as $index => $section): ?>
                <div class="col-lg-6 reveal">
                    <article class="content-card section-card h-100">
                        <img src="<?= e(url($fallbackImages[$index % count($fallbackImages)])) ?>" alt="<?= e($section['title']) ?>" class="section-image mb-3">
                        <h3 class="h4"><?= e($section['title']) ?></h3>
                        <p class="mb-0"><?= e($section['content']) ?></p>
                    </article>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <?php foreach ($sections as $index => $section): ?>
                <?php $sectionImage = !empty($section['image_path']) ? (string) $section['image_path'] : $fallbackImages[$index % count($fallbackImages)]; ?>
                <div class="col-lg-6 reveal">
                    <article class="content-card section-card h-100">
                        <img src="<?= e(url($sectionImage)) ?>" alt="<?= e($section['title']) ?>" class="section-image mb-3">
                        <h3 class="h4"><?= e($section['title']) ?></h3>
                        <p class="mb-0"><?= nl2br(e($section['content'])) ?></p>
                    </article>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<section class="container mt-5">
    <div class="section-head d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h2 class="h3 mb-0">Campus Moments</h2>
        <a href="<?= e(url('gallery.php')) ?>" class="btn btn-sm btn-outline-primary">
            <i class="fa-regular fa-images me-2"></i>View Gallery
        </a>
    </div>
    <div class="row g-4">
        <?php foreach ($campusMoments as $moment): ?>
            <div class="col-md-4 reveal">
                <article class="home-moment-card h-100">
                    <img src="<?= e(url($moment['image'])) ?>" alt="<?= e($moment['title']) ?>">
                    <div class="p-3">
                        <p class="mb-0 fw-semibold"><?= e($moment['title']) ?></p>
                    </div>
                </article>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="container mt-5">
    <div class="row g-4">
        <div class="col-md-6 reveal">
            <div class="content-card h-100">
                <h3 class="h4"><i class="fa-solid fa-file-signature text-primary me-2"></i>Admissions Open</h3>
                <p>Submit an inquiry to connect with our admission team and explore the right class for your child.</p>
                <a href="<?= e(url('admission.php')) ?>" class="btn btn-primary">Start Inquiry</a>
            </div>
        </div>
        <div class="col-md-6 reveal">
            <div class="content-card h-100">
                <h3 class="h4"><i class="fa-regular fa-calendar-check text-primary me-2"></i>Visit the Campus</h3>
                <p>Book a campus visit and experience our learning environment, labs, and co-curricular spaces.</p>
                <a href="<?= e(url('contact.php')) ?>" class="btn btn-outline-primary">Contact Us</a>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
