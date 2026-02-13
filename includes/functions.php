<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    $isAbsolute = (bool) preg_match('#^https?://#i', $path);
    $target = $isAbsolute ? $path : url($path);
    header("Location: {$target}");
    exit;
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function set_flash(string $key, string $message): void
{
    $_SESSION['flash'][$key] = $message;
}

function get_flash(string $key): ?string
{
    $message = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $message;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token(?string $token): bool
{
    return is_string($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

function cms_pages(): array
{
    return [
        'home' => 'Home',
        'about' => 'About the School',
        'facilities' => 'Facilities',
        'infrastructure' => 'Infrastructure',
    ];
}

function get_setting(string $key, string $default = ''): string
{
    $stmt = db()->prepare('SELECT setting_value FROM site_settings WHERE setting_key = :key LIMIT 1');
    $stmt->execute(['key' => $key]);
    $value = $stmt->fetchColumn();
    return $value !== false ? (string) $value : $default;
}

function set_setting(string $key, string $value): void
{
    $stmt = db()->prepare(
        'INSERT INTO site_settings (setting_key, setting_value) VALUES (:key, :value)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    );
    $stmt->execute([
        'key' => $key,
        'value' => $value,
    ]);
}

function get_sections(string $pageSlug): array
{
    $stmt = db()->prepare(
        'SELECT id, page_slug, section_key, title, content, image_path, is_enabled, sort_order
         FROM page_sections
         WHERE page_slug = :slug
         ORDER BY sort_order ASC, id ASC'
    );
    $stmt->execute(['slug' => $pageSlug]);
    return $stmt->fetchAll();
}

function get_gallery_images(int $limit = 0): array
{
    $sql = 'SELECT id, title, image_path, is_active, uploaded_at FROM gallery_images ORDER BY uploaded_at DESC';
    if ($limit > 0) {
        $sql .= ' LIMIT :limit';
    }

    $stmt = db()->prepare($sql);
    if ($limit > 0) {
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
    }
    $stmt->execute();

    return $stmt->fetchAll();
}

function get_home_banners(int $limit = 0): array
{
    $sql = 'SELECT id, title, subtitle, image_path, is_active, sort_order, created_at
            FROM home_banners
            WHERE is_active = 1
            ORDER BY sort_order ASC, id DESC';
    if ($limit > 0) {
        $sql .= ' LIMIT :limit';
    }

    try {
        $stmt = db()->prepare($sql);
        if ($limit > 0) {
            $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (Throwable $exception) {
        return [];
    }
}

function upload_image(array $file, string $targetRelativeDir): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Image upload failed. Please try again.');
    }

    $maxBytes = 5 * 1024 * 1024;
    if (($file['size'] ?? 0) > $maxBytes) {
        throw new RuntimeException('Image must be 5MB or smaller.');
    }

    $tmpName = $file['tmp_name'] ?? '';
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmpName);

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Only JPG, PNG, WEBP, and GIF images are allowed.');
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    $relativeDir = trim(str_replace('\\', '/', $targetRelativeDir), '/');
    $absoluteDir = app_path($relativeDir);

    if (!is_dir($absoluteDir)) {
        mkdir($absoluteDir, 0755, true);
    }

    $absolutePath = $absoluteDir . DIRECTORY_SEPARATOR . $filename;
    if (!move_uploaded_file($tmpName, $absolutePath)) {
        throw new RuntimeException('Unable to save uploaded image.');
    }

    return $relativeDir . '/' . $filename;
}

function is_admin_logged_in(): bool
{
    return isset($_SESSION['admin_id']) && (int) $_SESSION['admin_id'] > 0;
}

function require_admin(): void
{
    if (!is_admin_logged_in()) {
        redirect('admin/login.php');
    }
}

function current_admin_name(): string
{
    return (string) ($_SESSION['admin_name'] ?? 'Administrator');
}

function delete_file_if_exists(?string $relativePath): void
{
    if (!$relativePath) {
        return;
    }

    $cleaned = trim(str_replace('\\', '/', $relativePath), '/');
    $absolute = app_path($cleaned);

    if (is_file($absolute)) {
        unlink($absolute);
    }
}

