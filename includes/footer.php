        <footer class="site-footer mt-5 border-top">
            <div class="container py-4 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                <p class="mb-0 small text-body-secondary">
                    &copy; <?= date('Y') ?> <?= e(get_setting('school_name', 'Greenfield Public School')) ?>. All rights reserved.
                </p>
                <p class="mb-0 small text-body-secondary">
                    Built with HTML5, Bootstrap 5, PHP, and MySQL
                </p>
            </div>
        </footer>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= e(url('assets/js/main.js')) ?>"></script>
</body>
</html>
