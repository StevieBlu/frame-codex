<?php

// Search bar and category tabs. $page is index.php or favorites.php.

$tabs = [
    'warframe' => 'Warframes',
    'prime' => 'Primes',
    'protoframe' => 'Protoframes',
];

if (!empty($showAllTab)) {
    $tabs = ['all' => 'All'] + $tabs;
}
?>
<form class="row g-2 align-items-end filter-bar" method="get" action="<?= e($page) ?>">
    <?php if ($category !== '' && $category !== 'all'): ?>
        <input type="hidden" name="category" value="<?= e($category) ?>">
    <?php endif; ?>
    <div class="col-md-6">
        <label class="form-label" for="search-name">Search by name</label>
        <input class="form-control" id="search-name" name="q" value="<?= e($search) ?>" placeholder="Try Excalibur or Arthur">
    </div>
    <div class="col-md-4">
        <label class="form-label" for="search-role">Role</label>
        <select class="form-select" id="search-role" name="role">
            <option value="0">All roles</option>
            <?php foreach ($roles as $role): ?>
                <option value="<?= e((string) $role['id']) ?>"<?= (int) $role['id'] === (int) $roleId ? ' selected' : '' ?>>
                    <?= e($role['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2">
        <button class="btn btn-warning w-100" type="submit">Search</button>
    </div>
</form>

<ul class="nav nav-pills category-tabs">
    <?php foreach ($tabs as $tabKey => $tabLabel): ?>
        <?php
        $tabCategory = $tabKey === 'all' ? '' : $tabKey;
        $isActive = ($category === '' && $tabKey === 'all') || $category === $tabKey;
        $tabUrl = catalog_url($page, [
            'category' => $tabCategory,
            'q' => $search,
            'role' => $roleId,
        ]);
        ?>
        <li class="nav-item">
            <a class="nav-link<?= $isActive ? ' active' : '' ?>" href="<?= e($tabUrl) ?>"><?= e($tabLabel) ?></a>
        </li>
    <?php endforeach; ?>
</ul>
