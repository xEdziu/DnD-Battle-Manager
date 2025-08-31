<?php
// Battle Detail View - Modular Version
// Main entry point that combines all components

$battleId = intval($_GET['battle']);
$battleManager = new BattleManager();
$badgeManager = new BadgeManager();
$presetManager = new PresetManager();

$battle = $battleManager->getBattleById($battleId);
$badges = $badgeManager->getAllBadges();
$presets = $presetManager->getAllPresets();

// Include helper functions
function getBadgeDisplay($battle, $badges, $battleId)
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

    if (!$badge) {
        $badge = ['name' => 'Active Battle', 'color' => 'gold', 'icon' => 'zap'];
    }

    // Color class mapping
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
    $colorClass = $colorClasses[$badge['color']] ?? 'bg-blue-100 text-blue-700 border-blue-200';

    return sprintf(
        '<button onclick="changeBattleBadge(%d, \'%s\', %d)" 
                class="inline-flex items-center rounded-full border px-3 py-1 text-sm font-medium transition-colors hover:bg-opacity-80 %s" 
                title="Click to change badge">
            <i data-lucide="%s" class="mr-1 h-3 w-3"></i>
            %s
        </button>',
        $battleId,
        addslashes($battle['name']),
        $badge['id'],
        $colorClass,
        $badge['icon'],
        htmlspecialchars($badge['name'])
    );
}

// Include component files
require_once 'components/battle-header.php';
require_once 'components/add-participant.php';
require_once 'components/participants-table.php';
require_once 'components/battle-actions.php';

if (!$battle) {
    header('Location: index.php');
    exit();
}

$participants = $battle['participants'] ?? [];
?>

<div class="container mx-auto p-6">
    <?php renderBattleHeader($battle, $badges, $battleId); ?>

    <div class="grid grid-cols-1 gap-6">
        <!-- Add Participants Section -->
        <?php renderAddParticipantForm($presets, $battleId); ?>

        <!-- Battle Actions Section -->
        <?php if (!empty($participants)): ?>
            <?php renderBattleActions(); ?>
        <?php endif; ?>

        <!-- Participants Table -->
        <div class="bg-card rounded-lg border border-border p-6">
            <form id="battleForm" method="post">
                <input type="hidden" name="battle_id" value="<?= $battleId ?>">

                <?php if (empty($participants)): ?>
                    <div class="text-center py-12">
                        <i data-lucide="users" class="mx-auto h-12 w-12 text-muted-foreground"></i>
                        <h3 class="mt-4 text-lg font-semibold">No participants yet</h3>
                        <p class="mt-2 text-sm text-muted-foreground">
                            Get started by adding participants from presets above.
                        </p>
                    </div>
                <?php else: ?>
                    <?php renderParticipantsTable($participants, $battleId); ?>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>

<!-- Hidden form for HP adjustments and removal actions -->
<form id="ajaxForm" method="post" style="display: none;">
    <input type="hidden" name="action" id="ajaxAction">
    <input type="hidden" name="battle_id" id="ajaxBattleId" value="<?= $battleId ?>">
    <input type="hidden" name="participant_ids" id="ajaxParticipantIds">
    <input type="hidden" name="amount" id="ajaxAmount">
</form>

