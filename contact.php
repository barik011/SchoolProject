<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Contact Us';
$metaDescription = 'Reach out to our school office for admissions, academics, and general queries.';
$errors = [];
$input = [
    'name' => '',
    'email' => '',
    'message' => '',
];

$fallbackImages = [
    'uploads/gallery/d8a8391c03b184b635c368e36b6dbff3.jpg',
    'uploads/gallery/c6c6c1d60fae4dc1174559e9459936bf.jpg',
];

if (is_post()) {
    $input = [
        'name' => trim((string) ($_POST['name'] ?? '')),
        'email' => trim((string) ($_POST['email'] ?? '')),
        'message' => trim((string) ($_POST['message'] ?? '')),
    ];

    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid request token. Please refresh and try again.';
    }
    if ($input['name'] === '' || mb_strlen($input['name']) < 2) {
        $errors[] = 'Please enter your name.';
    }
    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email.';
    }
    if ($input['message'] === '' || mb_strlen($input['message']) < 10) {
        $errors[] = 'Please enter a message with at least 10 characters.';
    }

    if (!$errors) {
        $stmt = db()->prepare('INSERT INTO contact_messages (name, email, message) VALUES (:name, :email, :message)');
        $stmt->execute($input);
        set_flash('success', 'Thank you for contacting us. We will respond shortly.');
        redirect('contact.php');
    }
}

include __DIR__ . '/includes/header.php';
?>

<section class="container mb-5">
    <div class="facility-page-intro reveal">
        <p class="facility-intro-kicker mb-2">Connect With Us</p>
        <h1 class="mb-3">Contact Us</h1>
        <p class="mb-0">Get in touch with our school office for admissions, academic support, and general information. Our team will be happy to assist you.</p>
    </div>
</section>

<section class="container mb-4">
    <div class="row g-4">
        <div class="col-lg-5 reveal">
            <article class="contact-info-card h-100">
                <div class="contact-info-media">
                    <img src="<?= e(url($fallbackImages[0])) ?>" alt="School campus front view">
                </div>
                <div class="p-3 p-lg-4">
                    <h2 class="h4 mb-3"><i class="fa-solid fa-building me-2 text-primary"></i>School Office</h2>
                    <ul class="contact-list mb-4">
                        <li><i class="fa-solid fa-phone"></i><span><?= e(get_setting('contact_phone', '+1 000-000-0000')) ?></span></li>
                        <li><i class="fa-regular fa-envelope"></i><span><?= e(get_setting('contact_email', 'info@school.edu')) ?></span></li>
                        <li><i class="fa-solid fa-location-dot"></i><span><?= e(get_setting('contact_address', 'School Campus Address')) ?></span></li>
                        <li><i class="fa-regular fa-clock"></i><span>Monday - Friday, 8:30 AM to 4:00 PM</span></li>
                    </ul>
                    <div class="contact-mini-note">
                        <i class="fa-solid fa-headset me-2"></i>Admissions and office team typically respond within one business day.
                    </div>
                </div>
            </article>
        </div>

        <div class="col-lg-7 reveal">
            <div class="inquiry-panel h-100">
                <h2 class="h4 mb-3">Send Us a Message</h2>
                <?php if ($errors): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?= e($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <form method="post" class="needs-validation" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" required minlength="2" value="<?= e($input['name']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required value="<?= e($input['email']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea name="message" rows="6" class="form-control" required minlength="10"><?= e($input['message']) ?></textarea>
                    </div>
                    <button class="btn btn-primary" type="submit">
                        <i class="fa-regular fa-paper-plane me-2"></i>Send Message
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
