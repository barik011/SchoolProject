<?php
$footerMenu = get_navigation_menu_tree(true);
$quickLinks = [];

$collectQuickLinks = function (array $items) use (&$collectQuickLinks, &$quickLinks): void {
    foreach ($items as $item) {
        if (count($quickLinks) >= 8) {
            return;
        }

        $children = $item['children'] ?? [];
        $href = menu_item_href($item);
        if ($href !== '#') {
            $quickLinks[] = $item;
        }
        if (!empty($children)) {
            $collectQuickLinks($children);
        }
    }
};
$collectQuickLinks($footerMenu);
?>
        <footer class="site-footer footer-dark mt-5 border-top">
            <div class="container py-5">
                <div class="row g-4">
                    <div class="col-lg-4">
                        <h3 class="footer-title"><?= e(get_setting('school_name', 'Greenfield Public School')) ?></h3>
                        <p class="footer-copy mb-3"><?= e(get_setting('school_tagline', 'Inspiring minds. Building character.')) ?></p>
                        <p class="footer-copy mb-0">A safe and progressive learning ecosystem focused on academics, values, and holistic development.</p>
                    </div>

                    <div class="col-sm-6 col-lg-4">
                        <h4 class="footer-subtitle">Quick Links</h4>
                        <ul class="footer-link-list">
                            <?php if (!$quickLinks): ?>
                                <li><a href="<?= e(url('index.php')) ?>">Home</a></li>
                                <li><a href="<?= e(url('about.php')) ?>">About</a></li>
                                <li><a href="<?= e(url('admission.php')) ?>">Admission</a></li>
                                <li><a href="<?= e(url('contact.php')) ?>">Contact</a></li>
                            <?php else: ?>
                                <?php foreach ($quickLinks as $item): ?>
                                    <li>
                                        <a href="<?= e(menu_item_href($item)) ?>"<?= (int) ($item['open_in_new_tab'] ?? 0) === 1 ? ' target="_blank" rel="noopener noreferrer"' : '' ?>>
                                            <?= e((string) $item['label']) ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <div class="col-sm-6 col-lg-4">
                        <h4 class="footer-subtitle">Contact</h4>
                        <ul class="footer-contact-list">
                            <li><i class="fa-solid fa-phone"></i> <?= e(get_setting('contact_phone', '+1 000-000-0000')) ?></li>
                            <li><i class="fa-regular fa-envelope"></i> <?= e(get_setting('contact_email', 'info@school.edu')) ?></li>
                            <li><i class="fa-solid fa-location-dot"></i> <?= e(get_setting('contact_address', 'School Campus Address')) ?></li>
                        </ul>
                        <div class="footer-social d-flex gap-2 mt-3">
                            <a href="#" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                            <a href="#" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                            <a href="#" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer-bottom border-top">
                <div class="container py-3 d-flex flex-column flex-md-row justify-content-between gap-2">
                    <p class="mb-0 small">&copy; <?= date('Y') ?> <?= e(get_setting('school_name', 'Greenfield Public School')) ?>. All rights reserved.</p>
                    <p class="mb-0 small">Built with HTML5, Bootstrap 5, PHP, and MySQL</p>
                </div>
            </div>
        </footer>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= e(url('assets/js/main.js')) ?>"></script>
</body>
</html>
