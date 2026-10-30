<?php

require_once __DIR__ . '/db.php';

// The jobs a squad is measured against. Stealth is useful, but a team can
// still work without it. The other four are the core jobs.
function squad_core_roles(): array
{
    return ['Damage', 'Survival', 'Crowd Control', 'Support'];
}

function squad_all_roles(): array
{
    return ['Damage', 'Survival', 'Crowd Control', 'Support', 'Stealth'];
}

function squad_name_list(array $names): string
{
    $names = array_values(array_unique($names));
    $count = count($names);

    if ($count === 0) {
        return '';
    }

    if ($count === 1) {
        return $names[0];
    }

    if ($count === 2) {
        return $names[0] . ' and ' . $names[1];
    }

    $last = array_pop($names);

    return implode(', ', $names) . ', and ' . $last;
}

// Turns four picked frames into coverage and plain-language notes.
function analyze_squad(array $members): array
{
    $coverage = [];

    foreach (squad_all_roles() as $role) {
        $coverage[$role] = [];
    }

    foreach ($members as $member) {
        foreach ($member['roles'] as $role) {
            if (!isset($coverage[$role])) {
                $coverage[$role] = [];
            }

            $coverage[$role][] = $member['name'];
        }
    }

    $missing = [];

    foreach (squad_core_roles() as $role) {
        if ($coverage[$role] === []) {
            $missing[] = $role;
        }
    }

    $notes = [];
    $coveredCore = count(squad_core_roles()) - count($missing);

    if ($coveredCore === 4) {
        $notes[] = 'Damage, survival, crowd control, and support are all present. The four jobs cover each other.';
    } elseif ($missing !== []) {
        $notes[] = 'This squad is missing ' . squad_name_list($missing) . '. Those jobs are left to weapons and gear.';
    }

    if ($coverage['Damage'] === []) {
        $notes[] = 'Nobody is marked for damage, so the squad may be slow at clearing enemies.';
    } elseif (count($coverage['Damage']) === 4) {
        $notes[] = 'All four members can deal damage. The team hits hard, and the other jobs have to come from those same frames.';
    }

    if ($coverage['Support'] !== [] && $coverage['Survival'] !== []) {
        $notes[] = 'Support and survival are both covered, so the squad can restore allies and also stay in a long fight.';
    }

    if ($coverage['Crowd Control'] !== [] && $coverage['Damage'] !== []) {
        $notes[] = 'Crowd control holds enemies still while the damage frames finish them.';
    }

    if ($coverage['Stealth'] !== []) {
        $notes[] = squad_name_list($coverage['Stealth']) . ' can take the quiet jobs, such as slipping past enemies the rest of the squad would fight.';
    } else {
        $notes[] = 'There is no stealth frame. Spy and rescue routes will be done in the open.';
    }

    foreach (['Survival', 'Crowd Control', 'Support', 'Stealth'] as $role) {
        if (count($coverage[$role]) >= 3) {
            $notes[] = count($coverage[$role]) . ' members share ' . $role . ', so that job is very safe and the squad is stacked on it.';
        }
    }

    return [
        'coverage' => $coverage,
        'missing' => $missing,
        'notes' => $notes,
    ];
}

function fetch_squad_choices(): array
{
        $frames = db()->query(
        'SELECT id, name, category, shields, mechanics
         FROM warframes
         ORDER BY category, name'
    )->fetchAll();

    $roleRows = db()->query(
        'SELECT wr.warframe_id, r.name
         FROM warframe_roles wr
         INNER JOIN roles r ON r.id = wr.role_id
         ORDER BY r.name'
    );

    $rolesByFrame = [];

    foreach ($roleRows as $row) {
        $rolesByFrame[(int) $row['warframe_id']][] = $row['name'];
    }

    $grouped = [
        'warframe' => [],
        'prime' => [],
        'protoframe' => [],
    ];

    foreach ($frames as $frame) {
        $frame['id'] = (int) $frame['id'];
        $frame['shields'] = (int) $frame['shields'];
        $frame['roles'] = $rolesByFrame[$frame['id']] ?? [];
        $frame['mechanics'] = decode_mechanics($frame['mechanics'] ?? null);
        $grouped[$frame['category']][] = $frame;
    }

    return $grouped;
}

function squad_choice_map(array $grouped): array
{
    $map = [];

    foreach ($grouped as $frames) {
        foreach ($frames as $frame) {
            $map[$frame['id']] = $frame;
        }
    }

    return $map;
}

