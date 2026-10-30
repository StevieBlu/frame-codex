<?php

// Expects $synergies from evaluateSynergies().
?>
<section class="synergy-report">
    <h2>Mechanical Synergies &amp; Sustain</h2>
    <?php foreach (['stealth_melee', 'energy', 'defense'] as $key): ?>
        <article class="synergy-block">
            <h3><?= e($synergies[$key]['title']) ?></h3>
            <p><?= e($synergies[$key]['summary']) ?></p>
            <?php if ($synergies[$key]['details']): ?>
                <ul class="squad-notes">
                    <?php foreach ($synergies[$key]['details'] as $line): ?>
                        <li><?= e($line) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
</section>
