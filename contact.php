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

<section class="container">
    <div class="row g-4">
        <div class="col-lg-5 reveal">
            <div class="content-card h-100">
                <h2 class="h4">School Office</h2>
                <p class="mb-2"><strong>Phone:</strong> <?= e(get_setting('contact_phone', '+1 000-000-0000')) ?></p>
                <p class="mb-2"><strong>Email:</strong> <?= e(get_setting('contact_email', 'info@school.edu')) ?></p>
                <p class="mb-4"><strong>Address:</strong> <?= e(get_setting('contact_address', 'School Campus Address')) ?></p>
                <h3 class="h5">Office Hours</h3>
                <p class="mb-0">Monday - Friday, 8:30 AM to 4:00 PM</p>
            </div>
        </div>
        <div class="col-lg-7 reveal">
            <div class="inquiry-panel">
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
                        <textarea name="message" rows="5" class="form-control" required minlength="10"><?= e($input['message']) ?></textarea>
                    </div>
                    <button class="btn btn-primary" type="submit">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