// Returns four member rows, or an error string.
function squad_members_from_ids(array $ids, array $choiceMap): array
{
    if (count($ids) !== 4) {
        return ['error' => 'Choose four Warframes.'];
    }

    $unique = array_unique($ids);

    if (count($unique) !== 4) {
        return ['error' => 'Pick four different Warframes. A Prime counts as its own card.'];
    }

    $members = [];

    foreach ($ids as $id) {
        if (!isset($choiceMap[$id])) {
            return ['error' => 'One of those Warframes is not in the catalog.'];
        }

        $members[] = $choiceMap[$id];
    }

    return ['members' => $members];
}

function save_squad(int $userId, string $name, array $members): int
{
    $pdo = db();
    $pdo->beginTransaction();

    $insertSquad = $pdo->prepare('INSERT INTO squads (user_id, name) VALUES (?, ?)');
    $insertSquad->execute([$userId, $name]);
    $squadId = (int) $pdo->lastInsertId();

    $insertMember = $pdo->prepare(
        'INSERT INTO squad_members (squad_id, slot, warframe_id) VALUES (?, ?, ?)'
    );

    foreach ($members as $index => $member) {
        $insertMember->execute([$squadId, $index + 1, $member['id']]);
    }

    $pdo->commit();

    return $squadId;
}

function fetch_saved_squads(int $userId, array $choiceMap): array
{
    $statement = db()->prepare(
        'SELECT s.id, s.name, s.created_at, m.slot, m.warframe_id
         FROM squads s
         INNER JOIN squad_members m ON m.squad_id = s.id
         WHERE s.user_id = ?
         ORDER BY s.created_at DESC, s.id DESC, m.slot'
    );
    $statement->execute([$userId]);

    $squads = [];

    foreach ($statement as $row) {
        $squadId = (int) $row['id'];

        if (!isset($squads[$squadId])) {
            $squads[$squadId] = [
                'id' => $squadId,
                'name' => $row['name'],
                'created_at' => $row['created_at'],
                'members' => [],
            ];
        }

        $frameId = (int) $row['warframe_id'];
        $member = $choiceMap[$frameId] ?? [
            'id' => $frameId,
            'name' => 'Unknown Warframe',
            'category' => 'warframe',
            'shields' => 0,
            'roles' => [],
            'mechanics' => empty_mechanics(),
        ];
        $squads[$squadId]['members'][(int) $row['slot']] = $member;
    }

    foreach ($squads as $squadId => $squad) {
        ksort($squads[$squadId]['members']);
        $squads[$squadId]['members'] = array_values($squads[$squadId]['members']);
        $squads[$squadId]['analysis'] = analyze_squad($squads[$squadId]['members']);
        $squads[$squadId]['synergies'] = evaluateSynergies($squads[$squadId]['members']);
    }

    return array_values($squads);
}

function delete_squad(int $userId, int $squadId): void
{
    $statement = db()->prepare('DELETE FROM squads WHERE id = ? AND user_id = ?');
    $statement->execute([$squadId, $userId]);
}

function empty_mechanics(): array
{
    return [
        'buffs_provided' => [],
        'sustain_provided' => [],
        'ability_tags' => [],
    ];
}

function decode_mechanics(?string $json): array
{
    if ($json === null || $json === '') {
        return empty_mechanics();
    }

    $decoded = json_decode($json, true);

    if (!is_array($decoded)) {
        return empty_mechanics();
    }

    return [
        'buffs_provided' => is_array($decoded['buffs_provided'] ?? null) ? $decoded['buffs_provided'] : [],
        'sustain_provided' => is_array($decoded['sustain_provided'] ?? null) ? $decoded['sustain_provided'] : [],
        'ability_tags' => is_array($decoded['ability_tags'] ?? null) ? $decoded['ability_tags'] : [],
    ];
}

function synergy_number(float $value): string
{
    $rounded = round($value, 1);

    if (abs($rounded - round($rounded)) < 0.05) {
        return (string) (int) round($rounded);
    }

    return number_format($rounded, 1, '.', '');
}

function mechanics_entries(array $members, string $bucket, string $tag): array
{
    $found = [];

    foreach ($members as $member) {
        $mechanics = $member['mechanics'] ?? empty_mechanics();
        $entries = $mechanics[$bucket] ?? [];

        foreach ($entries as $entry) {
            if (!is_array($entry) || ($entry['tag'] ?? '') !== $tag) {
                continue;
            }

            $entry['frame'] = $member['name'];
            $found[] = $entry;
        }
    }

    return $found;
}

// Reads the mechanics object on each of the four frames and reports stealth
// melee setup, energy loops, and health or shield sustain. Numbers are the
// unmodded max-rank values stored with each ability.
function evaluateSynergies(array $members): array
{
    return [
        'stealth_melee' => evaluate_stealth_melee($members),
        'energy' => evaluate_energy_sustain($members),
        'defense' => evaluate_defense_sustain($members),
    ];
}

