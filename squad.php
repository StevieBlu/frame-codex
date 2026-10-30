<?php

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/squads.php';

$slotIds = [];

for ($slot = 1; $slot <= 4; $slot++) {
    $slotIds[$slot] = (int) ($_GET['slot' . $slot] ?? 0);
}

$next = catalog_url('squad.php', [
    'slot1' => $slotIds[1],
    'slot2' => $slotIds[2],
    'slot3' => $slotIds[3],
    'slot4' => $slotIds[4],
]);
require_login($next);

$user = current_user();
$grouped = fetch_squad_choices();
$choiceMap = squad_choice_map($grouped);
$errors = [];
$squadName = '';
$highlightId = (int) ($_GET['saved'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $errors[] = 'The form expired. Please try again.';
    }

    $squadName = trim($_POST['squad_name'] ?? '');

    for ($slot = 1; $slot <= 4; $slot++) {
        $slotIds[$slot] = (int) ($_POST['slot' . $slot] ?? 0);
    }

    if ($squadName === '' || strlen($squadName) > 40) {
        $errors[] = 'Give the squad a name of 1 to 40 characters.';
    }

    $built = squad_members_from_ids(array_values($slotIds), $choiceMap);

    if (isset($built['error'])) {
        $errors[] = $built['error'];
    }

    if (!$errors) {
        $newId = save_squad($user['id'], $squadName, $built['members']);
        header('Location: squad.php?saved=' . $newId);
        exit;
    }
}

$pickedIds = array_values($slotIds);
$analysis = null;
$synergies = null;
$pickedMembers = [];
$readyToSave = false;

if (count(array_filter($pickedIds)) === 4) {
    $built = squad_members_from_ids($pickedIds, $choiceMap);

    if (isset($built['members'])) {
        $pickedMembers = $built['members'];
        $analysis = analyze_squad($pickedMembers);
        $synergies = evaluateSynergies($pickedMembers);
        $readyToSave = true;
    } elseif ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $errors[] = $built['error'];
    }
}

$savedSquads = fetch_saved_squads($user['id'], $choiceMap);

$pageTitle = 'Squad builder - Frame Codex';
$nav = 'squad';
$loadCatalogJs = false;
$loadSquadJs = true;

require __DIR__ . '/includes/header.php';
?>

<section class="page-intro">
    <h1>Squad builder</h1>
    <p>Choose four Warframes. Role notes show the jobs you covered. The mechanics section shows invisibility, energy numbers, and healing or shield sustain from the actual abilities.</p>
</section>

<?php if ($errors): ?>
    <div class="alert alert-warning">
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
                <li><?= e($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form class="squad-builder" method="get" action="squad.php" id="squad-check-form">
    <div class="row g-3">
        <?php for ($slot = 1; $slot <= 4; $slot++): ?>
            <div class="col-md-6 col-xl-3">
                <label class="form-label" for="slot<?= $slot ?>">Slot <?= $slot ?></label>
                <select class="form-select squad-slot" id="slot<?= $slot ?>" name="slot<?= $slot ?>">
                    <option value="0">Choose a Warframe</option>
                    <?php foreach (['warframe' => 'Warframes', 'prime' => 'Primes', 'protoframe' => 'Protoframes'] as $category => $label): ?>
                        <optgroup label="<?= e($label) ?>">
                            <?php foreach ($grouped[$category] as $frame): ?>
                                <option
                                    value="<?= e((string) $frame['id']) ?>"
                                    data-roles="<?= e(implode('|', $frame['roles'])) ?>"
                                    <?= $slotIds[$slot] === $frame['id'] ? 'selected' : '' ?>
                                ><?= e($frame['name']) ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endfor; ?>
    </div>
    <button class="btn btn-outline-warning mt-3" type="submit">Check this squad</button>
</form>

<section class="squad-report" id="squad-report">
    <h2>Role coverage</h2>
    <p class="squad-live-note" id="squad-live-note">Choose four Warframes to see how the roles fit.</p>
    <div class="coverage-list" id="coverage-list">
        <?php foreach (squad_all_roles() as $role): ?>
            <div class="coverage-row" data-role="<?= e($role) ?>">
                <span class="role-pill" data-role="<?= e($role) ?>"><?= e($role) ?></span>
                <span class="coverage-names">Not covered</span>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($analysis): ?>
        <h2 class="mt-4">How this squad fits</h2>
        <ul class="squad-notes">
            <?php foreach ($analysis['notes'] as $note): ?>
                <li><?= e($note) ?></li>
            <?php endforeach; ?>
        </ul>

        <?php require __DIR__ . '/includes/synergy-report.php'; ?>

        <?php if ($readyToSave): ?>
            <form method="post" action="squad.php" class="squad-save">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <?php for ($slot = 1; $slot <= 4; $slot++): ?>
                    <input type="hidden" name="slot<?= $slot ?>" value="<?= e((string) $slotIds[$slot]) ?>">
                <?php endfor; ?>
                <label class="form-label" for="squad-name">Squad name</label>
                <div class="squad-save-row">
                    <input class="form-control" id="squad-name" name="squad_name" maxlength="40" value="<?= e($squadName) ?>" required>
                    <button class="btn btn-warning" type="submit">Save squad</button>
                </div>
                <p class="form-text" id="save-hint">This saves the four Warframes you just checked.</p>
            </form>
        <?php endif; ?>
    <?php endif; ?>
</section>

<section class="saved-squads">
    <h2>Saved squads</h2>
    <?php if (!$savedSquads): ?>
        <div class="empty-state">
            <p class="mb-0">You have not saved a squad yet. Check four Warframes, name the team, and save it.</p>
        </div>
    <?php else: ?>
        <div class="saved-squad-list">
            <?php foreach ($savedSquads as $squad): ?>
                <article class="saved-squad<?= $squad['id'] === $highlightId ? ' is-new' : '' ?>">
                    <header class="saved-squad-header">
                        <div>
                            <h3><?= e($squad['name']) ?></h3>
                            <p class="detail-label"><?= e(date('M j, Y', strtotime($squad['created_at']))) ?></p>
                        </div>
                        <form method="post" action="squad-delete.php">
                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="squad_id" value="<?= e((string) $squad['id']) ?>">
                            <button class="btn btn-sm btn-outline-warning" type="submit">Delete</button>
                        </form>
                    </header>
                    <ol class="saved-members">
                        <?php foreach ($squad['members'] as $member): ?>
                            <li>
                                <strong><?= e($member['name']) ?></strong>
                                <span>
                                    <?php foreach ($member['roles'] as $roleName): ?>
                                        <span class="role-pill" data-role="<?= e($roleName) ?>"><?= e($roleName) ?></span>
                                    <?php endforeach; ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                    <ul class="squad-notes">
                        <?php foreach ($squad['analysis']['notes'] as $note): ?>
                            <li><?= e($note) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php
                    $synergies = $squad['synergies'];
                    require __DIR__ . '/includes/synergy-report.php';
                    ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
