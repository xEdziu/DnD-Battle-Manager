<?php
$battleManager = new BattleManager();
$badgeManager = new BadgeManager();
$battles = $battleManager->getAllBattles();
$badges = $badgeManager->getAllBadges();

function getBadgeDisplay($battle, $badges)
{
    $badgeId = $battle['badge_id'] ?? 1;
    $badge = null;

    // Find the badge
    foreach ($badges as $b) {
        if ($b['id'] == $badgeId) {
            $badge = $b;
            break;
        }
    }

    // Fallback to default badge
    if (!$badge) {
        $badge = ['name' => 'Active Battle', 'color' => 'gold', 'icon' => 'zap'];
    }

    $colorClasses = [
        'crimson' => 'bg-dnd-crimson/10 text-dnd-crimson border-dnd-crimson/20',
        'gold' => 'bg-dnd-gold/10 text-dnd-gold border-dnd-gold/20',
        'emerald' => 'bg-dnd-emerald/10 text-dnd-emerald border-dnd-emerald/20',
        'royal' => 'bg-dnd-royal/10 text-dnd-royal border-dnd-royal/20',
        'shadow' => 'bg-dnd-shadow/10 text-dnd-shadow border-dnd-shadow/20',
        'stone' => 'bg-dnd-stone/10 text-dnd-stone border-dnd-stone/20',
        'purple' => 'bg-purple-100 text-purple-700 border-purple-200',
        'pink' => 'bg-pink-100 text-pink-700 border-pink-200',
        'orange' => 'bg-orange-100 text-orange-700 border-orange-200',
        'cyan' => 'bg-cyan-100 text-cyan-700 border-cyan-200',
        'gray' => 'bg-gray-100 text-gray-700 border-gray-200'
    ];

    $classes = $colorClasses[$badge['color']] ?? 'bg-blue-100 text-blue-700 border-blue-200';

    return [
        'html' => '<i data-lucide="' . h($badge['icon']) . '" class="mr-1 h-3 w-3"></i>' . h($badge['name']),
        'classes' => $classes
    ];
}
?>

<!-- Page Header -->
<div class="flex items-center justify-between">
    <div>
        <h2 class="text-3xl font-bold tracking-tight">Saved Battles</h2>
        <p class="text-muted-foreground">Manage your D&D battle encounters</p>
    </div>
    <button id="newBattleBtn" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
        <i data-lucide="plus" class="mr-2 h-4 w-4"></i>
        New Battle
    </button>
</div>

<?php if (!empty($battles)): ?>
    <!-- Battles Grid -->
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($battles as $battle):
            $badgeDisplay = getBadgeDisplay($battle, $badges);
        ?>
            <div class="battle-card rounded-lg border border-border bg-card text-card-foreground shadow-sm hover:shadow-md transition-shadow">
                <div class="p-6 pb-4">
                    <div class="flex items-start justify-between">
                        <div class="space-y-1 flex-1">
                            <h3 class="font-semibold leading-none tracking-tight text-lg">
                                <?= h($battle['name']) ?>
                            </h3>
                            <?php if (!empty($battle['description'])): ?>
                                <p class="text-sm text-muted-foreground line-clamp-2">
                                    <?= h($battle['description']) ?>
                                </p>
                            <?php endif; ?>
                            <div class="flex items-center text-sm text-muted-foreground">
                                <i data-lucide="users" class="mr-1 h-3 w-3"></i>
                                <?= count($battle['participants'] ?? []) ?> participants
                            </div>
                        </div>
                        <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors cursor-pointer hover:opacity-80 <?= $badgeDisplay['classes'] ?>"
                            onclick="changeBattleBadge(<?= $battle['id'] ?>, '<?= h($battle['name']) ?>', <?= $battle['badge_id'] ?? 1 ?>)">
                            <?= $badgeDisplay['html'] ?>
                        </div>
                    </div>
                </div>
                <div class="flex items-center p-6 pt-0">
                    <a href="index.php?battle=<?= $battle['id'] ?>"
                        class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-dnd-royal text-white hover:bg-dnd-royal/90 h-9 px-4 py-2 flex-1 mr-2">
                        <i data-lucide="play" class="mr-2 h-4 w-4"></i>
                        Open Battle
                    </a>
                    <button class="edit-battle-btn inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 w-9 mr-2"
                        data-battle-id="<?= $battle['id'] ?>"
                        data-battle-name="<?= h($battle['name']) ?>"
                        data-battle-description="<?= h($battle['description'] ?? '') ?>"
                        title="Edit Battle">
                        <i data-lucide="edit" class="h-4 w-4"></i>
                    </button>
                    <button class="delete-battle-btn inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 w-9"
                        data-battle-id="<?= $battle['id'] ?>"
                        data-battle-name="<?= h($battle['name']) ?>"
                        title="Delete Battle">
                        <i data-lucide="trash-2" class="h-4 w-4 text-destructive"></i>
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <!-- Empty State -->
    <div class="flex flex-col items-center justify-center rounded-lg border border-dashed border-border p-8 text-center animate-in fade-in-50">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-dnd-gold/10">
            <i data-lucide="sword" class="h-8 w-8 text-dnd-gold"></i>
        </div>
        <h3 class="mt-4 text-lg font-semibold">No battles yet</h3>
        <p class="mb-4 mt-2 text-sm text-muted-foreground max-w-sm">
            You haven't created any battles yet. Start your first epic encounter by creating a new battle.
        </p>
        <button id="newBattleBtn2" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
            <i data-lucide="plus" class="mr-2 h-4 w-4"></i>
            Create Your First Battle
        </button>
    </div>
