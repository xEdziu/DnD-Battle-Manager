<?php
$presetManager = new PresetManager();
$presets = $presetManager->getAllPresets();

// Check if editing an existing preset
$editingPreset = null;
if (isset($_GET['edit_id'])) {
    $editId = intval($_GET['edit_id']);
    $editingPreset = $presetManager->getPresetById($editId);
}
?>

<!-- Page Header -->
<div class="flex items-center justify-between">
    <div>
        <h2 class="text-3xl font-bold tracking-tight">Character Presets</h2>
        <p class="text-muted-foreground">Create and manage reusable character templates</p>
    </div>
    <a href="index.php"
        class="inline-flex items-center justify-center rounded-md text-sm font-medium border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
        <i data-lucide="arrow-left" class="mr-2 h-4 w-4"></i>
        Back to Battles
    </a>
</div>

<!-- Presets Table -->
<div class="rounded-lg border border-border bg-card text-card-foreground shadow-sm">
    <div class="flex flex-col space-y-1.5 p-6 pb-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <i data-lucide="library" class="h-5 w-5 text-dnd-royal"></i>
                <h3 class="text-lg font-semibold leading-none tracking-tight">Saved Presets</h3>
            </div>

            <!-- Character Type Filters -->
            <div class="flex items-center space-x-2">
                <span class="text-sm text-muted-foreground">Filter by type:</span>
                <div class="flex items-center space-x-1 bg-muted rounded-lg p-1">
                    <button type="button"
                        class="character-type-filter active px-3 py-1 text-xs font-medium rounded-md transition-colors bg-background text-foreground shadow-sm"
                        data-type="all">
                        <i data-lucide="users" class="mr-1 h-3 w-3"></i>
                        All
                    </button>
                    <button type="button"
                        class="character-type-filter px-3 py-1 text-xs font-medium rounded-md transition-colors hover:bg-background/80"
                        data-type="pc">
                        <i data-lucide="user-check" class="mr-1 h-3 w-3 text-emerald-600"></i>
                        PC
                    </button>
                    <button type="button"
                        class="character-type-filter px-3 py-1 text-xs font-medium rounded-md transition-colors hover:bg-background/80"
                        data-type="npc">
                        <i data-lucide="user" class="mr-1 h-3 w-3 text-blue-600"></i>
                        NPC
                    </button>
                    <button type="button"
                        class="character-type-filter px-3 py-1 text-xs font-medium rounded-md transition-colors hover:bg-background/80"
                        data-type="enemy">
                        <i data-lucide="skull" class="mr-1 h-3 w-3 text-red-600"></i>
                        Enemy
                    </button>
                </div>
            </div>
        </div>
        <p class="text-sm text-muted-foreground">
            Manage your character and monster templates
        </p>
    </div>
    <div class="p-6 pt-0">
        <?php if (!empty($presets)): ?>
            <div class="rounded-md border border-border overflow-hidden">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-border bg-muted/50">
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">
                                <div class="flex items-center">
                                    <i data-lucide="user" class="mr-2 h-4 w-4"></i>
                                    Name
                                </div>
                            </th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground text-center">
                                <div class="flex items-center justify-center">
                                    <i data-lucide="shield" class="mr-1 h-4 w-4"></i>
                                    AC
                                </div>
                            </th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground text-center">
                                <div class="flex items-center justify-center">
                                    <i data-lucide="heart" class="mr-1 h-4 w-4"></i>
                                    HP
                                </div>
                            </th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground text-center">
                                <div class="flex items-center justify-center">
                                    <i data-lucide="bar-chart-3" class="mr-1 h-4 w-4"></i>
                                    Stats
                                </div>
                            </th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground text-center">
                                <div class="flex items-center justify-center">
                                    <i data-lucide="settings" class="mr-1 h-4 w-4"></i>
                                    Actions
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($presets as $preset):
                            $characterType = $preset['character_type'] ?? 'enemy';
                            $typeConfig = [
                                'pc' => ['name' => 'PC', 'icon' => 'user-check', 'color' => 'emerald-600'],
                                'npc' => ['name' => 'NPC', 'icon' => 'user', 'color' => 'blue-600'],
                                'enemy' => ['name' => 'Enemy', 'icon' => 'skull', 'color' => 'red-600']
                            ];
                            $config = $typeConfig[$characterType] ?? $typeConfig['enemy'];
                        ?>
                            <tr class="preset-row border-b border-border hover:bg-muted/50 transition-colors"
                                data-character-type="<?= $characterType ?>">
                                <td class="p-4 align-middle">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex items-center space-x-1">
                                            <i data-lucide="<?= $config['icon'] ?>"
                                                class="h-4 w-4 text-<?= $config['color'] ?>"></i>
                                            <span
                                                class="text-xs font-medium text-<?= $config['color'] ?>"><?= $config['name'] ?></span>
                                        </div>
                                        <span class="font-medium"><?= h($preset['name']) ?></span>
                                    </div>
                                </td>
                                <td class="p-4 align-middle text-center">
                                    <div
                                        class="inline-flex items-center rounded-full border px-2 py-1 text-xs font-semibold bg-secondary text-secondary-foreground">
                                        <?= $preset['ac'] ?>
                                    </div>
                                </td>
                                <td class="p-4 align-middle text-center">
                                    <div
                                        class="inline-flex items-center rounded-full border px-2 py-1 text-xs font-semibold bg-dnd-crimson/10 text-dnd-crimson">
                                        <?= h($preset['hp']) ?>
                                    </div>
                                </td>
                                <td class="p-4 align-middle text-center">
                                    <div class="grid grid-cols-2 gap-2 text-xs">
                                        <?php
                                        $statDetails = [
                                            'str' => ['name' => 'STR', 'icon' => 'dumbbell', 'color' => 'text-red-600'],
                                            'dex' => ['name' => 'DEX', 'icon' => 'zap', 'color' => 'text-yellow-600'],
                                            'con' => ['name' => 'CON', 'icon' => 'shield-check', 'color' => 'text-green-600'],
                                            'int' => ['name' => 'INT', 'icon' => 'brain', 'color' => 'text-blue-600'],
                                            'wis' => ['name' => 'WIS', 'icon' => 'eye', 'color' => 'text-purple-600'],
                                            'cha' => ['name' => 'CHA', 'icon' => 'sparkles', 'color' => 'text-pink-600']
                                        ];
                                        foreach (['str', 'dex', 'con', 'int', 'wis', 'cha'] as $stat):
                                            $value = $preset[$stat];
                                            $modifier = formatModifier(calculateModifier($value));
                                            $details = $statDetails[$stat];
                                        ?>
                                            <div class="flex items-center justify-between bg-muted rounded px-2 py-1">
                                                <div class="flex items-center space-x-1">
                                                    <i data-lucide="<?= $details['icon'] ?>"
                                                        class="h-3 w-3 <?= $details['color'] ?>"></i>
                                                    <span class="font-medium text-muted-foreground"><?= $details['name'] ?></span>
                                                </div>
                                                <span class="font-bold"><?= $modifier ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                                <td class="p-4 align-middle text-center">
                                    <div class="flex justify-center space-x-2">
                                        <a href="index.php?page=presets&edit_id=<?= $preset['id'] ?>"
                                            class="inline-flex items-center justify-center rounded-md text-sm font-medium border border-input bg-background hover:bg-accent hover:text-accent-foreground h-8 w-8"
                                            title="Edit preset">
                                            <i data-lucide="edit" class="h-4 w-4"></i>
                                        </a>
                                        <a href="index.php?page=presets&action=clone_preset&id=<?= $preset['id'] ?>"
                                            class="clone-preset-link inline-flex items-center justify-center rounded-md text-sm font-medium border border-input bg-background hover:bg-blue-50 hover:text-blue-600 h-8 w-8"
                                            title="Clone preset">
                                            <i data-lucide="copy" class="h-4 w-4"></i>
                                        </a>
                                        <a href="index.php?page=presets&action=delete_preset&id=<?= $preset['id'] ?>"
                                            class="delete-preset-link inline-flex items-center justify-center rounded-md text-sm font-medium border border-input bg-background hover:bg-destructive hover:text-destructive-foreground h-8 w-8"
                                            title="Delete preset"
                                            data-preset-name="<?= h($preset['name']) ?>">
                                            <i data-lucide="trash-2" class="h-4 w-4"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="flex flex-col items-center justify-center p-8 text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-dnd-royal/10">
                    <i data-lucide="users" class="h-8 w-8 text-dnd-royal"></i>
                </div>
                <h3 class="mt-4 text-lg font-semibold">No presets yet</h3>
                <p class="mb-4 mt-2 text-sm text-muted-foreground max-w-sm">
                    Create your first character or monster preset to speed up battle setup.
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Preset Form -->
<div class="rounded-lg border border-border bg-card text-card-foreground shadow-sm">
    <div class="flex flex-col space-y-1.5 p-6 pb-4">
        <h3 class="text-lg font-semibold leading-none tracking-tight flex items-center">
            <i data-lucide="<?= $editingPreset ? 'edit' : 'plus' ?>" class="mr-2 h-5 w-5 text-dnd-emerald"></i>
            <?= $editingPreset ? 'Edit Preset' : 'Create New Preset' ?>
        </h3>
        <p class="text-sm text-muted-foreground">
            <?= $editingPreset ? 'Modify the character template' : 'Create a reusable character or monster template' ?>
        </p>
    </div>
    <div class="p-6 pt-0">
        <form method="post" class="space-y-6">
            <input type="hidden" name="action" value="<?= $editingPreset ? 'edit_preset' : 'add_preset' ?>">
            <?php if ($editingPreset): ?>
                <input type="hidden" name="preset_id" value="<?= $editingPreset['id'] ?>">
            <?php endif; ?>

            <!-- Basic Info -->
            <div class="space-y-4">
                <div class="grid w-full max-w-sm items-center gap-1.5">
                    <label class="text-sm font-medium leading-none">Name</label>
                    <input type="text" name="name" value="<?= h($editingPreset['name'] ?? '') ?>"
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                        placeholder="Enter character name">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="grid w-full items-center gap-1.5">
                        <label class="text-sm font-medium leading-none">Armor Class</label>
                        <input type="number" name="ac" value="<?= $editingPreset['ac'] ?? 10 ?>"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                            min="1" max="30">
                    </div>

                    <div class="grid w-full items-center gap-1.5">
                        <label class="text-sm font-medium leading-none">Hit Points</label>
                        <input type="text" name="hp" value="<?= h($editingPreset['hp'] ?? '') ?>"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                            placeholder="e.g. 2d8+2 or 15">
                    </div>

                    <div class="grid w-full items-center gap-1.5">
                        <label class="text-sm font-medium leading-none">Passive Perception</label>
                        <input type="number" name="passive" value="<?= $editingPreset['passive'] ?? 10 ?>"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                            min="1" max="30">
                    </div>
                </div>
            </div>

            <!-- Character Type -->
            <div class="space-y-4">
                <div class="flex items-center space-x-2">
                    <i data-lucide="users" class="h-5 w-5 text-dnd-gold"></i>
                    <h4 class="text-lg font-medium">Character Type</h4>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <?php
                    $currentType = $editingPreset['character_type'] ?? 'enemy';
                    $characterTypes = [
                        'pc' => [
                            'name' => 'Player Character',
                            'description' => 'Controlled by players',
                            'icon' => 'user-check',
                            'color' => 'emerald'
                        ],
                        'npc' => [
                            'name' => 'Non-Player Character',
                            'description' => 'Friendly or neutral NPCs',
                            'icon' => 'user',
                            'color' => 'blue'
                        ],
                        'enemy' => [
                            'name' => 'Enemy/Monster',
                            'description' => 'Hostile creatures and monsters',
                            'icon' => 'skull',
                            'color' => 'red'
                        ]
                    ];
                    ?>

                    <?php foreach ($characterTypes as $type => $info): ?>
                        <label
                            class="character-type-option relative flex cursor-pointer rounded-lg border p-4 hover:bg-accent/50 transition-colors <?= $currentType === $type ? 'border-emerald-500 bg-emerald-50' : 'border-input' ?>"
                            data-type="<?= $type ?>">
                            <input type="radio" name="character_type" value="<?= $type ?>"
                                class="sr-only character-type-radio" <?= $currentType === $type ? 'checked' : '' ?>>
                            <div class="flex items-start space-x-3">
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-full <?= $type === 'pc' ? 'bg-emerald-100' : ($type === 'npc' ? 'bg-blue-100' : 'bg-red-100') ?>">
                                    <i data-lucide="<?= $info['icon'] ?>"
                                        class="h-4 w-4 <?= $type === 'pc' ? 'text-emerald-600' : ($type === 'npc' ? 'text-blue-600' : 'text-red-600') ?>"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="font-medium text-sm"><?= $info['name'] ?></div>
                                    <div class="text-xs text-muted-foreground"><?= $info['description'] ?></div>
                                </div>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Ability Scores -->
            <div class="space-y-4">
                <div class="flex items-center space-x-2">
                    <i data-lucide="dices" class="h-5 w-5 text-dnd-gold"></i>
                    <h4 class="text-lg font-medium">Ability Scores</h4>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <?php $stats = [
                        'str' => ['name' => 'Strength', 'icon' => 'dumbbell'],
                        'dex' => ['name' => 'Dexterity', 'icon' => 'zap'],
                        'con' => ['name' => 'Constitution', 'icon' => 'shield-check'],
                        'int' => ['name' => 'Intelligence', 'icon' => 'brain'],
                        'wis' => ['name' => 'Wisdom', 'icon' => 'eye'],
                        'cha' => ['name' => 'Charisma', 'icon' => 'sparkles']
                    ]; ?>

                    <?php foreach ($stats as $field => $info):
                        $val = $editingPreset[$field] ?? 10;
                        $modVal = calculateModifier($val);
                    ?>
                        <div class="space-y-2">
                            <label class="text-sm font-medium leading-none flex items-center">
                                <i data-lucide="<?= $info['icon'] ?>" class="mr-1 h-3 w-3"></i>
                                <?= $info['name'] ?>
                            </label>
                            <div class="relative">
                                <input type="number" name="<?= $field ?>" value="<?= $val ?>"
                                    class="ability-score flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 text-center font-medium"
                                    data-field="<?= $field ?>" min="1" max="30">
                                <div class="absolute -bottom-6 left-0 right-0 text-center">
                                    <span class="text-xs text-muted-foreground bg-background px-1 rounded border">
                                        <span id="<?= $field ?>_mod"><?= formatModifier($modVal) ?></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Additional Info -->
            <div class="space-y-4 pt-4">
                <div class="grid w-full items-center gap-1.5">
                    <label class="text-sm font-medium leading-none">Skills</label>
                    <input type="text" name="skills" value="<?= h($editingPreset['skills'] ?? '') ?>"
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                        placeholder="e.g. Stealth +7, Perception +3">
                </div>

                <div class="grid w-full items-center gap-1.5">
                    <label class="text-sm font-medium leading-none">Actions</label>
                    <textarea name="actions"
                        class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                        placeholder="Describe attacks and special abilities"><?= h($editingPreset['actions'] ?? '') ?></textarea>
                </div>

                <div class="grid w-full items-center gap-1.5">
                    <label class="text-sm font-medium leading-none">Notes</label>
                    <textarea name="notes"
                        class="flex min-h-[60px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                        placeholder="Additional notes and details"><?= h($editingPreset['notes'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-between pt-4 border-t border-border">
                <div class="flex items-center space-x-2">
                    <?php if ($editingPreset): ?>
                        <a href="index.php?page=presets"
                            class="inline-flex items-center justify-center rounded-md text-sm font-medium border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
                            <i data-lucide="x" class="mr-2 h-4 w-4"></i>
                            Cancel
                        </a>
                    <?php endif; ?>
                </div>

                <button type="submit"
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium bg-dnd-emerald text-white hover:bg-green-600 h-10 px-4 py-2">
                    <i data-lucide="<?= $editingPreset ? 'save' : 'plus' ?>" class="mr-2 h-4 w-4"></i>
                    <?= $editingPreset ? 'Save Changes' : 'Create Preset' ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Function to calculate D&D 5e ability modifier
    function calculateModifier(score) {
        return Math.floor((score - 10) / 2);
    }

    // Function to format modifier with + or - sign
    function formatModifier(modifier) {
        return modifier >= 0 ? '+' + modifier : modifier.toString();
    }

    // Add event listeners to all ability score inputs
    document.addEventListener('DOMContentLoaded', function() {
        const abilityInputs = document.querySelectorAll('.ability-score');

        abilityInputs.forEach(input => {
            input.addEventListener('input', function() {
                const field = this.getAttribute('data-field');
                const score = parseInt(this.value) || 10;
                const modifier = calculateModifier(score);
                const modifierSpan = document.getElementById(field + '_mod');

                if (modifierSpan) {
                    modifierSpan.textContent = formatModifier(modifier);
                }
            });
        });

        // Handle character type selection
        const characterTypeOptions = document.querySelectorAll('.character-type-option');
        const characterTypeRadios = document.querySelectorAll('.character-type-radio');

        characterTypeOptions.forEach((option, index) => {
            option.addEventListener('click', function(e) {
                const radio = this.querySelector('.character-type-radio');
                const type = this.getAttribute('data-type');

                // Uncheck all radios and remove active styling
                characterTypeRadios.forEach(r => r.checked = false);
                characterTypeOptions.forEach(opt => {
                    opt.classList.remove('border-emerald-500', 'bg-emerald-50',
                        'border-blue-500', 'bg-blue-50', 'border-red-500', 'bg-red-50');
                    opt.classList.add('border-input');
                });

                // Check this radio and add active styling
                radio.checked = true;
                this.classList.remove('border-input');

                if (type === 'pc') {
                    this.classList.add('border-emerald-500', 'bg-emerald-50');
                } else if (type === 'npc') {
                    this.classList.add('border-blue-500', 'bg-blue-50');
                } else if (type === 'enemy') {
                    this.classList.add('border-red-500', 'bg-red-50');
                }
            });
        });

        // Handle character type filtering
        const filterButtons = document.querySelectorAll('.character-type-filter');
        const presetRows = document.querySelectorAll('.preset-row');

        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                const filterType = this.getAttribute('data-type');

                // Update button states
                filterButtons.forEach(btn => {
                    btn.classList.remove('active', 'bg-background', 'text-foreground',
                        'shadow-sm');
                    btn.classList.add('hover:bg-background/80');
                });

                this.classList.add('active', 'bg-background', 'text-foreground', 'shadow-sm');
                this.classList.remove('hover:bg-background/80');

                // Filter preset rows
                presetRows.forEach(row => {
                    const rowType = row.getAttribute('data-character-type');

                    if (filterType === 'all' || rowType === filterType) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });

        // Initialize Lucide icons
        lucide.createIcons();
    });
</script>