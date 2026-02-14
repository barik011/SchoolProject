<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';
require_admin();

$pageTitle = $pageTitle ?? 'Admin Panel';
$schoolName = get_setting('school_name', 'Greenfield Public School');
$primaryColor = get_setting('primary_color', '#0b6efd');
$defaultMode = get_setting('default_mode', 'light');
$currentScript = basename($_SERVER['PHP_SELF'] ?? 'index.php');
$activeScript = $currentScript === 'edit_banner.php' ? 'banners.php' : $currentScript;
$activeScript = $activeScript === 'edit_section.php' ? 'pages.php' : $activeScript;

$adminLinks = [
    'index.php' => 'Dashboard',
    'pages.php' => 'Page Content',
    'custom_pages.php' => 'Custom Pages',
    'menu_builder.php' => 'Menu Builder',
    'banners.php' => 'Home Banners',
    'gallery.php' => 'Gallery',
    'inquiries.php' => 'Inquiries',
    'settings.php' => 'Settings',
];
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="<?= e($defaultMode) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;700;800&family=Merriweather:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
    <style>:root { --primary-color: <?= e($primaryColor) ?>; }</style>
</head>
<body class="admin-body">
    <div class="admin-shell">
        <aside class="admin-sidebar p-3">
            <a href="<?= e(url('admin/index.php')) ?>" class="admin-brand d-block mb-3">
                <span class="fw-bold"><?= e($schoolName) ?></span>
                <small class="d-block text-body-secondary">Admin Panel</small>
            </a>
            <nav class="nav flex-column gap-1">
                <?php foreach ($adminLinks as $file => $label): ?>
                    <a class="nav-link <?= $activeScript === $file ? 'active' : '' ?>" href="<?= e(url('admin/' . $file)) ?>">
                        <?= e($label) ?>
                    </a>
                <?php endforeach; ?>
                <a class="nav-link text-danger" href="<?= e(url('admin/logout.php')) ?>">Logout</a>
            </nav>
        </aside>

        <div class="admin-content">
            <header class="admin-topbar border-bottom bg-body">
                <div class="container-fluid py-3 d-flex justify-content-between align-items-center">
                    <h1 class="h4 mb-0"><?= e($pageTitle) ?></h1>
                    <div class="d-flex align-items-center gap-2">
                        <span class="small text-body-secondary">Signed in as <?= e(current_admin_name()) ?></span>
                        <button class="btn btn-outline-secondary btn-sm theme-toggle" id="themeToggle" type="button">
                            <i class="bi bi-moon-stars"></i>
                            <span>Theme</span>
                        </button>
                    </div>
                </div>
            </header>

            <main class="container-fluid py-4">
                <?php if ($message = get_flash('success')): ?>
                    <div class="alert alert-success"><?= e($message) ?></div>
                <?php endif; ?>
                <?php if ($message = get_flash('error')): ?>
                    <div class="alert alert-danger"><?= e($message) ?></div>
                <?php endif; ?>