<?php endif; ?>

<script>
    // Function to change battle badge
    function changeBattleBadge(battleId, battleName, currentBadgeId) {
        const badges = <?= json_encode($badges) ?>;

        // Generate badge options
        let badgeOptions = '';
        badges.forEach(badge => {
            const selected = badge.id == currentBadgeId ? 'selected' : '';
            badgeOptions += `<option value="${badge.id}" ${selected}>${badge.name}</option>`;
        });

        Swal.fire({
            title: 'Change Badge',
            text: `Select a badge for "${battleName}"`,
            html: `
            <div class="text-left">
                <label class="block text-sm font-medium mb-2">Select Badge:</label>
                <select id="badgeSelect" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    ${badgeOptions}
                </select>
                <div class="mt-3">
                    <label class="block text-sm font-medium mb-1">Preview:</label>
                    <div id="badgePreview" class="inline-flex items-center rounded-full border px-3 py-1 text-sm font-medium">
                        <i id="previewIcon" data-lucide="zap" class="mr-1 h-3 w-3"></i>
                        <span id="previewText">Active Battle</span>
                    </div>
                </div>
            </div>
        `,
            showCancelButton: true,
            confirmButtonText: 'Update Badge',
            cancelButtonText: 'Cancel',
            customClass: {
                confirmButton: 'bg-dnd-emerald hover:bg-green-600 text-white',
                cancelButton: 'bg-muted hover:bg-muted/80 text-muted-foreground'
            },
            didOpen: () => {
                const badgeSelect = document.getElementById('badgeSelect');
                const preview = document.getElementById('badgePreview');

                function updatePreview() {
                    const selectedBadgeId = badgeSelect.value;
                    const selectedBadge = badges.find(b => b.id == selectedBadgeId);

                    if (selectedBadge) {
                        // Update the icon by replacing the entire preview content
                        preview.innerHTML = `<i data-lucide="${selectedBadge.icon}" class="mr-1 h-3 w-3"></i><span>${selectedBadge.name}</span>`;

                        // Update preview classes based on color
                        const colorClasses = {
                            'crimson': 'bg-red-100 text-red-700 border-red-200',
                            'gold': 'bg-yellow-100 text-yellow-700 border-yellow-200',
                            'emerald': 'bg-green-100 text-green-700 border-green-200',
                            'royal': 'bg-blue-100 text-blue-700 border-blue-200',
                            'shadow': 'bg-gray-800 text-gray-100 border-gray-700',
                            'stone': 'bg-gray-100 text-gray-700 border-gray-200',
                            'purple': 'bg-purple-100 text-purple-700 border-purple-200',
                            'pink': 'bg-pink-100 text-pink-700 border-pink-200',
                            'orange': 'bg-orange-100 text-orange-700 border-orange-200',
                            'cyan': 'bg-cyan-100 text-cyan-700 border-cyan-200',
                            'gray': 'bg-gray-100 text-gray-700 border-gray-200'
                        };

                        preview.className = 'inline-flex items-center rounded-full border px-3 py-1 text-sm font-medium ' +
                            (colorClasses[selectedBadge.color] || 'bg-blue-100 text-blue-700 border-blue-200');

                        // Reinitialize lucide icons
                        lucide.createIcons();
                    }
                }

                badgeSelect.addEventListener('change', updatePreview);
                updatePreview(); // Initial update
            },
            preConfirm: () => {
                const selectedBadgeId = document.getElementById('badgeSelect').value;
                return selectedBadgeId;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'index.php';

                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'update_battle_badge';

                const battleIdInput = document.createElement('input');
                battleIdInput.type = 'hidden';
                battleIdInput.name = 'battle_id';
                battleIdInput.value = battleId;

                const badgeIdInput = document.createElement('input');
                badgeIdInput.type = 'hidden';
                badgeIdInput.name = 'badge_id';
                badgeIdInput.value = result.value;

                form.appendChild(actionInput);
                form.appendChild(battleIdInput);
                form.appendChild(badgeIdInput);

                document.body.appendChild(form);
                form.submit();
            }
        });
    }
</script>