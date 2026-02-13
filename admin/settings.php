<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';
require_admin();

$errors = [];
$defaultValues = [
    'school_name' => get_setting('school_name', 'Greenfield Public School'),
    'school_tagline' => get_setting('school_tagline', 'Inspiring minds. Building character.'),
    'primary_color' => get_setting('primary_color', '#0b6efd'),
    'default_mode' => get_setting('default_mode', 'light'),
    'contact_phone' => get_setting('contact_phone', '+1 000-000-0000'),
    'contact_email' => get_setting('contact_email', 'info@school.edu'),
    'contact_address' => get_setting('contact_address', 'School Campus Address'),
];

if (is_post()) {
    $input = [
        'school_name' => trim((string) ($_POST['school_name'] ?? '')),
        'school_tagline' => trim((string) ($_POST['school_tagline'] ?? '')),
        'primary_color' => trim((string) ($_POST['primary_color'] ?? '#0b6efd')),
        'default_mode' => trim((string) ($_POST['default_mode'] ?? 'light')),
        'contact_phone' => trim((string) ($_POST['contact_phone'] ?? '')),
        'contact_email' => trim((string) ($_POST['contact_email'] ?? '')),
        'contact_address' => trim((string) ($_POST['contact_address'] ?? '')),
    ];

    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid request token.';
    }
    if ($input['school_name'] === '') {
        $errors[] = 'School name is required.';
    }
    if (!preg_match('/^#[a-fA-F0-9]{6}$/', $input['primary_color'])) {
        $errors[] = 'Primary color must be a valid HEX color.';
    }
    if (!in_array($input['default_mode'], ['light', 'dark'], true)) {
        $errors[] = 'Default mode must be light or dark.';
    }
    if (!filter_var($input['contact_email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Contact email is invalid.';
    }

    if (!$errors) {
        foreach ($input as $key => $value) {
            set_setting($key, $value);
        }
        set_flash('success', 'Settings updated successfully.');
        redirect('admin/settings.php');
    } else {
        $defaultValues = $input;
    }
}

$pageTitle = 'Site Settings';
include __DIR__ . '/../includes/admin_header.php';
?>

<div class="card border-0 shadow-sm">
    <div class="card-body">
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
                    <label class="form-label">School Name</label>
                    <input type="text" name="school_name" class="form-control" required value="<?= e($defaultValues['school_name']) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tagline</label>
                    <input type="text" name="school_tagline" class="form-control" value="<?= e($defaultValues['school_tagline']) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Primary Color</label>
                    <input type="color" name="primary_color" class="form-control form-control-color" value="<?= e($defaultValues['primary_color']) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Default Mode</label>
                    <select name="default_mode" class="form-select">
                        <option value="light" <?= $defaultValues['default_mode'] === 'light' ? 'selected' : '' ?>>Light</option>
                        <option value="dark" <?= $defaultValues['default_mode'] === 'dark' ? 'selected' : '' ?>>Dark</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Contact Phone</label>
                    <input type="text" name="contact_phone" class="form-control" value="<?= e($defaultValues['contact_phone']) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Contact Email</label>
                    <input type="email" name="contact_email" class="form-control" required value="<?= e($defaultValues['contact_email']) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Contact Address</label>
                    <input type="text" name="contact_address" class="form-control" value="<?= e($defaultValues['contact_address']) ?>">
                </div>
            </div>

            <button type="submit" class="btn btn-primary mt-4">Save Settings</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