function evaluate_stealth_melee(array $members): array
{
    $squad = mechanics_entries($members, 'buffs_provided', 'invisibility_squad');
    $self = mechanics_entries($members, 'buffs_provided', 'invisibility_self');
    $details = [];

    if ($squad === [] && $self === []) {
        return [
            'title' => 'Stealth and melee multipliers',
            'summary' => 'Nobody provides invisibility. Melee stealth multipliers and stealth affinity stay off.',
            'details' => [],
        ];
    }

    if ($squad !== []) {
        $names = squad_name_list(array_column($squad, 'frame'));
        $details[] = $names . ' can hide allies. A melee attack made from that invisibility uses the stealth damage multiplier, 8 times damage on an unaware enemy, and the kill grants stealth affinity.';

        foreach ($squad as $entry) {
            $line = $entry['frame'] . ' — ' . $entry['ability'] . ': ' . $entry['summary'];

            if (!empty($entry['condition'])) {
                $line .= ' Condition: ' . $entry['condition'];
            }

            $details[] = $line;
        }
    }

    if ($self !== []) {
        $names = squad_name_list(array_column($self, 'frame'));
        $details[] = $names . ' can turn invisible alone. Only that Warframe\'s melee gets the stealth multiplier. The rest of the squad stays visible and does not get it.';

        foreach ($self as $entry) {
            $details[] = $entry['frame'] . ' — ' . $entry['ability'] . ': ' . $entry['summary'];
        }
    }

    $summary = $squad !== []
        ? 'Squad invisibility is available, so teammates can borrow it for stealth melee.'
        : 'Invisibility in this squad is personal. It does not cloak the whole team.';

    return [
        'title' => 'Stealth and melee multipliers',
        'summary' => $summary,
        'details' => $details,
    ];
}

function evaluate_energy_sustain(array $members): array
{
    $multiplierEntry = null;

    foreach (mechanics_entries($members, 'sustain_provided', 'energy_multiplier') as $entry) {
        if ($multiplierEntry === null || (float) $entry['multiplier'] > (float) $multiplierEntry['multiplier']) {
            $multiplierEntry = $entry;
        }
    }

    $multiplier = $multiplierEntry === null ? 1.0 : (float) $multiplierEntry['multiplier'];
    $details = [];

    if ($multiplierEntry !== null) {
        $details[] = $multiplierEntry['frame'] . ' — ' . $multiplierEntry['ability'] . ': energy from every source is multiplied by ' . synergy_number($multiplier) . ' for allies nearby. ' . $multiplierEntry['summary'];
    }

    $sourceCount = 0;

    foreach (mechanics_entries($members, 'sustain_provided', 'energy_pulse') as $entry) {
        $details[] = format_energy_pulse($entry, $multiplier, $multiplierEntry !== null);
        $sourceCount++;
    }

    foreach (mechanics_entries($members, 'sustain_provided', 'energy_regen') as $entry) {
        $details[] = format_energy_regen($entry, $multiplier, $multiplierEntry !== null);
        $sourceCount++;
    }

    foreach (mechanics_entries($members, 'sustain_provided', 'energy_orb') as $entry) {
        $perOrb = (float) $entry['energy_per_orb'] * $multiplier;
        $details[] = $entry['frame'] . ' — ' . $entry['ability'] . ': each energy orb restores ' . synergy_number($perOrb) . ' energy'
            . ($multiplierEntry !== null ? ' after ' . $multiplierEntry['ability'] : '')
            . '. ' . $entry['summary'];
        $sourceCount++;
    }

    foreach (['energy_on_kill', 'bonus_orbs', 'health_to_energy', 'damage_to_energy', 'health_orb_to_energy', 'universal_orb', 'orb_chance'] as $tag) {
        foreach (mechanics_entries($members, 'sustain_provided', $tag) as $entry) {
            $line = $entry['frame'] . ' — ' . $entry['ability'] . ': ' . $entry['summary'];

            if ($multiplier > 1 && isset($entry['energy_per_orb'])) {
                $line .= ' With the multiplier, each energy orb is worth ' . synergy_number((float) $entry['energy_per_orb'] * $multiplier) . ' energy.';
            }

            $details[] = $line;
            $sourceCount++;
        }
    }

    if ($multiplierEntry !== null && $sourceCount === 0) {
        $details[] = 'No other selected ability creates energy. The multiplier still applies to energy orbs and other energy pickups.';
    }

    if ($details === []) {
        return [
            'title' => 'Ability-driven energy sustain',
            'summary' => 'No selected ability generates energy or multiplies energy gains.',
            'details' => [],
        ];
    }

    $summary = 'Energy here comes from ability loops. Flat energy per second is shown only while that ability is actually running.';

    if ($multiplierEntry !== null) {
        $summary = $multiplierEntry['ability'] . ' multiplies the other energy effects. The rates below already include that multiplier.';
    }

    return [
        'title' => 'Ability-driven energy sustain',
        'summary' => $summary,
        'details' => $details,
    ];
}

