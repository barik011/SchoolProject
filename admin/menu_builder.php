<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';
require_admin();

$pageTitle = 'Menu Builder';
$extensionsReady = cms_extensions_available();
$errors = [];

$itemTypes = [
    'static' => 'Static Page',
    'custom_page' => 'Custom Page',
    'custom_path' => 'Custom Relative Path',
    'external' => 'External URL',
];

$form = [
    'id' => 0,
    'label' => '',
    'parent_id' => 0,
    'item_type' => 'static',
    'link_value' => 'index.php',
    'page_id' => 0,
    'icon_class' => 'fa-solid fa-link',
    'open_in_new_tab' => 0,
    'is_enabled' => 1,
    'sort_order' => 1,
];

if ($extensionsReady && is_post()) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        set_flash('error', 'Invalid request token.');
        redirect('admin/menu_builder.php');
    }

    $action = (string) ($_POST['action'] ?? '');
    $itemId = (int) ($_POST['item_id'] ?? 0);

    try {
        if ($action === 'toggle' && $itemId > 0) {
            $enabled = (int) ($_POST['enabled'] ?? 0) === 1 ? 1 : 0;
            $stmt = db()->prepare('UPDATE menu_items SET is_enabled = :enabled WHERE id = :id');
            $stmt->execute([
                'enabled' => $enabled,
                'id' => $itemId,
            ]);
            set_flash('success', 'Menu item visibility updated.');
            redirect('admin/menu_builder.php');
        }

        if ($action === 'delete' && $itemId > 0) {
            $stmt = db()->prepare('DELETE FROM menu_items WHERE id = :id');
            $stmt->execute(['id' => $itemId]);
            set_flash('success', 'Menu item deleted.');
            redirect('admin/menu_builder.php');
        }

        if ($action === 'save') {
            $staticLinkValue = trim((string) ($_POST['static_link_value'] ?? 'index.php'));
            $customLinkValue = trim((string) ($_POST['custom_link_value'] ?? ''));
            $form = [
                'id' => $itemId,
                'label' => trim((string) ($_POST['label'] ?? '')),
                'parent_id' => max(0, (int) ($_POST['parent_id'] ?? 0)),
                'item_type' => trim((string) ($_POST['item_type'] ?? 'static')),
                'link_value' => $customLinkValue,
                'page_id' => max(0, (int) ($_POST['page_id'] ?? 0)),
                'icon_class' => trim((string) ($_POST['icon_class'] ?? '')),
                'open_in_new_tab' => isset($_POST['open_in_new_tab']) ? 1 : 0,
                'is_enabled' => isset($_POST['is_enabled']) ? 1 : 0,
                'sort_order' => max(1, (int) ($_POST['sort_order'] ?? 1)),
            ];

            if ($form['item_type'] === 'static') {
                $form['link_value'] = $staticLinkValue;
            }

            if ($form['label'] === '') {
                $errors[] = 'Menu label is required.';
            }
            if (!array_key_exists($form['item_type'], $itemTypes)) {
                $errors[] = 'Invalid menu type selected.';
            }
            if ((int) $form['id'] > 0 && (int) $form['parent_id'] === (int) $form['id']) {
                $errors[] = 'A menu item cannot be its own parent.';
            }

            if ($form['item_type'] === 'static') {
                $options = menu_static_page_options();
                if (!array_key_exists($form['link_value'], $options)) {
                    $errors[] = 'Select a valid static page.';
                }
            }

            if ($form['item_type'] === 'custom_page') {
                if ((int) $form['page_id'] <= 0 || !get_custom_page_by_id((int) $form['page_id'])) {
                    $errors[] = 'Select a valid custom page.';
                }
            }

            if ($form['item_type'] === 'custom_path') {
                if ($form['link_value'] === '') {
                    $errors[] = 'Custom relative path is required.';
                }
            }

            if ($form['item_type'] === 'external') {
                if (!preg_match('#^https?://#i', $form['link_value'])) {
                    $errors[] = 'External URL must start with http:// or https://';
                }
            }

            $allItems = get_menu_items(false);
            $byParent = [];
            foreach ($allItems as $item) {
                $byParent[(int) ($item['parent_id'] ?? 0)][] = (int) $item['id'];
            }

            $descendants = [];
            $collectDescendants = function (int $id) use (&$collectDescendants, &$descendants, $byParent): void {
                foreach ($byParent[$id] ?? [] as $childId) {
                    $descendants[] = $childId;
                    $collectDescendants($childId);
                }
            };
            if ((int) $form['id'] > 0) {
                $collectDescendants((int) $form['id']);
                if (in_array((int) $form['parent_id'], $descendants, true)) {
                    $errors[] = 'Invalid parent. A child item cannot be selected as parent.';
                }
            }

            if (!$errors) {
                $stmtData = [
                    'parent_id' => (int) $form['parent_id'] > 0 ? (int) $form['parent_id'] : null,
                    'label' => $form['label'],
                    'item_type' => $form['item_type'],
                    'link_value' => in_array($form['item_type'], ['custom_page'], true) ? null : ($form['link_value'] !== '' ? $form['link_value'] : null),
                    'page_id' => $form['item_type'] === 'custom_page' ? (int) $form['page_id'] : null,
                    'icon_class' => $form['icon_class'] !== '' ? $form['icon_class'] : null,
                    'open_in_new_tab' => (int) $form['open_in_new_tab'],
                    'is_enabled' => (int) $form['is_enabled'],
                    'sort_order' => (int) $form['sort_order'],
                ];

                if ((int) $form['id'] > 0) {
                    $stmt = db()->prepare(
                        'UPDATE menu_items
                         SET parent_id = :parent_id, label = :label, item_type = :item_type, link_value = :link_value,
                             page_id = :page_id, icon_class = :icon_class, open_in_new_tab = :open_in_new_tab,
                             is_enabled = :is_enabled, sort_order = :sort_order
                         WHERE id = :id'
                    );
                    $stmtData['id'] = (int) $form['id'];
                    $stmt->execute($stmtData);
                    set_flash('success', 'Menu item updated.');
                } else {
                    $stmt = db()->prepare(
                        'INSERT INTO menu_items
                         (parent_id, label, item_type, link_value, page_id, icon_class, open_in_new_tab, is_enabled, sort_order)
                         VALUES (:parent_id, :label, :item_type, :link_value, :page_id, :icon_class, :open_in_new_tab, :is_enabled, :sort_order)'
                    );
                    $stmt->execute($stmtData);
                    set_flash('success', 'Menu item created.');
                }

                redirect('admin/menu_builder.php');
            }
        }
    } catch (Throwable $exception) {
        $errors[] = $exception->getMessage();
    }
}

