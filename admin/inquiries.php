<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';
require_admin();

$statusOptions = ['new', 'contacted', 'in_process', 'enrolled', 'closed'];

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $rows = db()->query(
        'SELECT id, student_name, parent_name, class_applying, mobile, email, address, message, status, created_at
         FROM admission_inquiries
         ORDER BY created_at DESC'
    )->fetchAll();

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=admission_inquiries.csv');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Student Name', 'Parent Name', 'Class', 'Mobile', 'Email', 'Address', 'Message', 'Status', 'Submitted At']);
    foreach ($rows as $row) {
        fputcsv($output, [
            $row['id'],
            $row['student_name'],
            $row['parent_name'],
            $row['class_applying'],
            $row['mobile'],
            $row['email'],
            $row['address'],
            $row['message'],
            $row['status'],
            $row['created_at'],
        ]);
    }
    fclose($output);
    exit;
}

if (is_post()) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        set_flash('error', 'Invalid request token.');
        redirect('admin/inquiries.php');
    }

    $action = (string) ($_POST['action'] ?? '');
    $inquiryId = (int) ($_POST['inquiry_id'] ?? 0);

    if ($action === 'update_status' && $inquiryId > 0) {
        $status = (string) ($_POST['status'] ?? 'new');
        if (!in_array($status, $statusOptions, true)) {
            $status = 'new';
        }
        $stmt = db()->prepare('UPDATE admission_inquiries SET status = :status WHERE id = :id');
        $stmt->execute([
            'status' => $status,
            'id' => $inquiryId,
        ]);
        set_flash('success', 'Inquiry status updated.');
    }

    if ($action === 'delete' && $inquiryId > 0) {
        $stmt = db()->prepare('DELETE FROM admission_inquiries WHERE id = :id');
        $stmt->execute(['id' => $inquiryId]);
        set_flash('success', 'Inquiry deleted.');
    }

    redirect('admin/inquiries.php');
}

$inquiries = db()->query(
    'SELECT id, student_name, parent_name, class_applying, mobile, email, address, message, status, created_at
     FROM admission_inquiries
     ORDER BY created_at DESC'
)->fetchAll();

$pageTitle = 'Admission Inquiries';
include __DIR__ . '/../includes/admin_header.php';
?>

<div class="d-flex justify-content-end mb-3">
    <a href="<?= e(url('admin/inquiries.php?export=csv')) ?>" class="btn btn-outline-primary">
        <i class="bi bi-download"></i> Export CSV
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Student</th>
                        <th>Parent</th>
                        <th>Class</th>
                        <th>Contact</th>
                        <th>Address</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$inquiries): ?>
                        <tr>
                            <td colspan="10" class="text-center text-body-secondary">No admission inquiries yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($inquiries as $inquiry): ?>
                            <tr>
                                <td>#<?= e((string) $inquiry['id']) ?></td>
                                <td><?= e($inquiry['student_name']) ?></td>
                                <td><?= e($inquiry['parent_name']) ?></td>
                                <td><?= e($inquiry['class_applying']) ?></td>
                                <td>
                                    <div><?= e($inquiry['mobile']) ?></div>
                                    <small class="text-body-secondary"><?= e($inquiry['email']) ?></small>
                                </td>
                                <td><?= e($inquiry['address']) ?></td>
                                <td style="min-width:220px;"><?= e(mb_strimwidth($inquiry['message'], 0, 140, '...')) ?></td>
                                <td>
                                    <form method="post" class="d-flex gap-2">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="inquiry_id" value="<?= e((string) $inquiry['id']) ?>">
                                        <select name="status" class="form-select form-select-sm">
                                            <?php foreach ($statusOptions as $status): ?>
                                                <option value="<?= e($status) ?>" <?= $inquiry['status'] === $status ? 'selected' : '' ?>>
                                                    <?= e(ucwords(str_replace('_', ' ', $status))) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-outline-success">Save</button>
                                    </form>
                                </td>
                                <td><?= e(date('d M Y', strtotime($inquiry['created_at']))) ?></td>
                                <td>
                                    <form method="post">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="inquiry_id" value="<?= e((string) $inquiry['id']) ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" data-confirm="Delete this inquiry?">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