function format_energy_pulse(array $entry, float $multiplier, bool $multiplied): string
{
    $pulses = (int) round((float) $entry['duration_seconds'] / (float) $entry['pulse_interval_seconds']);
    $perPulse = (float) $entry['energy_per_pulse'] * $multiplier;
    $gross = $pulses * $perPulse;
    $cost = (float) $entry['energy_cost'];
    $duration = (float) $entry['duration_seconds'];
    $casterNet = $gross - $cost;
    $casterPerSecond = $casterNet / $duration;
    $allyPerSecond = $gross / $duration;
    $multiplierNote = $multiplied ? ' This already includes the energy multiplier.' : ' These are unmodded max-rank numbers.';

    return $entry['frame'] . ' — ' . $entry['ability'] . ': ' . $pulses . ' pulses of ' . synergy_number($perPulse)
        . ' energy over ' . synergy_number($duration) . ' seconds. Allies in range gain ' . synergy_number($gross)
        . ' energy, about ' . synergy_number($allyPerSecond) . ' per second, and they do not pay the cast. '
        . $entry['frame'] . ' spends ' . synergy_number($cost) . ', so the net is '
        . ($casterNet >= 0 ? '+' : '') . synergy_number($casterNet) . ' energy, about '
        . synergy_number($casterPerSecond) . ' per second while the mark lasts.' . $multiplierNote . ' ' . $entry['summary'];
}

function format_energy_regen(array $entry, float $multiplier, bool $multiplied): string
{
    $perSecond = (float) $entry['energy_per_second'] * $multiplier;
    $cost = (float) $entry['energy_cost'];
    $payback = $perSecond > 0 ? $cost / $perSecond : 0;
    $multiplierNote = $multiplied ? ' The rate includes the squad energy multiplier while that buff is up.' : ' This regen belongs to this Warframe only.';

    return $entry['frame'] . ' — ' . $entry['ability'] . ': ' . synergy_number($perSecond)
        . ' energy per second while it is active. The cast costs ' . synergy_number($cost)
        . ' energy and is paid back in about ' . synergy_number($payback) . ' seconds of uptime.'
        . $multiplierNote . ' ' . $entry['summary'];
}

function evaluate_defense_sustain(array $members): array
{
    $details = [];
    $shieldRestore = mechanics_entries($members, 'sustain_provided', 'shield_restore');
    $shieldRecharge = mechanics_entries($members, 'sustain_provided', 'shield_recharge');
    $healthOrbs = array_merge(
        mechanics_entries($members, 'sustain_provided', 'health_orb'),
        mechanics_entries($members, 'sustain_provided', 'bonus_orbs'),
        mechanics_entries($members, 'sustain_provided', 'orb_chance')
    );
    $heals = array_merge(
        mechanics_entries($members, 'sustain_provided', 'squad_heal'),
        mechanics_entries($members, 'sustain_provided', 'health_regen')
    );

    if ($shieldRestore !== [] || $shieldRecharge !== []) {
        $details[] = 'Breaking shields grants a short invulnerability. Restoring or recharging shields lets that shield gate happen again.';

        foreach (array_merge($shieldRestore, $shieldRecharge) as $entry) {
            $who = ($entry['target'] ?? 'self') === 'squad' ? 'Affects nearby allies. ' : 'Affects this Warframe. ';
            $details[] = $entry['frame'] . ' — ' . $entry['ability'] . ': ' . $who . $entry['summary'];
        }
    }

    $noShields = [];

    foreach ($members as $member) {
        if ((int) ($member['shields'] ?? 0) === 0) {
            $noShields[] = $member['name'];
        }
    }

    if ($noShields !== []) {
        $details[] = squad_name_list($noShields) . ' has no shields, so shield gating does not apply to them. They have to live on health, healing, or other barriers.';
    }

    if ($healthOrbs !== []) {
        $details[] = 'Health orbs are the pickup loop. A frame that heals when orbs are collected gets more value when another frame makes more orbs.';

        foreach ($healthOrbs as $entry) {
            $details[] = $entry['frame'] . ' — ' . $entry['ability'] . ': ' . $entry['summary'];
        }
    }

    foreach ($heals as $entry) {
        $details[] = $entry['frame'] . ' — ' . $entry['ability'] . ': ' . $entry['summary'];
    }

    if ($details === []) {
        return [
            'title' => 'Defense and survivability sustain',
            'summary' => 'No selected ability restores shields, creates health orbs, or heals the squad.',
            'details' => [],
        ];
    }

    return [
        'title' => 'Defense and survivability sustain',
        'summary' => 'Survivability here is the shield gate, health orbs, and direct healing from the abilities below.',
        'details' => $details,
    ];
}