$menuItems = $extensionsReady ? get_menu_items(false) : [];
$menuTree = $extensionsReady ? build_menu_tree($menuItems, 0) : [];
$customPages = $extensionsReady ? get_custom_pages(false) : [];
$staticPages = menu_static_page_options();

$flattened = [];
$flatten = function (array $items, int $level = 0) use (&$flattened, &$flatten): void {
    foreach ($items as $item) {
        $item['level'] = $level;
        $flattened[] = $item;
        if (!empty($item['children'])) {
            $flatten($item['children'], $level + 1);
        }
    }
};
$flatten($menuTree);

if ($extensionsReady && empty($errors)) {
    $editId = (int) ($_GET['id'] ?? 0);
    if ($editId > 0) {
        foreach ($menuItems as $item) {
            if ((int) $item['id'] === $editId) {
                $form = [
                    'id' => (int) $item['id'],
                    'label' => (string) $item['label'],
                    'parent_id' => (int) ($item['parent_id'] ?? 0),
                    'item_type' => (string) $item['item_type'],
                    'link_value' => (string) ($item['link_value'] ?? ''),
                    'page_id' => (int) ($item['page_id'] ?? 0),
                    'icon_class' => (string) ($item['icon_class'] ?? ''),
                    'open_in_new_tab' => (int) ($item['open_in_new_tab'] ?? 0),
                    'is_enabled' => (int) ($item['is_enabled'] ?? 0),
                    'sort_order' => (int) $item['sort_order'],
                ];
                break;
            }
        }
    }
}

$excludedParentIds = [];
if ((int) $form['id'] > 0) {
    $byParent = [];
    foreach ($menuItems as $item) {
        $byParent[(int) ($item['parent_id'] ?? 0)][] = (int) $item['id'];
    }

    $collectExcluded = function (int $id) use (&$collectExcluded, &$excludedParentIds, $byParent): void {
        $excludedParentIds[] = $id;
        foreach ($byParent[$id] ?? [] as $childId) {
            $collectExcluded($childId);
        }
    };
    $collectExcluded((int) $form['id']);
}

include __DIR__ . '/../includes/admin_header.php';
?>

<?php if (!$extensionsReady): ?>
    <div class="alert alert-danger mb-0">
        Unable to initialize menu tables. Check database permissions.
    </div>
