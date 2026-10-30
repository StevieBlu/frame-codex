<?php

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

function fetch_roles(): array
{
    $statement = db()->query('SELECT id, name FROM roles ORDER BY name');

    return $statement->fetchAll();
}

// Loads cards for the catalog or the favorites page.
// Primes and Protoframes keep their own stats. Ability text stays on the base Warframe.
function fetch_frames(array $filters): array
{
    $category = $filters['category'] ?? '';
    $search = trim((string) ($filters['q'] ?? ''));
    $roleId = (int) ($filters['role_id'] ?? 0);
    $userId = (int) ($filters['user_id'] ?? 0);
    $favoritesOnly = !empty($filters['favorites_only']);

    $sql = '
        SELECT
            w.id,
            w.name,
            w.slug,
            w.category,
            w.related_id,
            w.summary,
            w.passive_name,
            w.passive_summary,
            w.health,
            w.shields,
            w.armor,
            w.energy,
            w.accent,
            base.name AS related_name,
            CASE WHEN fav.user_id IS NULL THEN 0 ELSE 1 END AS is_favorite
        FROM warframes w
        LEFT JOIN warframes base ON base.id = w.related_id
        LEFT JOIN favorites fav ON fav.warframe_id = w.id AND fav.user_id = :user_id
    ';

    if ($favoritesOnly) {
        $sql .= ' INNER JOIN favorites only_fav ON only_fav.warframe_id = w.id AND only_fav.user_id = :fav_user_id ';
    }

    $where = [];
    $params = [
        'user_id' => $userId,
    ];

    if ($favoritesOnly) {
        $params['fav_user_id'] = $userId;
    }

    if ($category !== '') {
        $where[] = 'w.category = :category';
        $params['category'] = $category;
    }

    if ($search !== '') {
        $where[] = 'w.name LIKE :q';
        $params['q'] = '%' . addcslashes($search, '%_\\') . '%';
    }

    if ($roleId > 0) {
        $where[] = 'EXISTS (
            SELECT 1
            FROM warframe_roles role_filter
            WHERE role_filter.warframe_id = w.id
              AND role_filter.role_id = :role_id
        )';
        $params['role_id'] = $roleId;
    }

    if ($where) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }

    $sql .= ' ORDER BY w.name';

    $statement = db()->prepare($sql);
    $statement->execute($params);
    $frames = $statement->fetchAll();

    $roleStatement = db()->query(
        'SELECT wr.warframe_id, r.name
         FROM warframe_roles wr
         INNER JOIN roles r ON r.id = wr.role_id
         ORDER BY r.name'
    );
    $rolesByFrame = [];

    foreach ($roleStatement as $roleRow) {
        $rolesByFrame[(int) $roleRow['warframe_id']][] = $roleRow['name'];
    }

    $abilityStatement = db()->query(
        'SELECT warframe_id, slot, name, summary
         FROM abilities
         ORDER BY warframe_id, slot'
    );

    $abilitiesByFrame = [];

    foreach ($abilityStatement as $ability) {
        $ownerId = (int) $ability['warframe_id'];
        $abilitiesByFrame[$ownerId][] = $ability;
    }

    foreach ($frames as $index => $frame) {
        $frameId = (int) $frame['id'];
        $relatedId = (int) $frame['related_id'];
        $roles = $rolesByFrame[$frameId] ?? [];

        if (isset($abilitiesByFrame[$frameId])) {
            $abilities = $abilitiesByFrame[$frameId];
            $usesRelatedAbilities = false;
        } elseif ($relatedId > 0 && isset($abilitiesByFrame[$relatedId])) {
            $abilities = $abilitiesByFrame[$relatedId];
            $usesRelatedAbilities = true;
        } else {
            $abilities = [];
            $usesRelatedAbilities = false;
        }

        $frames[$index]['roles'] = $roles;
        $frames[$index]['abilities'] = $abilities;
        $frames[$index]['uses_related_abilities'] = $usesRelatedAbilities;
        $frames[$index]['is_favorite'] = (int) $frame['is_favorite'] === 1;
        $frames[$index]['portrait'] = portrait_for_slug($frame['slug']);
    }

    return $frames;
}
