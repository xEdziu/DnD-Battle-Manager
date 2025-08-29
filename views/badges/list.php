<?php
$badges = $badgeManager->getAllBadges();
$availableColors = $badgeManager->getAvailableColors();
$availableIcons = $badgeManager->getAvailableIcons();

function getBadgeClasses($color)
{
    $colorMap = [
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

    return $colorMap[$color] ?? 'bg-blue-100 text-blue-700 border-blue-200';
}
?>

<!-- Page Header -->
<div class="flex items-center justify-between">
    <div>
        <h2 class="text-3xl font-bold tracking-tight flex items-center">
            <i data-lucide="tag" class="mr-3 h-8 w-8 text-dnd-royal"></i>
            Badge Management
        </h2>
        <p class="text-muted-foreground">
            Create and manage badges for organizing your battles
        </p>
    </div>

    <button id="createBadgeBtn"
        class="inline-flex items-center justify-center rounded-md text-sm font-medium bg-dnd-emerald text-white hover:bg-green-600 h-10 px-4 py-2">
        <i data-lucide="plus" class="mr-2 h-4 w-4"></i>
        Create Badge
    </button>
</div>

<!-- Badge List -->
<div class="rounded-lg border border-border bg-card text-card-foreground shadow-sm">
    <div class="flex flex-col space-y-1.5 p-6 pb-4">
        <h3 class="text-lg font-semibold leading-none tracking-tight flex items-center">
            <i data-lucide="tags" class="mr-2 h-5 w-5 text-dnd-gold"></i>
            Available Badges
        </h3>
        <p class="text-sm text-muted-foreground">
            <?= count($badges) ?> badge<?= count($badges) !== 1 ? 's' : '' ?> configured
        </p>
    </div>
    <div class="p-6 pt-0">
        <?php if (empty($badges)): ?>
            <div
                class="flex flex-col items-center justify-center rounded-lg border border-dashed border-border p-8 text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-muted">
                    <i data-lucide="tag" class="h-8 w-8 text-muted-foreground"></i>
                </div>
                <h3 class="mt-4 text-lg font-semibold">No badges configured</h3>
                <p class="mb-4 mt-2 text-sm text-muted-foreground">
                    Get started by creating your first badge
                </p>
                <button onclick="document.getElementById('createBadgeBtn').click()"
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                    <i data-lucide="plus" class="mr-2 h-4 w-4"></i>
                    Create First Badge
                </button>
            </div>
        <?php else: ?>
            <div class="rounded-md border border-border overflow-hidden">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-border bg-muted/50">
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Preview</th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Name</th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Color</th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Icon</th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Created</th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($badges as $badge): ?>
                            <tr class="border-b border-border hover:bg-muted/50 transition-colors">
                                <td class="p-4 align-middle">
                                    <div
                                        class="inline-flex items-center rounded-full border px-3 py-1 text-sm font-medium <?= getBadgeClasses($badge['color']) ?>">
                                        <i data-lucide="<?= h($badge['icon']) ?>" class="mr-1 h-3 w-3"></i>
                                        <?= h($badge['name']) ?>
                                    </div>
                                </td>
                                <td class="p-4 align-middle">
                                    <div class="font-medium"><?= h($badge['name']) ?></div>
                                </td>
                                <td class="p-4 align-middle">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-4 h-4 rounded-full border border-border"
                                            style="background-color: <?= $availableColors[$badge['color']] ?? '#3B82F6' ?>">
                                        </div>
                                        <span class="text-sm text-muted-foreground capitalize"><?= h($badge['color']) ?></span>
                                    </div>
                                </td>
                                <td class="p-4 align-middle">
                                    <div class="flex items-center space-x-2">
                                        <i data-lucide="<?= h($badge['icon']) ?>" class="h-4 w-4 text-muted-foreground"></i>
                                        <span class="text-sm text-muted-foreground"><?= h($badge['icon']) ?></span>
                                    </div>
                                </td>
                                <td class="p-4 align-middle">
                                    <div class="text-sm text-muted-foreground">
                                        <?= isset($badge['created_at']) ? date('M j, Y', strtotime($badge['created_at'])) : 'Unknown' ?>
                                    </div>
                                </td>
                                <td class="p-4 align-middle">
                                    <div class="flex items-center space-x-2">
                                        <button
                                            class="edit-badge-btn inline-flex items-center justify-center rounded-md text-sm font-medium border border-input bg-background hover:bg-accent hover:text-accent-foreground h-8 w-8"
                                            data-badge-id="<?= $badge['id'] ?>" data-badge-name="<?= h($badge['name']) ?>"
                                            data-badge-color="<?= h($badge['color']) ?>"
                                            data-badge-icon="<?= h($badge['icon']) ?>">
                                            <i data-lucide="edit" class="h-3 w-3"></i>
                                        </button>
                                        <?php if ($badge['id'] > 6): // Don't allow deleting default badges 
                                        ?>
                                            <button
                                                class="delete-badge-btn inline-flex items-center justify-center rounded-md text-sm font-medium border border-destructive bg-destructive text-destructive-foreground hover:bg-destructive/90 h-8 w-8"
                                                data-badge-id="<?= $badge['id'] ?>" data-badge-name="<?= h($badge['name']) ?>">
                                                <i data-lucide="trash-2" class="h-3 w-3"></i>
                                            </button>
                                        <?php else: ?>
                                            <span
                                                class="inline-flex items-center justify-center rounded-md text-xs font-medium bg-muted text-muted-foreground h-8 px-2">
                                                Default
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Badge Usage -->
<div class="rounded-lg border border-border bg-card text-card-foreground shadow-sm">
    <div class="flex flex-col space-y-1.5 p-6 pb-4">
        <h3 class="text-lg font-semibold leading-none tracking-tight flex items-center">
            <i data-lucide="info" class="mr-2 h-5 w-5 text-dnd-royal"></i>
            Badge Usage
        </h3>
    </div>
    <div class="p-6 pt-0">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
                <h4 class="font-medium">How to use badges:</h4>
                <ul class="text-sm text-muted-foreground space-y-1">
                    <li>• Badges help organize and categorize your battles</li>
                    <li>• Each battle can have one badge assigned</li>
                    <li>• Default badges cannot be deleted</li>
                    <li>• Custom badges can be created, edited, and removed</li>
                </ul>
            </div>
            <div class="space-y-2">
                <h4 class="font-medium">Available colors:</h4>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($availableColors as $colorName => $colorHex): ?>
                        <div class="flex items-center space-x-1">
                            <div class="w-3 h-3 rounded-full border border-border"
                                style="background-color: <?= $colorHex ?>"></div>
                            <span class="text-xs text-muted-foreground capitalize"><?= $colorName ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Available colors and icons from PHP
        const availableColors = <?= json_encode($availableColors) ?>;
        const availableIcons = <?= json_encode($availableIcons) ?>;

        // Create Badge Button
        document.getElementById('createBadgeBtn').addEventListener('click', function() {
            showBadgeModal('create', {
                id: '',
                name: '',
                color: 'blue',
                icon: 'zap'
            });
        });

        // Edit Badge Buttons
        document.querySelectorAll('.edit-badge-btn').forEach(button => {
            button.addEventListener('click', function() {
                const badgeData = {
                    id: this.dataset.badgeId,
                    name: this.dataset.badgeName,
                    color: this.dataset.badgeColor,
                    icon: this.dataset.badgeIcon
                };
                showBadgeModal('edit', badgeData);
            });
        });

        // Delete Badge Buttons
        document.querySelectorAll('.delete-badge-btn').forEach(button => {
            button.addEventListener('click', function() {
                const badgeId = this.dataset.badgeId;
                const badgeName = this.dataset.badgeName;

                Swal.fire({
                    title: 'Delete Badge?',
                    text: `Are you sure you want to delete "${badgeName}"? This will reset all battles using this badge to the default badge.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        confirmButton: 'bg-dnd-crimson hover:bg-red-600 text-white',
                        cancelButton: 'bg-muted hover:bg-muted/80 text-muted-foreground'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = 'index.php?page=badges';

                        const actionInput = document.createElement('input');
                        actionInput.type = 'hidden';
                        actionInput.name = 'action';
                        actionInput.value = 'delete_badge';

                        const badgeIdInput = document.createElement('input');
                        badgeIdInput.type = 'hidden';
                        badgeIdInput.name = 'badge_id';
                        badgeIdInput.value = badgeId;

                        form.appendChild(actionInput);
                        form.appendChild(badgeIdInput);
                        document.body.appendChild(form);

                        form.submit();
                    }
                });
            });
        });

        function showBadgeModal(mode, badgeData) {
            const isEdit = mode === 'edit';
            const title = isEdit ? 'Edit Badge' : 'Create Badge';

            // Generate color options
            let colorOptions = '';
            Object.entries(availableColors).forEach(([colorName, colorHex]) => {
                const selected = badgeData.color === colorName ? 'selected' : '';
                colorOptions +=
                    `<option value="${colorName}" ${selected} data-color="${colorHex}">${colorName.charAt(0).toUpperCase() + colorName.slice(1)}</option>`;
            });

            // Generate icon options  
            let iconOptions = '';
            availableIcons.forEach(icon => {
                const selected = badgeData.icon === icon ? 'selected' : '';
                iconOptions += `<option value="${icon}" ${selected}>${icon}</option>`;
            });

            Swal.fire({
                title: title,
                html: `
                <div class="space-y-4 text-left">
                    <div>
                        <label class="block text-sm font-medium mb-1">Badge Name</label>
                        <input type="text" id="badgeName" value="${badgeData.name}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                               placeholder="Enter badge name">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Color</label>
                        <select id="badgeColor" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            ${colorOptions}
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Icon</label>
                        <select id="badgeIcon" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            ${iconOptions}
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Preview</label>
                        <div id="badgePreview" class="inline-flex items-center rounded-full border px-3 py-1 text-sm font-medium">
                            <i id="previewIcon" data-lucide="${badgeData.icon}" class="mr-1 h-3 w-3"></i>
                            <span id="previewText">${badgeData.name || 'Badge Name'}</span>
                        </div>
                    </div>
                </div>
            `,
                showCancelButton: true,
                confirmButtonText: isEdit ? 'Update Badge' : 'Create Badge',
                cancelButtonText: 'Cancel',
                customClass: {
                    confirmButton: 'bg-dnd-emerald hover:bg-green-600 text-white',
                    cancelButton: 'bg-muted hover:bg-muted/80 text-muted-foreground'
                },
                didOpen: () => {
                    const nameInput = document.getElementById('badgeName');
                    const colorSelect = document.getElementById('badgeColor');
                    const iconSelect = document.getElementById('badgeIcon');
                    const preview = document.getElementById('badgePreview');
                    const previewIcon = document.getElementById('previewIcon');
                    const previewText = document.getElementById('previewText');

                    function updatePreview() {
                        const name = nameInput.value || 'Badge Name';
                        const color = colorSelect.value;
                        const icon = iconSelect.value;

                        previewText.textContent = name;

                        // Remove old icon and create new one for proper Lucide rendering
                        const oldIcon = document.getElementById('previewIcon');
                        if (oldIcon) {
                            oldIcon.remove();
                        }
                        const newIcon = document.createElement('i');
                        newIcon.id = 'previewIcon';
                        newIcon.setAttribute('data-lucide', icon);
                        newIcon.className = 'mr-1 h-3 w-3';
                        preview.insertBefore(newIcon, previewText);

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

                        // Reset classes
                        preview.className =
                            'inline-flex items-center rounded-full border px-3 py-1 text-sm font-medium ' +
                            (colorClasses[color] || 'bg-blue-100 text-blue-700 border-blue-200');

                        // Reinitialize lucide icons
                        lucide.createIcons();
                    }

                    nameInput.addEventListener('input', updatePreview);
                    colorSelect.addEventListener('change', updatePreview);
                    iconSelect.addEventListener('change', updatePreview);

                    // Set initial values and trigger update
                    setTimeout(() => {
                        nameInput.value = badgeData.name;
                        colorSelect.value = badgeData.color;
                        iconSelect.value = badgeData.icon;
                        updatePreview();
                    }, 100);
                },
                preConfirm: () => {
                    const name = document.getElementById('badgeName').value.trim();
                    const color = document.getElementById('badgeColor').value;
                    const icon = document.getElementById('badgeIcon').value;

                    if (!name) {
                        Swal.showValidationMessage('Please enter a badge name');
                        return false;
                    }

                    return {
                        name,
                        color,
                        icon
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'index.php?page=badges';

                    const actionInput = document.createElement('input');
                    actionInput.type = 'hidden';
                    actionInput.name = 'action';
                    actionInput.value = isEdit ? 'edit_badge' : 'create_badge';

                    if (isEdit) {
                        const badgeIdInput = document.createElement('input');
                        badgeIdInput.type = 'hidden';
                        badgeIdInput.name = 'badge_id';
                        badgeIdInput.value = badgeData.id;
                        form.appendChild(badgeIdInput);
                    }

                    const nameInput = document.createElement('input');
                    nameInput.type = 'hidden';
                    nameInput.name = 'name';
                    nameInput.value = result.value.name;

                    const colorInput = document.createElement('input');
                    colorInput.type = 'hidden';
                    colorInput.name = 'color';
                    colorInput.value = result.value.color;

                    const iconInput = document.createElement('input');
                    iconInput.type = 'hidden';
                    iconInput.name = 'icon';
                    iconInput.value = result.value.icon;

                    form.appendChild(actionInput);
                    form.appendChild(nameInput);
                    form.appendChild(colorInput);
                    form.appendChild(iconInput);

                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    });
</script>