<?php else: ?>
    <div class="row g-4">
        <div class="col-xl-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2 class="h5 mb-3"><?= (int) $form['id'] > 0 ? 'Edit Menu Item' : 'Add Menu Item' ?></h2>

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
                        <input type="hidden" name="action" value="save">
                        <input type="hidden" name="item_id" value="<?= e((string) $form['id']) ?>">

                        <div class="mb-3">
                            <label class="form-label">Menu Label</label>
                            <input type="text" name="label" class="form-control" required value="<?= e((string) $form['label']) ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Parent Menu</label>
                            <select name="parent_id" class="form-select">
                                <option value="0">Top Level</option>
                                <?php foreach ($flattened as $item): ?>
                                    <?php if (in_array((int) $item['id'], $excludedParentIds, true)): ?>
                                        <?php continue; ?>
                                    <?php endif; ?>
                                    <option value="<?= e((string) $item['id']) ?>" <?= (int) $form['parent_id'] === (int) $item['id'] ? 'selected' : '' ?>>
                                        <?= e(str_repeat('- ', (int) $item['level']) . $item['label']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Menu Type</label>
                            <select name="item_type" class="form-select" id="menuItemType">
                                <?php foreach ($itemTypes as $value => $label): ?>
                                    <option value="<?= e($value) ?>" <?= $form['item_type'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Static Page</label>
                            <select name="static_link_value" class="form-select" id="staticLinkField">
                                <?php foreach ($staticPages as $path => $label): ?>
                                    <option value="<?= e($path) ?>" <?= $form['link_value'] === $path ? 'selected' : '' ?>><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-body-secondary">Used when menu type is Static Page.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Custom Page</label>
                            <select name="page_id" class="form-select" id="customPageField">
                                <option value="0">Select Custom Page</option>
                                <?php foreach ($customPages as $page): ?>
                                    <option value="<?= e((string) $page['id']) ?>" <?= (int) $form['page_id'] === (int) $page['id'] ? 'selected' : '' ?>>
                                        <?= e($page['title']) ?> (<?= e($page['slug']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-body-secondary">Used when menu type is Custom Page.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Custom Path or External URL</label>
                            <input type="text" name="custom_link_value" class="form-control" id="customLinkField" value="<?= e((string) $form['item_type'] === 'static' ? '' : (string) $form['link_value']) ?>" placeholder="events.php or https://example.com">
                            <small class="text-body-secondary">Used for Custom Relative Path and External URL types.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Font Awesome Icon Class</label>
                            <input type="text" name="icon_class" class="form-control" value="<?= e((string) $form['icon_class']) ?>" placeholder="fa-solid fa-link">
                        </div>

                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label">Sort Order</label>
                                <input type="number" name="sort_order" class="form-control" min="1" value="<?= e((string) $form['sort_order']) ?>">
                            </div>
                            <div class="col-6 d-flex flex-column justify-content-end">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="open_in_new_tab" name="open_in_new_tab" <?= (int) $form['open_in_new_tab'] === 1 ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="open_in_new_tab">Open in new tab</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_enabled" name="is_enabled" <?= (int) $form['is_enabled'] === 1 ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_enabled">Enabled</label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><?= (int) $form['id'] > 0 ? 'Update Item' : 'Add Item' ?></button>
                            <?php if ((int) $form['id'] > 0): ?>
                                <a href="<?= e(url('admin/menu_builder.php')) ?>" class="btn btn-outline-secondary">Cancel Edit</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2 class="h5 mb-3">Menu Structure</h2>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Label</th>
                                    <th>Type</th>
                                    <th>Link</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!$flattened): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-body-secondary">No menu items yet.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($flattened as $item): ?>
                                        <tr>
                                            <td><?= e(str_repeat('- ', (int) $item['level']) . $item['label']) ?></td>
                                            <td><?= e($itemTypes[$item['item_type']] ?? $item['item_type']) ?></td>
                                            <td>
                                                <?php $href = menu_item_href($item); ?>
                                                <?php if ($href === '#'): ?>
                                                    <code>#</code>
                                                <?php else: ?>
                                                    <a href="<?= e($href) ?>" target="_blank"><?= e($href) ?></a>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                                    <input type="hidden" name="action" value="toggle">
                                                    <input type="hidden" name="item_id" value="<?= e((string) $item['id']) ?>">
                                                    <input type="hidden" name="enabled" value="<?= (int) $item['is_enabled'] === 1 ? '0' : '1' ?>">
                                                    <button type="submit" class="btn btn-sm <?= (int) $item['is_enabled'] === 1 ? 'btn-outline-success' : 'btn-outline-secondary' ?>">
                                                        <?= (int) $item['is_enabled'] === 1 ? 'Enabled' : 'Disabled' ?>
                                                    </button>
                                                </form>
                                            </td>
                                            <td class="d-flex gap-2">
                                                <a href="<?= e(url('admin/menu_builder.php?id=' . (string) $item['id'])) ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="item_id" value="<?= e((string) $item['id']) ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" data-confirm="Delete this menu item?">Delete</button>
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
        </div>
    </div>
<?php endif; ?>

<script>
(() => {
    const typeField = document.getElementById("menuItemType");
    const staticField = document.getElementById("staticLinkField");
    const customPageField = document.getElementById("customPageField");
    const customLinkField = document.getElementById("customLinkField");

    if (!typeField || !staticField || !customPageField || !customLinkField) {
        return;
    }

    const syncFields = () => {
        const selectedType = typeField.value;
        staticField.disabled = selectedType !== "static";
        customPageField.disabled = selectedType !== "custom_page";
        customLinkField.disabled = selectedType !== "custom_path" && selectedType !== "external";
    };

    typeField.addEventListener("change", syncFields);
    syncFields();
})();
</script>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
