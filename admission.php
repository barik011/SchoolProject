<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Admission Inquiry';
$metaDescription = 'Submit your admission inquiry and our team will get in touch shortly.';
$errors = [];
$input = [
    'student_name' => '',
    'parent_name' => '',
    'class_applying' => '',
    'mobile' => '',
    'email' => '',
    'address' => '',
    'message' => '',
];

$fallbackImages = [
    'uploads/gallery/2141b8f4fc1cb49ae649900bd9ef58bd.jpg',
    'uploads/gallery/d8a8391c03b184b635c368e36b6dbff3.jpg',
];

if (is_post()) {
    $input = [
        'student_name' => trim((string) ($_POST['student_name'] ?? '')),
        'parent_name' => trim((string) ($_POST['parent_name'] ?? '')),
        'class_applying' => trim((string) ($_POST['class_applying'] ?? '')),
        'mobile' => trim((string) ($_POST['mobile'] ?? '')),
        'email' => trim((string) ($_POST['email'] ?? '')),
        'address' => trim((string) ($_POST['address'] ?? '')),
        'message' => trim((string) ($_POST['message'] ?? '')),
    ];

    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid request token. Please refresh the page and try again.';
    }
    if ($input['student_name'] === '' || mb_strlen($input['student_name']) < 2) {
        $errors[] = 'Student name is required.';
    }
    if ($input['parent_name'] === '' || mb_strlen($input['parent_name']) < 2) {
        $errors[] = 'Parent name is required.';
    }
    if ($input['class_applying'] === '') {
        $errors[] = 'Please provide the class applying for.';
    }
    if (!preg_match('/^[0-9+\-\s]{8,15}$/', $input['mobile'])) {
        $errors[] = 'Enter a valid mobile number.';
    }
    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }
    if ($input['address'] === '') {
        $errors[] = 'Address is required.';
    }
    if ($input['message'] === '' || mb_strlen($input['message']) < 10) {
        $errors[] = 'Message should be at least 10 characters.';
    }

    if (!$errors) {
        $stmt = db()->prepare(
            'INSERT INTO admission_inquiries
            (student_name, parent_name, class_applying, mobile, email, address, message, status)
            VALUES (:student_name, :parent_name, :class_applying, :mobile, :email, :address, :message, :status)'
        );
        $stmt->execute([
            'student_name' => $input['student_name'],
            'parent_name' => $input['parent_name'],
            'class_applying' => $input['class_applying'],
            'mobile' => $input['mobile'],
            'email' => $input['email'],
            'address' => $input['address'],
            'message' => $input['message'],
            'status' => 'new',
        ]);

        set_flash('success', 'Inquiry submitted successfully. Our admission office will contact you soon.');
        redirect('admission.php');
    }
}

include __DIR__ . '/includes/header.php';
?>

<section class="container mb-5">
    <div class="facility-page-intro reveal">
        <p class="facility-intro-kicker mb-2">Admissions</p>
        <h1 class="mb-3">Admission Inquiry</h1>
        <p class="mb-0">Share your inquiry details and our admissions team will guide you through class availability, process, and next steps.</p>
    </div>
</section>

<section class="container mb-4">
    <div class="row g-4">
        <div class="col-lg-4 reveal">
            <article class="contact-info-card h-100">
                <div class="contact-info-media">
                    <img src="<?= e(url($fallbackImages[0])) ?>" alt="Admissions and student support">
                </div>
                <div class="p-3 p-lg-4">
                    <h2 class="h4 mb-3"><i class="fa-solid fa-headset me-2 text-primary"></i>Admission Support</h2>
                    <ul class="contact-list mb-4">
                        <li><i class="fa-solid fa-phone"></i><span><?= e(get_setting('contact_phone', '+1 000-000-0000')) ?></span></li>
                        <li><i class="fa-regular fa-envelope"></i><span><?= e(get_setting('contact_email', 'admissions@school.edu')) ?></span></li>
                        <li><i class="fa-solid fa-location-dot"></i><span><?= e(get_setting('contact_address', 'School Campus Address')) ?></span></li>
                    </ul>
                    <div class="admission-steps">
                        <h3 class="h6 mb-2">Admission Process</h3>
                        <ol class="mb-0">
                            <li>Submit inquiry form</li>
                            <li>Connect with admissions desk</li>
                            <li>Campus visit and final guidance</li>
                        </ol>
                    </div>
                </div>
            </article>
        </div>

        <div class="col-lg-8 reveal">
            <div class="inquiry-panel h-100">
                <h2 class="h4 mb-3">Admission Inquiry Form</h2>
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
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Student Name</label>
                            <input type="text" name="student_name" class="form-control" required minlength="2" value="<?= e($input['student_name']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Parent Name</label>
                            <input type="text" name="parent_name" class="form-control" required minlength="2" value="<?= e($input['parent_name']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Class Applying For</label>
                            <input type="text" name="class_applying" class="form-control" required value="<?= e($input['class_applying']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mobile Number</label>
                            <input type="text" name="mobile" class="form-control" required pattern="[0-9+\-\s]{8,15}" value="<?= e($input['mobile']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" required value="<?= e($input['email']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Address</label>
                            <input type="text" name="address" class="form-control" required value="<?= e($input['address']) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Message</label>
                            <textarea name="message" rows="4" class="form-control" required minlength="10"><?= e($input['message']) ?></textarea>
                        </div>
                    </div>
                    <button class="btn btn-primary mt-3" type="submit">
                        <i class="fa-solid fa-file-signature me-2"></i>Submit Inquiry
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<section class="container mb-4">
    <div class="welcome-about-card reveal">
        <div class="row g-0 align-items-center">
            <div class="col-lg-5">
                <div class="welcome-about-media h-100">
                    <img src="<?= e(url($fallbackImages[1])) ?>" alt="Students during school orientation">
                </div>
            </div>
            <div class="col-lg-7">
                <div class="welcome-about-content">
                    <h2 class="h4 mb-3">Campus Visit and Counselling</h2>
                    <p class="mb-3">Families are welcome to visit the campus and interact with our team to understand academics, co-curricular offerings, and classroom environment.</p>
                    <a href="<?= e(url('contact.php')) ?>" class="btn btn-outline-primary">Book a Visit</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
