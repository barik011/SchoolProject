<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';

$pageTitle = $pageTitle ?? 'School Website';
$metaDescription = $metaDescription ?? 'A modern school website with transparent information for parents and students.';
$schoolName = get_setting('school_name', 'Greenfield Public School');
$tagline = get_setting('school_tagline', 'Inspiring minds. Building character.');
$primaryColor = get_setting('primary_color', '#0b6efd');
$defaultMode = get_setting('default_mode', 'light');
$currentScript = basename($_SERVER['PHP_SELF'] ?? 'index.php');
$successFlash = get_flash('success');
$errorFlash = get_flash('error');
$hideDefaultHero = $hideDefaultHero ?? false;
$menuTree = get_navigation_menu_tree(true);
$currentSlug = trim((string) ($_GET['slug'] ?? ''));
$requestPath = parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?: '/';
$requestPath = '/' . trim((string) $requestPath, '/');

$isMenuItemActive = function (array $item) use (&$isMenuItemActive, $currentScript, $currentSlug, $requestPath): bool {
    $type = (string) ($item['item_type'] ?? 'static');
    $isActive = false;

    if ($type === 'static') {
        $targetScript = basename((string) ($item['link_value'] ?? 'index.php'));
        $isActive = $currentScript === $targetScript;
    } elseif ($type === 'custom_page') {
        $isActive = $currentScript === 'page.php' && $currentSlug !== '' && $currentSlug === (string) ($item['page_slug'] ?? '');
    } elseif ($type === 'custom_path') {
        $relative = trim((string) ($item['link_value'] ?? ''));
        if ($relative !== '' && $relative !== '#' && !preg_match('#^https?://#i', $relative)) {
            $targetPath = parse_url(url(ltrim($relative, '/')), PHP_URL_PATH) ?: '';
            $targetPath = '/' . trim((string) $targetPath, '/');
            $isActive = rtrim($targetPath, '/') === rtrim($requestPath, '/');
        }
    }

    foreach ($item['children'] ?? [] as $child) {
        if ($isMenuItemActive($child)) {
            return true;
        }
    }

    return $isActive;
};

$renderMenu = function (array $items, int $level = 0) use (&$renderMenu, $isMenuItemActive): void {
    foreach ($items as $item) {
        $children = $item['children'] ?? [];
        $hasChildren = !empty($children);
        $isActive = $isMenuItemActive($item);
        $href = menu_item_href($item);
        $icon = trim((string) ($item['icon_class'] ?? ''));
        $openInNew = (int) ($item['open_in_new_tab'] ?? 0) === 1;

        if ($level === 0): ?>
            <?php if ($hasChildren): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= $isActive ? 'active' : '' ?>" href="<?= e($href) ?>" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                        <?php if ($icon !== ''): ?><i class="<?= e($icon) ?> nav-icon" aria-hidden="true"></i><?php endif; ?>
                        <span><?= e((string) $item['label']) ?></span>
                    </a>
                    <ul class="dropdown-menu menu-fade">
                        <?php $renderMenu($children, $level + 1); ?>
                    </ul>
                </li>
            <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link <?= $isActive ? 'active' : '' ?>" href="<?= e($href) ?>"<?= $openInNew ? ' target="_blank" rel="noopener noreferrer"' : '' ?>>
                        <?php if ($icon !== ''): ?><i class="<?= e($icon) ?> nav-icon" aria-hidden="true"></i><?php endif; ?>
                        <span><?= e((string) $item['label']) ?></span>
                    </a>
                </li>
            <?php endif; ?>
        <?php else: ?>
            <?php if ($hasChildren): ?>
                <li class="dropdown-submenu">
                    <a class="dropdown-item dropdown-toggle <?= $isActive ? 'active' : '' ?>" href="<?= e($href) ?>" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                        <?php if ($icon !== ''): ?><i class="<?= e($icon) ?> nav-icon" aria-hidden="true"></i><?php endif; ?>
                        <span><?= e((string) $item['label']) ?></span>
                    </a>
                    <ul class="dropdown-menu menu-fade">
                        <?php $renderMenu($children, $level + 1); ?>
                    </ul>
                </li>
            <?php else: ?>
                <li>
                    <a class="dropdown-item <?= $isActive ? 'active' : '' ?>" href="<?= e($href) ?>"<?= $openInNew ? ' target="_blank" rel="noopener noreferrer"' : '' ?>>
                        <?php if ($icon !== ''): ?><i class="<?= e($icon) ?> nav-icon" aria-hidden="true"></i><?php endif; ?>
                        <span><?= e((string) $item['label']) ?></span>
                    </a>
                </li>
            <?php endif; ?>
        <?php endif;
    }
};
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="<?= e($defaultMode) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | <?= e($schoolName) ?></title>
    <meta name="description" content="<?= e($metaDescription) ?>">
    <meta name="theme-color" content="<?= e($primaryColor) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;700;800&family=Merriweather:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
    <style>:root { --primary-color: <?= e($primaryColor) ?>; }</style>
</head>
<body>
    <header class="site-header">
        <nav class="navbar navbar-expand-lg sticky-top border-bottom bg-body main-navbar" id="mainNavbar">
            <div class="container">
                <a class="navbar-brand d-flex flex-column" href="<?= e(url('index.php')) ?>">
                    <span class="brand-main"><?= e($schoolName) ?></span>
                    <span class="brand-sub"><?= e($tagline) ?></span>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="mainNav">
                    <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2 dynamic-menu">
                        <?php $renderMenu($menuTree); ?>
                        <li class="nav-item ms-lg-2">
                            <button class="btn btn-outline-secondary btn-sm theme-toggle" id="themeToggle" type="button">
                                <i class="bi bi-moon-stars"></i>
                                <span>Theme</span>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main class="pb-5">
        <?php if (!$hideDefaultHero): ?>
            <section class="hero-shell py-5">
                <div class="container">
                    <div class="hero-box">
                        <p class="hero-kicker">Welcome to <?= e($schoolName) ?></p>
                        <h1 class="hero-title"><?= e($pageTitle) ?></h1>
                        <p class="hero-copy mb-0"><?= e($tagline) ?></p>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <div class="container mt-4">
            <?php if ($successFlash): ?>
                <div class="alert alert-success"><?= e($successFlash) ?></div>
            <?php endif; ?>
            <?php if ($errorFlash): ?>
                <div class="alert alert-danger"><?= e($errorFlash) ?></div>
            <?php endif; ?>
        </div>
