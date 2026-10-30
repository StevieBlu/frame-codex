<?php

// Expects $frame plus the filter values set by index.php or favorites.php.

$panelId = 'frame-' . $frame['slug'];
$isOpen = isset($openSlug) && $openSlug === $frame['slug'];
$initial = substr($frame['name'], 0, 1);
?>
<div class="col-12 col-sm-6 col-lg-4 col-xl-3">
    <article class="frame-card h-100" style="--accent: <?= e($frame['accent']) ?>">
        <button
            class="frame-card-toggle"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#<?= e($panelId) ?>"
            aria-expanded="<?= $isOpen ? 'true' : 'false' ?>"
            aria-controls="<?= e($panelId) ?>"
        >
            <span class="frame-crest" aria-hidden="true">
                <?php if ($frame['portrait']): ?>
                    <img src="<?= e($frame['portrait']) ?>" alt="">
                <?php else: ?>
                    <?= e($initial) ?>
                <?php endif; ?>
            </span>
            <span class="frame-card-copy">
                <span class="frame-kicker"><?= e(category_label($frame['category'])) ?></span>
                <span class="frame-name"><?= e($frame['name']) ?></span>
                <span class="frame-roles">
                    <?php foreach ($frame['roles'] as $roleName): ?>
                        <span class="role-pill" data-role="<?= e($roleName) ?>"><?= e($roleName) ?></span>
                    <?php endforeach; ?>
                </span>
            </span>
        </button>

        <?php if (!empty($showMorning) && $frame['slug'] === 'ash'): ?>
            <?php
            $morningHref = catalog_url('favorites.php', [
                'category' => $category,
                'q' => $search,
                'role' => $roleId,
                'morning' => 'ash',
            ]);
            ?>
            <a class="morning-launch" href="<?= e($morningHref) ?>">Upgrade</a>
        <?php endif; ?>

        <div id="<?= e($panelId) ?>" class="collapse frame-details<?= $isOpen ? ' show' : '' ?>">
            <div class="frame-details-inner">
                <p><?= e($frame['summary']) ?></p>

                <?php if ($frame['uses_related_abilities'] && $frame['related_name']): ?>
                    <p class="related-note">Abilities match <?= e($frame['related_name']) ?>. The numbers below belong to this card.</p>
                <?php endif; ?>

                <dl class="stat-grid">
                    <div>
                        <dt>Health</dt>
                        <dd><?= e((string) $frame['health']) ?></dd>
                    </div>
                    <div>
                        <dt>Shields</dt>
                        <dd><?= e((string) $frame['shields']) ?></dd>
                    </div>
                    <div>
                        <dt>Armor</dt>
                        <dd><?= e((string) $frame['armor']) ?></dd>
                    </div>
                    <div>
                        <dt>Energy</dt>
                        <dd><?= e((string) $frame['energy']) ?></dd>
                    </div>
                </dl>

                <p class="detail-label">Passive</p>
                <h3 class="detail-heading"><?= e($frame['passive_name']) ?></h3>
                <p><?= e($frame['passive_summary']) ?></p>

                <h3 class="detail-heading">Abilities</h3>
                <ol class="ability-list">
                    <?php foreach ($frame['abilities'] as $ability): ?>
                        <li>
                            <strong><?= e($ability['name']) ?></strong>
                            <span><?= e($ability['summary']) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ol>

                <?php if ($user): ?>
                    <form method="post" action="favorite-toggle.php" class="favorite-form">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="warframe_id" value="<?= e((string) $frame['id']) ?>">
                        <input type="hidden" name="return_to" value="<?= e($returnTo) ?>">
                        <input type="hidden" name="category" value="<?= e($category) ?>">
                        <input type="hidden" name="q" value="<?= e($search) ?>">
                        <input type="hidden" name="role" value="<?= e((string) $roleId) ?>">
                        <input type="hidden" name="open" value="<?= e($frame['slug']) ?>">
                        <button class="btn btn-sm btn-outline-warning" type="submit">
                            <?= $frame['is_favorite'] ? 'Remove favorite' : 'Save favorite' ?>
                        </button>
                    </form>
                <?php else: ?>
                    <?php
                    $loginNext = catalog_url($returnTo, [
                        'category' => $category,
                        'q' => $search,
                        'role' => $roleId,
                        'open' => $frame['slug'],
                    ]);
                    ?>
                    <a class="btn btn-sm btn-outline-warning" href="login.php?next=<?= e(urlencode($loginNext)) ?>">Log in to save</a>
                <?php endif; ?>
            </div>
        </div>
    </article>
</div>
