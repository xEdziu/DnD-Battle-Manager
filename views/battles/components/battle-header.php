<?php
// Battle Header Component
// Displays battle title, badge, and action buttons

function renderBattleHeader($battle, $badges, $battleId)
{
    $badgeDisplay = getBadgeDisplay($battle, $badges, $battleId);
?>
    <div class="bg-card rounded-lg border border-border p-6 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4 flex-1">
                <div class="flex-1">
                    <div class="flex items-center space-x-3">
                        <h1 class="text-3xl font-bold text-card-foreground"><?= htmlspecialchars($battle['name']) ?></h1>
                        <button class="edit-battle-btn inline-flex items-center justify-center rounded-md text-sm font-medium border border-input bg-background hover:bg-accent hover:text-accent-foreground h-8 w-8"
                            data-battle-id="<?= $battleId ?>"
                            data-battle-name="<?= htmlspecialchars($battle['name']) ?>"
                            data-battle-description="<?= htmlspecialchars($battle['description'] ?? '') ?>"
                            title="Edit Battle Info">
                            <i data-lucide="edit" class="h-4 w-4"></i>
                        </button>
                    </div>
                    <?php if (!empty($battle['description'])): ?>
                        <p class="text-muted-foreground mt-2 text-base">
                            <?= htmlspecialchars($battle['description']) ?>
                        </p>
                    <?php endif; ?>
                    <p class="text-sm text-muted-foreground mt-1">
                        <?= count($battle['participants']) ?> participants in battle
                    </p>
                </div>
            </div>

            <div class="flex items-center space-x-2">
                <div class="flex items-center space-x-2">
                    <?= $badgeDisplay ?>
                </div>
                <a href="index.php"
                    class="inline-flex items-center rounded-md border border-border bg-background px-3 py-2 text-sm font-medium text-foreground hover:bg-accent hover:text-accent-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2">
                    <i data-lucide="arrow-left" class="mr-2 h-4 w-4"></i>
                    Back to Battles
                </a>
            </div>
        </div>
    </div>
<?php
}
?>