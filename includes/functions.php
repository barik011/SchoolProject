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

function cms_extensions_available(): bool
{
    static $ready = null;
    if ($ready !== null) {
        return $ready;
    }

    try {
        db()->exec(
            "CREATE TABLE IF NOT EXISTS custom_pages (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(200) NOT NULL,
                slug VARCHAR(160) NOT NULL UNIQUE,
                excerpt TEXT DEFAULT NULL,
                content LONGTEXT NOT NULL,
                hero_image VARCHAR(255) DEFAULT NULL,
                is_enabled TINYINT(1) NOT NULL DEFAULT 1,
                sort_order INT NOT NULL DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                KEY idx_custom_pages_enabled_sort (is_enabled, sort_order)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        db()->exec(
            "CREATE TABLE IF NOT EXISTS menu_items (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                parent_id INT UNSIGNED DEFAULT NULL,
                label VARCHAR(140) NOT NULL,
                item_type ENUM('static','custom_page','custom_path','external') NOT NULL DEFAULT 'static',
                link_value VARCHAR(255) DEFAULT NULL,
                page_id INT UNSIGNED DEFAULT NULL,
                icon_class VARCHAR(120) DEFAULT NULL,
                open_in_new_tab TINYINT(1) NOT NULL DEFAULT 0,
                is_enabled TINYINT(1) NOT NULL DEFAULT 1,
                sort_order INT NOT NULL DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                KEY idx_menu_parent_sort (parent_id, sort_order),
                KEY idx_menu_enabled (is_enabled),
                KEY idx_menu_page (page_id),
                CONSTRAINT fk_menu_parent FOREIGN KEY (parent_id) REFERENCES menu_items(id) ON DELETE CASCADE,
                CONSTRAINT fk_menu_page FOREIGN KEY (page_id) REFERENCES custom_pages(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $ready = true;
    } catch (Throwable $exception) {
        $ready = false;
    }

    return $ready;
}

function slugify(string $value): string
{
    $slug = strtolower(trim($value));
    $slug = (string) preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim($slug, '-');

    return $slug !== '' ? $slug : 'page';
}

function menu_static_page_options(): array
{
    return [
        'index.php' => 'Home',
        'about.php' => 'About',
        'facilities.php' => 'Facilities',
        'infrastructure.php' => 'Infrastructure',
        'gallery.php' => 'Gallery',
        'admission.php' => 'Admission Inquiry',
        'contact.php' => 'Contact',
    ];
}

function default_navigation_menu(): array
{
    return [
        [
            'id' => 1,
            'label' => 'Home',
            'item_type' => 'static',
            'link_value' => 'index.php',
            'icon_class' => 'fa-solid fa-house',
            'open_in_new_tab' => 0,
            'children' => [],
        ],
        [
            'id' => 2,
            'label' => 'About',
            'item_type' => 'static',
            'link_value' => 'about.php',
            'icon_class' => 'fa-solid fa-school',
            'open_in_new_tab' => 0,
            'children' => [],
        ],
        [
            'id' => 3,
            'label' => 'Academics',
            'item_type' => 'custom_path',
            'link_value' => '#',
            'icon_class' => 'fa-solid fa-book-open-reader',
            'open_in_new_tab' => 0,
            'children' => [
                [
                    'id' => 31,
                    'label' => 'Facilities',
                    'item_type' => 'static',
                    'link_value' => 'facilities.php',
                    'icon_class' => 'fa-solid fa-flask-vial',
                    'open_in_new_tab' => 0,
                    'children' => [],
                ],
                [
                    'id' => 32,
                    'label' => 'Infrastructure',
                    'item_type' => 'static',
                    'link_value' => 'infrastructure.php',
                    'icon_class' => 'fa-solid fa-building-columns',
                    'open_in_new_tab' => 0,
                    'children' => [],
                ],
            ],
        ],
        [
            'id' => 4,
            'label' => 'Gallery',
            'item_type' => 'static',
            'link_value' => 'gallery.php',
            'icon_class' => 'fa-regular fa-images',
            'open_in_new_tab' => 0,
            'children' => [],
        ],
        [
            'id' => 5,
            'label' => 'Connect',
            'item_type' => 'custom_path',
            'link_value' => '#',
            'icon_class' => 'fa-solid fa-link',
            'open_in_new_tab' => 0,
            'children' => [
                [
                    'id' => 51,
                    'label' => 'Admission Inquiry',
                    'item_type' => 'static',
                    'link_value' => 'admission.php',
                    'icon_class' => 'fa-solid fa-file-signature',
                    'open_in_new_tab' => 0,
                    'children' => [],
                ],
                [
                    'id' => 52,
                    'label' => 'Contact',
                    'item_type' => 'static',
                    'link_value' => 'contact.php',
                    'icon_class' => 'fa-regular fa-envelope',
                    'open_in_new_tab' => 0,
                    'children' => [],
                ],
            ],
        ],
    ];
}

function get_custom_pages(bool $enabledOnly = true): array
{
    if (!cms_extensions_available()) {
        return [];
    }

    $sql = 'SELECT id, title, slug, excerpt, content, hero_image, is_enabled, sort_order, created_at, updated_at
            FROM custom_pages';
    if ($enabledOnly) {
        $sql .= ' WHERE is_enabled = 1';
    }
    $sql .= ' ORDER BY sort_order ASC, id ASC';

    $stmt = db()->query($sql);
    return $stmt->fetchAll();
}

function get_custom_page_by_id(int $id): ?array
{
    if ($id <= 0 || !cms_extensions_available()) {
        return null;
    }

    $stmt = db()->prepare(
        'SELECT id, title, slug, excerpt, content, hero_image, is_enabled, sort_order, created_at, updated_at
         FROM custom_pages
         WHERE id = :id
         LIMIT 1'
    );
    $stmt->execute(['id' => $id]);
    $page = $stmt->fetch();

    return $page ?: null;
}

function get_custom_page_by_slug(string $slug, bool $enabledOnly = true): ?array
{
    if ($slug === '' || !cms_extensions_available()) {
        return null;
    }

    $sql = 'SELECT id, title, slug, excerpt, content, hero_image, is_enabled, sort_order, created_at, updated_at
            FROM custom_pages
            WHERE slug = :slug';
    if ($enabledOnly) {
        $sql .= ' AND is_enabled = 1';
    }
    $sql .= ' LIMIT 1';

    $stmt = db()->prepare($sql);
    $stmt->execute(['slug' => $slug]);
    $page = $stmt->fetch();

    return $page ?: null;
}

function get_menu_items(bool $enabledOnly = true): array
{
    if (!cms_extensions_available()) {
        return [];
    }

    $sql = 'SELECT mi.id, mi.parent_id, mi.label, mi.item_type, mi.link_value, mi.page_id, mi.icon_class,
                   mi.open_in_new_tab, mi.is_enabled, mi.sort_order, cp.slug AS page_slug, cp.is_enabled AS page_enabled
            FROM menu_items mi
            LEFT JOIN custom_pages cp ON cp.id = mi.page_id';

    if ($enabledOnly) {
        $sql .= ' WHERE mi.is_enabled = 1
                  AND (mi.item_type <> "custom_page" OR (cp.id IS NOT NULL AND cp.is_enabled = 1))';
    }

    $sql .= ' ORDER BY mi.sort_order ASC, mi.id ASC';

    $stmt = db()->query($sql);
    return $stmt->fetchAll();
}

function build_menu_tree(array $items, int $parentId = 0): array
{
    $tree = [];
    foreach ($items as $item) {
        $itemParentId = (int) ($item['parent_id'] ?? 0);
        if ($itemParentId !== $parentId) {
            continue;
        }

        $item['children'] = build_menu_tree($items, (int) $item['id']);
        $tree[] = $item;
    }

    return $tree;
}

function get_navigation_menu_tree(bool $enabledOnly = true): array
{
    $items = get_menu_items($enabledOnly);
    if (!$items) {
        return default_navigation_menu();
    }

    return build_menu_tree($items, 0);
}

function menu_item_href(array $item): string
{
    $type = (string) ($item['item_type'] ?? 'static');
    $linkValue = trim((string) ($item['link_value'] ?? ''));

    if ($type === 'external') {
        return $linkValue !== '' ? $linkValue : '#';
    }

    if ($type === 'custom_page') {
        $slug = trim((string) ($item['page_slug'] ?? ''));
        return $slug !== '' ? url('page.php?slug=' . urlencode($slug)) : '#';
    }

    if ($type === 'custom_path') {
        if ($linkValue === '' || $linkValue === '#') {
            return '#';
        }
        if (preg_match('#^https?://#i', $linkValue)) {
            return $linkValue;
        }
        return url(ltrim($linkValue, '/'));
    }

    $staticOptions = menu_static_page_options();
    $path = $linkValue !== '' ? $linkValue : 'index.php';
    if (!array_key_exists($path, $staticOptions)) {
        $path = 'index.php';
    }

    return url($path);
}

