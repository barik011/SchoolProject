<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'Dashboard';

$stats = [
    'sections' => (int) db()->query('SELECT COUNT(*) FROM page_sections')->fetchColumn(),
    'gallery' => (int) db()->query('SELECT COUNT(*) FROM gallery_images')->fetchColumn(),
    'inquiries' => (int) db()->query('SELECT COUNT(*) FROM admission_inquiries')->fetchColumn(),
    'new_inquiries' => (int) db()->query("SELECT COUNT(*) FROM admission_inquiries WHERE status = 'new'")->fetchColumn(),
];

$recentInquiries = db()->query(
    'SELECT id, student_name, parent_name, class_applying, mobile, status, created_at
     FROM admission_inquiries
     ORDER BY created_at DESC
     LIMIT 8'
)->fetchAll();

include __DIR__ . '/../includes/admin_header.php';
?>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="small text-body-secondary">Total CMS Sections</div>
            <div class="display-6 fw-bold"><?= e((string) $stats['sections']) ?></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="small text-body-secondary">Gallery Images</div>
            <div class="display-6 fw-bold"><?= e((string) $stats['gallery']) ?></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="small text-body-secondary">Admission Inquiries</div>
            <div class="display-6 fw-bold"><?= e((string) $stats['inquiries']) ?></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="small text-body-secondary">New Inquiries</div>
            <div class="display-6 fw-bold"><?= e((string) $stats['new_inquiries']) ?></div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <h2 class="h5 mb-3">Recent Admission Inquiries</h2>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Student</th>
                        <th>Parent</th>
                        <th>Class</th>
                        <th>Mobile</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$recentInquiries): ?>
                        <tr>
                            <td colspan="7" class="text-center text-body-secondary">No inquiries found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentInquiries as $inquiry): ?>
                            <tr>
                                <td>#<?= e((string) $inquiry['id']) ?></td>
                                <td><?= e($inquiry['student_name']) ?></td>
                                <td><?= e($inquiry['parent_name']) ?></td>
                                <td><?= e($inquiry['class_applying']) ?></td>
                                <td><?= e($inquiry['mobile']) ?></td>
                                <td><span class="badge text-bg-secondary"><?= e(ucfirst($inquiry['status'])) ?></span></td>
                                <td><?= e(date('d M Y', strtotime($inquiry['created_at']))) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>

