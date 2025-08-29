<?php
// Participants Table Component
// The main table with all participants

function renderParticipantsTable($participants, $battleId)
{
?>
    <div class="rounded-lg border border-border bg-card text-card-foreground shadow-sm">
        <div class="flex flex-col space-y-1.5 p-6 pb-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold leading-none tracking-tight flex items-center">
                    <i data-lucide="users" class="mr-2 h-5 w-5 text-dnd-royal"></i>
                    Battle Participants
                </h3>
                <div class="flex items-center space-x-2">
                    <button id="sortByInit" type="button"
                        class="inline-flex items-center justify-center rounded-md text-sm font-medium border border-input bg-background hover:bg-accent hover:text-accent-foreground h-8 px-3">
                        <i data-lucide="arrow-up-down" class="mr-1 h-3 w-3"></i>
                        Sort by Initiative
                    </button>
                    <button id="rollInitiative" type="button"
                        class="inline-flex items-center justify-center rounded-md text-sm font-medium border border-input bg-background hover:bg-accent hover:text-accent-foreground h-8 px-3">
                        <i data-lucide="shuffle" class="mr-1 h-3 w-3"></i>
                        Roll Initiative
                    </button>
                </div>
            </div>
        </div>
        <div class="p-6 pt-0">
            <div class="rounded-md border border-border overflow-hidden">
                <table class="w-full caption-bottom text-sm" id="participants-table">
                    <thead class="[&_tr]:border-b">
                        <tr class="border-b border-border transition-colors hover:bg-muted/50">
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">
                                <input type="checkbox" id="selectAll"
                                    class="h-4 w-4 shrink-0 rounded-sm border border-primary">
                            </th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">
                                Name
                            </th>
                            <th class="h-12 px-4 text-center align-middle font-medium text-muted-foreground">
                                AC
                            </th>
                            <th class="h-12 px-4 text-center align-middle font-medium text-muted-foreground">
                                HP
                            </th>
                            <?php foreach (['STR', 'DEX', 'CON', 'INT', 'WIS', 'CHA'] as $stat): ?>
                                <th class="h-12 px-4 text-center align-middle font-medium text-muted-foreground">
                                    <?= $stat ?>
                                </th>
                            <?php endforeach; ?>
                            <th class="h-12 px-4 text-center align-middle font-medium text-muted-foreground">
                                Passive Perception
                            </th>
                            <th class="h-12 px-4 text-center align-middle font-medium text-muted-foreground">
                                Initiative
                            </th>
                            <th class="h-12 px-4 text-center align-middle font-medium text-muted-foreground">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($participants as $participant):
                            $isDead = $participant['hp_current'] <= 0;
                            $hpPercentage = $participant['hp_max'] > 0 ? ($participant['hp_current'] / $participant['hp_max']) * 100 : 0;
                            $hpColor = $hpPercentage > 75 ? 'text-dnd-emerald' : ($hpPercentage > 25 ? 'text-dnd-gold' : 'text-dnd-crimson');
                        ?>
                            <tr id="participant-row-<?= $participant['id'] ?>"
                                class="participant-row border-b border-border hover:bg-muted/50 transition-colors cursor-pointer <?= $isDead ? 'opacity-50 bg-destructive/5' : '' ?>"
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
                                    <input type="checkbox" name="selected" value="<?= $participant['id'] ?>"
                                        class="participant-checkbox h-4 w-4 shrink-0 rounded-sm border border-primary"
                                        onclick="event.stopPropagation()">
                                </td>
                                <td class="p-4 align-middle">
                                    <div class="flex items-center space-x-2">
                                        <?php if ($isDead): ?>
                                            <i data-lucide="skull" class="h-4 w-4 text-destructive"></i>
                                        <?php else:
                                            // Character type icons
                                            $characterType = $participant['character_type'] ?? 'enemy';
                                            $typeConfig = [
                                                'pc' => ['icon' => 'user-check', 'color' => 'text-emerald-600'],
                                                'npc' => ['icon' => 'user', 'color' => 'text-blue-600'],
                                                'enemy' => ['icon' => 'skull', 'color' => 'text-red-600']
                                            ];
                                            $config = $typeConfig[$characterType] ?? $typeConfig['enemy'];
                                        ?>
                                            <i data-lucide="<?= $config['icon'] ?>" class="h-4 w-4 <?= $config['color'] ?>"></i>
                                        <?php endif; ?>
                                        <input type="text" name="name[<?= $participant['id'] ?>]"
                                            value="<?= h($participant['name']) ?>"
                                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring <?= $isDead ? 'bg-destructive/10 text-destructive' : '' ?>"
                                            onclick="event.stopPropagation()">
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
                                    <input type="number" name="init[<?= $participant['id'] ?>]"
                                        value="<?= $participant['initiative'] ?>"
                                        class="initiative-input flex h-9 w-16 rounded-md border border-input bg-transparent px-2 py-1 text-sm text-center shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                        onclick="event.stopPropagation()">
                                </td>
                                <td class="p-4 align-middle text-center">
                                    <button type="button"
                                        class="delete-participant-btn inline-flex items-center justify-center rounded-md h-8 w-8 border border-input bg-background hover:bg-destructive hover:text-destructive-foreground transition-colors"
                                        data-participant-id="<?= $participant['id'] ?>"
                                        data-participant-name="<?= h($participant['name']) ?>"
                                        data-battle-id="<?= $battleId ?>"
                                        title="Remove participant">
                                        <i data-lucide="trash-2" class="h-3 w-3"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php
}
?>