<script>
    // Badge changing functionality (kept inline for simplicity)
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

                        // Update preview colors
                        const colorClasses = {
                            'crimson': 'bg-dnd-crimson/10 text-dnd-crimson border-dnd-crimson/20',
                            'gold': 'bg-dnd-gold/10 text-dnd-gold border-dnd-gold/20',
                            'emerald': 'bg-dnd-emerald/10 text-dnd-emerald border-dnd-emerald/20',
                            'royal': 'bg-dnd-royal/10 text-dnd-royal border-dnd-royal/20',
                            'shadow': 'bg-dnd-shadow/10 text-dnd-shadow border-dnd-shadow/20',
                            'stone': 'bg-dnd-stone/10 text-dnd-stone border-dnd-stone/20',
                            'purple': 'bg-purple-100 text-purple-700 border-purple-200',
                            'pink': 'bg-pink-100 text-pink-700 border-pink-200',
                            'orange': 'bg-orange-100 text-orange-700 border-orange-200',
                            'cyan': 'bg-cyan-100 text-cyan-700 border-cyan-200',
                            'gray': 'bg-gray-100 text-gray-700 border-gray-200'
                        };

                        preview.className = 'inline-flex items-center rounded-full border px-3 py-1 text-sm font-medium ' +
                            (colorClasses[selectedBadge.color] || 'bg-blue-100 text-blue-700 border-blue-200');

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
                // Submit the form
                const form = document.createElement('form');
                form.method = 'POST';

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

                const sourceInput = document.createElement('input');
                sourceInput.type = 'hidden';
                sourceInput.name = 'source';
                sourceInput.value = 'battle_detail';

                form.appendChild(actionInput);
                form.appendChild(battleIdInput);
                form.appendChild(badgeIdInput);
                form.appendChild(sourceInput);

                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    // Load modular JavaScript (using regular script tags for browser compatibility)

    // Fallback JavaScript for essential functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Participant row click handlers for modal
        document.querySelectorAll('.participant-row').forEach(row => {
            row.addEventListener('click', function(e) {
                // Don't show modal if clicking on interactive elements
                if (e.target.closest('input, button, .delete-participant-btn')) {
                    return;
                }

                const participantData = {
                    id: this.dataset.participantId,
                    name: this.dataset.participantName,
                    skills: this.dataset.participantSkills,
                    actions: this.dataset.participantActions,
                    notes: this.dataset.participantNotes,
                    ac: this.dataset.participantAc,
                    hpCurrent: this.dataset.participantHpCurrent,
                    hpMax: this.dataset.participantHpMax,
                    str: this.dataset.participantStr,
                    dex: this.dataset.participantDex,
                    con: this.dataset.participantCon,
                    int: this.dataset.participantInt,
                    wis: this.dataset.participantWis,
                    cha: this.dataset.participantCha,
                    passive: this.dataset.participantPassive,
                    type: this.dataset.participantType
                };

                showParticipantDetails(participantData);
            });
        });

        // Note: Initiative and participant management is now handled by modular JavaScript
        // See assets/js/modules/initiative.js and other modules
    });

    // Function to show participant details modal
    function showParticipantDetails(participant) {
        // Character type configuration
        const typeConfig = {
            'pc': {
                icon: 'user-check',
                color: 'text-emerald-600',
                name: 'PC'
            },
            'npc': {
                icon: 'user',
                color: 'text-blue-600',
                name: 'NPC'
            },
            'enemy': {
                icon: 'skull',
                color: 'text-red-600',
                name: 'Enemy'
            }
        };
        const config = typeConfig[participant.type] || typeConfig['enemy'];

        // Calculate ability modifiers
        const calculateModifier = (score) => Math.floor((score - 10) / 2);
        const formatModifier = (modifier) => modifier >= 0 ? `+${modifier}` : `${modifier}`;

        // HP percentage for color coding
        const hpPercentage = participant.hpMax > 0 ? (participant.hpCurrent / participant.hpMax) * 100 : 0;
        const hpColor = hpPercentage > 75 ? 'text-emerald-600' : (hpPercentage > 25 ? 'text-yellow-600' : 'text-red-600');

        // Build ability scores section
        const abilities = [{
                name: 'STR',
                value: participant.str
            },
            {
                name: 'DEX',
                value: participant.dex
            },
            {
                name: 'CON',
                value: participant.con
            },
            {
                name: 'INT',
                value: participant.int
            },
            {
                name: 'WIS',
                value: participant.wis
            },
            {
                name: 'CHA',
                value: participant.cha
            }
        ];

        const abilityScoresHTML = abilities.map(ability => `
        <div class="text-center">
            <div class="font-medium">${ability.name}</div>
            <div>${ability.value}</div>
            <div class="text-gray-500">(${formatModifier(calculateModifier(ability.value))})</div>
        </div>
    `).join('');

        // Build details sections
        let detailsSections = '';

        if (participant.skills) {
            detailsSections += `
            <div>
                <h4 class="font-medium text-gray-700 mb-2 flex items-center">
                    <i data-lucide="zap" class="h-4 w-4 mr-1"></i>
                    Skills
                </h4>
                <div class="bg-gray-50 p-3 rounded text-sm whitespace-pre-wrap">${participant.skills}</div>
            </div>
        `;
        }

        if (participant.actions) {
            detailsSections += `
            <div>
                <h4 class="font-medium text-gray-700 mb-2 flex items-center">
                    <i data-lucide="sword" class="h-4 w-4 mr-1"></i>
                    Actions
                </h4>
                <div class="bg-gray-50 p-3 rounded text-sm whitespace-pre-wrap">${participant.actions}</div>
            </div>
        `;
        }

        if (participant.notes) {
            detailsSections += `
            <div>
                <h4 class="font-medium text-gray-700 mb-2 flex items-center">
                    <i data-lucide="file-text" class="h-4 w-4 mr-1"></i>
                    Notes
                </h4>
                <div class="bg-gray-50 p-3 rounded text-sm whitespace-pre-wrap">${participant.notes}</div>
            </div>
        `;
        }

        if (!participant.skills && !participant.actions && !participant.notes) {
            detailsSections = `
            <div class="text-center text-gray-500 py-4">
                <i data-lucide="info" class="h-8 w-8 mx-auto mb-2 opacity-50"></i>
                <p>No additional details available for this participant.</p>
            </div>
        `;
        }

        const modalContent = `
        <div class="text-left space-y-4">
            <!-- Basic Stats -->
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="font-medium">AC:</span>
                        <span class="bg-gray-100 px-2 py-1 rounded text-sm font-semibold">${participant.ac}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium">HP:</span>
                        <span class="${hpColor} font-semibold">${participant.hpCurrent}/${participant.hpMax}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium">Passive Perception:</span>
                        <span class="bg-gray-100 px-2 py-1 rounded text-sm font-semibold">${participant.passive}</span>
                    </div>
                </div>
                <div class="space-y-1">
                    <div class="text-sm font-medium text-gray-600">Ability Scores</div>
                    <div class="grid grid-cols-3 gap-1 text-xs">
                        ${abilityScoresHTML}
                    </div>
                </div>
            </div>

            ${detailsSections}
        </div>
    `;

        Swal.fire({
            title: `
            <div class="flex items-center justify-center space-x-2">
                <i data-lucide="${config.icon}" class="h-5 w-5 ${config.color}"></i>
                <span>${participant.name}</span>
                <span class="text-sm ${config.color} bg-gray-100 px-2 py-1 rounded">${config.name}</span>
            </div>
        `,
            html: modalContent,
            width: 600,
            showConfirmButton: true,
            confirmButtonText: 'Close',
            customClass: {
                confirmButton: 'bg-blue-600 hover:bg-blue-700 text-white',
                htmlContainer: 'text-left'
            },
            didOpen: () => {
                // Re-render lucide icons in the modal
                setTimeout(() => {
                    if (window.lucide) {
                        window.lucide.createIcons();
                    }
                }, 100);
            }
        });
    }
</script>

<!-- Load the modular JavaScript -->
<script type="module" src="assets/js/battle-detail.js"></script>