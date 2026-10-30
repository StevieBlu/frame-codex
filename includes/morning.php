<?php

// Ash upgrade screen. Included only from favorites.php when Ash is a favorite.

if (($frame['slug'] ?? '') !== 'ash') {
    return;
}

$morningBack = catalog_url('favorites.php', [
    'category' => $category ?? '',
    'q' => $search ?? '',
    'role' => $roleId ?? 0,
]);

$usedCapacity = 31;
$maxCapacity = 60;
$capacityRemaining = $maxCapacity - $usedCapacity;
$capacityFill = (int) round(($usedCapacity / $maxCapacity) * 100);
?>
<section class="morning" style="--accent: <?= e($frame['accent']) ?>" aria-label="Ash upgrade">
    <header class="morning-top">
        <a class="morning-back" href="<?= e($morningBack) ?>">Favorites</a>
        <p class="morning-title"><?= e($frame['name']) ?></p>
        <p class="morning-rank">Rank 30</p>
    </header>

    <div class="morning-body">
        <div class="morning-frame">
            <div class="morning-portrait">
                <?php if ($frame['portrait']): ?>
                    <img src="<?= e($frame['portrait']) ?>" alt="">
                <?php else: ?>
                    <span class="morning-portrait-fallback" aria-hidden="true">A</span>
                <?php endif; ?>
            </div>
            <div class="morning-identity">
                <p class="morning-kicker">Warframe</p>
                <h1><?= e($frame['name']) ?></h1>
                <p class="morning-summary"><?= e($frame['summary']) ?></p>
            </div>
            <dl class="morning-stats">
                <div>
                    <dt>Health</dt>
                    <dd><?= e((string) $frame['health']) ?></dd>
                </div>
                <div>
                    <dt>Shield</dt>
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
        </div>

        <div class="morning-upgrade">
            <p class="morning-upgrade-label">Upgrade</p>

            <div class="morning-capacity">
                <div class="morning-capacity-figure">
                    <span class="morning-capacity-num"><?= e((string) $capacityRemaining) ?></span>
                    <span class="morning-capacity-caption">Capacity</span>
                </div>
                <div
                    class="morning-capacity-track"
                    role="img"
                    aria-label="<?= e((string) $usedCapacity) ?> of <?= e((string) $maxCapacity) ?> capacity used"
                >
                    <span class="morning-capacity-fill" style="width: <?= e((string) $capacityFill) ?>%"></span>
                </div>
            </div>

            <div class="morning-mods" role="radiogroup" aria-label="Equipped mods">
                <label class="wf-mod">
                    <input type="radio" name="ash-mod" value="intensify" checked>
                    <span class="wf-mod-shell">
                        <span class="wf-mod-plate">
                            <span class="wf-mod-top">
                                <span class="wf-polarity" title="Madurai">
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M5 7 L12 19 L19 7" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linejoin="miter"/>
                                    </svg>
                                    <span class="visually-hidden">Madurai polarity</span>
                                </span>
                                <span class="wf-drain">11</span>
                            </span>
                            <span class="wf-mod-art wf-mod-art-strength" aria-hidden="true">
                                <svg viewBox="0 0 80 72">
                                    <path d="M40 64 V28" fill="none" stroke="currentColor" stroke-width="2"/>
                                    <path d="M26 40 L40 18 L54 40" fill="none" stroke="currentColor" stroke-width="2.4"/>
                                    <path d="M20 52 L40 28 L60 52" fill="none" stroke="currentColor" stroke-width="2" opacity="0.55"/>
                                    <circle cx="40" cy="62" r="3.5" fill="currentColor"/>
                                </svg>
                            </span>
                            <span class="wf-mod-name">Intensify</span>
                            <span class="wf-mod-effect"><b>+30%</b> Ability Strength</span>
                            <span class="wf-pips" aria-label="Rank 5 of 5">
                                <i></i><i></i><i></i><i></i><i></i>
                            </span>
                        </span>
                    </span>
                </label>

                <label class="wf-mod">
                    <input type="radio" name="ash-mod" value="streamline">
                    <span class="wf-mod-shell">
                        <span class="wf-mod-plate">
                            <span class="wf-mod-top">
                                <span class="wf-polarity" title="Naramon">
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M4 12 H20 M8 8 V16 M16 8 V16" fill="none" stroke="currentColor" stroke-width="2.2"/>
                                    </svg>
                                    <span class="visually-hidden">Naramon polarity</span>
                                </span>
                                <span class="wf-drain">9</span>
                            </span>
                            <span class="wf-mod-art wf-mod-art-efficiency" aria-hidden="true">
                                <svg viewBox="0 0 80 72">
                                    <path d="M10 26 Q28 16 40 26 T70 26" fill="none" stroke="currentColor" stroke-width="2.2"/>
                                    <path d="M10 38 Q28 28 40 38 T70 38" fill="none" stroke="currentColor" stroke-width="2.2"/>
                                    <path d="M10 50 Q28 40 40 50 T70 50" fill="none" stroke="currentColor" stroke-width="2.2" opacity="0.55"/>
                                </svg>
                            </span>
                            <span class="wf-mod-name">Streamline</span>
                            <span class="wf-mod-effect"><b>+30%</b> Ability Efficiency</span>
                            <span class="wf-pips" aria-label="Rank 5 of 5">
                                <i></i><i></i><i></i><i></i><i></i>
                            </span>
                        </span>
                    </span>
                </label>

                <label class="wf-mod">
                    <input type="radio" name="ash-mod" value="range">
                    <span class="wf-mod-shell">
                        <span class="wf-mod-plate">
                            <span class="wf-mod-top">
                                <span class="wf-polarity" title="Zenurik">
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M4 16 C8 6 12 18 20 8" fill="none" stroke="currentColor" stroke-width="2.2"/>
                                    </svg>
                                    <span class="visually-hidden">Zenurik polarity</span>
                                </span>
                                <span class="wf-drain">11</span>
                            </span>
                            <span class="wf-mod-art wf-mod-art-range" aria-hidden="true">
                                <svg viewBox="0 0 80 72">
                                    <circle cx="40" cy="40" r="4" fill="currentColor"/>
                                    <path d="M27 44 a14 14 0 0 1 26 0" fill="none" stroke="currentColor" stroke-width="2"/>
                                    <path d="M18 48 a22 22 0 0 1 44 0" fill="none" stroke="currentColor" stroke-width="2" opacity="0.75"/>
                                    <path d="M10 52 a30 30 0 0 1 60 0" fill="none" stroke="currentColor" stroke-width="2" opacity="0.45"/>
                                </svg>
                            </span>
                            <span class="wf-mod-name">Range</span>
                            <span class="wf-mod-effect"><b>+30%</b> Ability Range</span>
                            <span class="wf-pips" aria-label="Rank 5 of 5">
                                <i></i><i></i><i></i><i></i><i></i>
                            </span>
                        </span>
                    </span>
                </label>
            </div>

            <dl class="morning-powers">
                <div class="is-strength is-up">
                    <dt>Ability Strength</dt>
                    <dd>130%</dd>
                </div>
                <div class="is-duration">
                    <dt>Ability Duration</dt>
                    <dd>100%</dd>
                </div>
                <div class="is-efficiency is-up">
                    <dt>Ability Efficiency</dt>
                    <dd>130%</dd>
                </div>
                <div class="is-range is-up">
                    <dt>Ability Range</dt>
                    <dd>130%</dd>
                </div>
            </dl>
        </div>
    </div>

    <?php if ($frame['abilities']): ?>
        <ol class="morning-abilities">
            <?php foreach ($frame['abilities'] as $ability): ?>
                <li>
                    <span><?= e((string) $ability['slot']) ?></span>
                    <strong><?= e($ability['name']) ?></strong>
                </li>
            <?php endforeach; ?>
        </ol>
    <?php endif; ?>
</section>
