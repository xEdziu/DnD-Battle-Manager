<?php
// Add Participant Component
// Form for adding new participants from presets

function renderAddParticipantForm($presets, $battleId)
{
?>
    <div class="mb-6 p-6 bg-card rounded-lg border border-border">
        <h3 class="text-lg font-semibold mb-4 flex items-center">
            <i data-lucide="user-plus" class="mr-2 h-5 w-5"></i>
            Add Participants
        </h3>

        <!-- Preset Type Filter -->
        <div class="mb-4">
            <div class="flex items-center space-x-2 mb-2">
                <span class="text-sm text-muted-foreground">Filter presets by type:</span>
            </div>
            <div class="flex items-center space-x-1 bg-muted rounded-lg p-1">
                <button type="button"
                    class="preset-type-filter active px-3 py-1 text-xs font-medium rounded-md transition-colors bg-background text-foreground shadow-sm"
                    data-type="all">
                    <i data-lucide="users" class="mr-1 h-3 w-3"></i>
                    All
                </button>
                <button type="button"
                    class="preset-type-filter px-3 py-1 text-xs font-medium rounded-md transition-colors hover:bg-background/80"
                    data-type="pc">
                    <i data-lucide="user-check" class="mr-1 h-3 w-3 text-emerald-600"></i>
                    PC
                </button>
                <button type="button"
                    class="preset-type-filter px-3 py-1 text-xs font-medium rounded-md transition-colors hover:bg-background/80"
                    data-type="npc">
                    <i data-lucide="user" class="mr-1 h-3 w-3 text-blue-600"></i>
                    NPC
                </button>
                <button type="button"
                    class="preset-type-filter px-3 py-1 text-xs font-medium rounded-md transition-colors hover:bg-background/80"
                    data-type="enemy">
                    <i data-lucide="skull" class="mr-1 h-3 w-3 text-red-600"></i>
                    Enemy
                </button>
            </div>
        </div>

        <!-- Add Participant Form -->
        <form method="post" class="flex flex-wrap items-end gap-4">
            <input type="hidden" name="action" value="add_participant">
            <input type="hidden" name="battle_id" value="<?= $battleId ?>">

            <div class="grid w-full max-w-sm items-center gap-1.5">
                <label class="text-sm font-medium leading-none">Preset</label>
                <select name="preset_id" id="preset-select"
                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    <option value="">Choose preset...</option>
                    <?php foreach ($presets as $preset):
                        $characterType = $preset['character_type'] ?? 'enemy';
                        $typeConfig = [
                            'pc' => ['name' => 'PC', 'icon' => '👤'],
                            'npc' => ['name' => 'NPC', 'icon' => '🤝'],
                            'enemy' => ['name' => 'Enemy', 'icon' => '💀']
                        ];
                        $config = $typeConfig[$characterType] ?? $typeConfig['enemy'];
                    ?>
                        <option value="<?= $preset['id'] ?>" data-character-type="<?= $characterType ?>">
                            <?= $config['icon'] ?> <?= h($preset['name']) ?> (<?= $config['name'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="grid w-full max-w-xs items-center gap-1.5">
                <label class="text-sm font-medium leading-none">Quantity</label>
                <input type="number" name="quantity" value="1" min="1" max="20"
                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
            </div>

            <div class="flex items-center space-x-2 pb-2">
                <input type="checkbox" id="useRoll" name="use_roll"
                    class="peer h-4 w-4 shrink-0 rounded-sm border border-primary ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                <label for="useRoll"
                    class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                    Roll HP
                </label>
            </div>

            <button type="submit"
                class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                <i data-lucide="plus" class="mr-2 h-4 w-4"></i>
                Add Participant
            </button>
        </form>
    </div>
<?php
}
?>