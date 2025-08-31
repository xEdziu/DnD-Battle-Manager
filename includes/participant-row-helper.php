<?php
// Helper functions for generating participant table rows

function generateParticipantRowHTML($participant, $battleId, $isHidden = false)
{
    $isDead = $participant['hp_current'] <= 0;
    $hpPercentage = $participant['hp_max'] > 0 ? ($participant['hp_current'] / $participant['hp_max']) * 100 : 0;
    $hpColor = $hpPercentage > 75 ? 'text-dnd-emerald' : ($hpPercentage > 25 ? 'text-dnd-gold' : 'text-dnd-crimson');

    // Character type configuration
    $characterType = $participant['character_type'] ?? 'enemy';
    $typeConfig = [
        'pc' => ['icon' => 'user-check', 'color' => 'text-emerald-600'],
        'npc' => ['icon' => 'user', 'color' => 'text-blue-600'],
        'enemy' => ['icon' => 'skull', 'color' => 'text-red-600']
    ];
    $config = $typeConfig[$characterType] ?? $typeConfig['enemy'];

    $rowClass = $isHidden ? 'hidden-participant-row' : 'participant-row';
    $rowId = $isHidden ? 'hidden-participant-row-' . $participant['id'] : 'participant-row-' . $participant['id'];
    $checkboxClass = $isHidden ? 'hidden-participant-checkbox' : 'participant-checkbox';
    $checkboxName = $isHidden ? 'selectedHidden' : 'selected';

    $rowStyle = '';
    if ($isHidden) {
        $rowStyle = 'opacity-75 bg-muted/20';
    }
    if ($isDead) {
        $rowStyle .= ' opacity-50 bg-destructive/5';
    }

    ob_start();
?>
    <tr id="<?= $rowId ?>"
        class="<?= $rowClass ?> border-b border-border hover:bg-muted/50 transition-colors cursor-pointer <?= $rowStyle ?>"
        data-participant-type="<?= $participant['character_type'] ?? 'enemy' ?>"
        data-participant-id="<?= $participant['id'] ?>"
        data-participant-name="<?= h($participant['name']) ?>"
        data-participant-skills="<?= h($participant['skills'] ?? '') ?>"
        data-participant-actions="<?= h($participant['actions'] ?? '') ?>"
        data-participant-notes="<?= h($participant['notes'] ?? '') ?>"
        data-participant-ac="<?= $participant['ac'] ?>"
        data-participant-hp-current="<?= $participant['hp_current'] ?>"
        data-participant-hp-max="<?= $participant['hp_max'] ?>"
        data-participant-str="<?= $participant['str'] ?>"
        data-participant-dex="<?= $participant['dex'] ?>"
        data-participant-con="<?= $participant['con'] ?>"
        data-participant-int="<?= $participant['int'] ?>"
        data-participant-wis="<?= $participant['wis'] ?>"
        data-participant-cha="<?= $participant['cha'] ?>"
        data-participant-passive="<?= $participant['passive'] ?>"
        title="Click to view details">
        <td class="p-4 align-middle">
            <input type="checkbox" name="<?= $checkboxName ?>" value="<?= $participant['id'] ?>"
                class="<?= $checkboxClass ?> h-4 w-4 shrink-0 rounded-sm border border-primary"
                onclick="event.stopPropagation()">
        </td>
        <td class="p-4 align-middle">
            <div class="flex items-center space-x-2">
                <?php if ($isDead): ?>
                    <i data-lucide="skull" class="h-4 w-4 text-destructive"></i>
                <?php else: ?>
                    <i data-lucide="<?= $config['icon'] ?>" class="h-4 w-4 <?= $config['color'] ?>"></i>
                <?php endif; ?>
                <?php if ($isHidden): ?>
                    <i data-lucide="eye-off" class="h-3 w-3 text-muted-foreground mr-1" title="Hidden"></i>
                    <span class="text-sm text-muted-foreground"><?= h($participant['name']) ?></span>
                <?php else: ?>
                    <input type="text" name="name[<?= $participant['id'] ?>]"
                        value="<?= h($participant['name']) ?>"
                        class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring <?= $isDead ? 'bg-destructive/10 text-destructive' : '' ?>"
                        onclick="event.stopPropagation()">
                <?php endif; ?>
            </div>
        </td>
        <td class="p-4 align-middle text-center">
            <div class="inline-flex items-center rounded-full border px-2 py-1 text-xs font-semibold bg-secondary text-secondary-foreground">
                <?= $participant['ac'] ?>
            </div>
        </td>
        <td class="p-4 align-middle text-center">
            <div class="flex items-center justify-center space-x-1">
                <span class="font-medium <?= $hpColor ?>">
                    <?= $participant['hp_current'] ?>
                </span>
                <span class="text-muted-foreground">/</span>
                <span class="text-muted-foreground"><?= $participant['hp_max'] ?></span>
            </div>
            <div class="w-full bg-secondary rounded-full h-1 mt-1">
                <div class="h-1 rounded-full transition-all duration-300 <?= $hpPercentage > 75 ? 'bg-dnd-emerald' : ($hpPercentage > 25 ? 'bg-dnd-gold' : 'bg-dnd-crimson') ?>"
                    style="width: <?= max(0, $hpPercentage) ?>%"></div>
            </div>
        </td>
        <?php foreach (['str', 'dex', 'con', 'int', 'wis', 'cha'] as $stat): ?>
            <td class="p-4 align-middle text-center">
                <div class="text-sm">
                    <div class="font-medium"><?= $participant[$stat] ?></div>
                    <div class="text-xs text-muted-foreground">
                        (<?= formatModifier(calculateModifier($participant[$stat])) ?>)
                    </div>
                </div>
            </td>
        <?php endforeach; ?>
        <td class="p-4 align-middle text-center">
            <div class="inline-flex items-center rounded-full border px-2 py-1 text-xs font-semibold bg-accent text-accent-foreground">
                <?= $participant['passive'] ?>
            </div>
        </td>
        <td class="p-4 align-middle text-center">
            <?php if ($isHidden): ?>
                <div class="text-sm text-muted-foreground">
                    <?= $participant['initiative'] ?>
                </div>
            <?php else: ?>
                <input type="number" name="init[<?= $participant['id'] ?>]"
                    value="<?= $participant['initiative'] ?>"
                    class="initiative-input flex h-9 w-16 rounded-md border border-input bg-transparent px-2 py-1 text-sm text-center shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                    onclick="event.stopPropagation()">
            <?php endif; ?>
        </td>
        <td class="p-4 align-middle text-center">
            <div class="flex items-center justify-center space-x-1">
                <?php if ($isHidden): ?>
                    <button type="button"
                        class="unhide-participant-btn inline-flex items-center justify-center rounded-md h-8 w-8 border border-input bg-background hover:bg-accent hover:text-accent-foreground transition-colors"
                        data-participant-id="<?= $participant['id'] ?>"
                        data-participant-name="<?= h($participant['name']) ?>"
                        data-battle-id="<?= $battleId ?>"
                        title="Unhide participant">
                        <i data-lucide="eye" class="h-3 w-3"></i>
                    </button>
                <?php else: ?>
                    <button type="button"
                        class="hide-participant-btn inline-flex items-center justify-center rounded-md h-8 w-8 border border-input bg-background hover:bg-accent hover:text-accent-foreground transition-colors"
                        data-participant-id="<?= $participant['id'] ?>"
                        data-participant-name="<?= h($participant['name']) ?>"
                        data-battle-id="<?= $battleId ?>"
                        title="Hide participant">
                        <i data-lucide="eye-off" class="h-3 w-3"></i>
                    </button>
                <?php endif; ?>
                <button type="button"
                    class="delete-participant-btn inline-flex items-center justify-center rounded-md h-8 w-8 border border-input bg-background hover:bg-destructive hover:text-destructive-foreground transition-colors"
                    data-participant-id="<?= $participant['id'] ?>"
                    data-participant-name="<?= h($participant['name']) ?>"
                    data-battle-id="<?= $battleId ?>"
                    title="Remove participant">
                    <i data-lucide="trash-2" class="h-3 w-3"></i>
                </button>
            </div>
        </td>
    </tr>
<?php
    return ob_get_clean();
}
?>