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
require_once 'components/hidden-participants-table.php';
require_once 'components/battle-actions.php';

if (!$battle) {
    header('Location: index.php');
    exit();
}

$participants = $battle['participants'] ?? [];
$hiddenParticipants = $battle['hidden_participants'] ?? [];
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

        <!-- Hidden Participants Table -->
        <?php renderHiddenParticipantsTable($hiddenParticipants, $battleId); ?>
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
        // Participant row click handlers for expandable details
        document.querySelectorAll('.participant-row').forEach(row => {
            row.addEventListener('click', function(e) {
                // Don't show details if clicking on interactive elements
                if (e.target.closest('input, button, .delete-participant-btn, .hide-participant-btn')) {
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

                toggleParticipantDetails(this, participantData);
            });
        });

        // Hidden participant row click handlers for expandable details
        document.querySelectorAll('.hidden-participant-row').forEach(row => {
            row.addEventListener('click', function(e) {
                // Don't show details if clicking on interactive elements
                if (e.target.closest('input, button, .delete-participant-btn, .unhide-participant-btn')) {
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

                toggleParticipantDetails(this, participantData);
            });
        });

        // Note: Initiative and participant management is now handled by modular JavaScript
        // See assets/js/modules/initiative.js and other modules
    });

    // Function to toggle participant details expandable row
    function toggleParticipantDetails(clickedRow, participant) {
        const participantId = participant.id;
        const existingDetailsRow = document.getElementById(`details-row-${participantId}`);

        // If details are already showing, hide them
        if (existingDetailsRow) {
            existingDetailsRow.remove();
            clickedRow.classList.remove('bg-accent/30');
            return;
        }

        // Hide any other open details first
        document.querySelectorAll('[id^="details-row-"]').forEach(row => row.remove());
        document.querySelectorAll('.participant-row, .hidden-participant-row').forEach(row => row.classList.remove('bg-accent/30'));

        // Highlight the clicked row
        clickedRow.classList.add('bg-accent/30');

        // Create details row
        const detailsRow = createParticipantDetailsRow(participant);

        // Insert the details row after the clicked row
        clickedRow.insertAdjacentElement('afterend', detailsRow);

        // Smooth reveal animation
        setTimeout(() => {
            detailsRow.style.maxHeight = detailsRow.scrollHeight + 'px';
            detailsRow.style.opacity = '1';
        }, 10);

        // Re-initialize Lucide icons for the new content
        if (window.lucide) {
            window.lucide.createIcons();
        }
    }

    function createParticipantDetailsRow(participant) {
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
        const abilities = ['str', 'dex', 'con', 'int', 'wis', 'cha'];
        const abilityScoresHTML = abilities.map(ability => {
            const value = participant[ability] || 10;
            const modifier = calculateModifier(value);
            return `
                <div class="bg-white rounded-lg p-3 text-center border">
                    <div class="text-xs font-medium text-gray-500 uppercase">${ability}</div>
                    <div class="text-lg font-bold">${value}</div>
                    <div class="text-xs text-gray-500">${formatModifier(modifier)}</div>
                </div>
            `;
        }).join('');

        // Build details sections
        let detailsSections = '';

        if (participant.skills && participant.skills.trim()) {
            detailsSections += `
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 class="font-semibold text-blue-800 mb-2 flex items-center">
                        <i data-lucide="zap" class="h-4 w-4 mr-2"></i>
                        Skills
                    </h4>
                    <div class="text-sm text-blue-700 whitespace-pre-wrap">${participant.skills}</div>
                </div>
            `;
        }

        if (participant.actions && participant.actions.trim()) {
            detailsSections += `
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <h4 class="font-semibold text-red-800 mb-2 flex items-center">
                        <i data-lucide="sword" class="h-4 w-4 mr-2"></i>
                        Actions
                    </h4>
                    <div class="text-sm text-red-700 whitespace-pre-wrap">${participant.actions}</div>
                </div>
            `;
        }

        if (participant.notes && participant.notes.trim()) {
            detailsSections += `
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <h4 class="font-semibold text-yellow-800 mb-2 flex items-center">
                        <i data-lucide="file-text" class="h-4 w-4 mr-2"></i>
                        Notes
                    </h4>
                    <div class="text-sm text-yellow-700 whitespace-pre-wrap">${participant.notes}</div>
                </div>
            `;
        }

        if (!detailsSections) {
            detailsSections = `
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 text-center">
                    <i data-lucide="info" class="h-8 w-8 mx-auto text-gray-400 mb-2"></i>
                    <p class="text-gray-500">No additional details available for this participant.</p>
                </div>
            `;
        }

        // Get table column count for colspan - check both main and hidden tables
        const mainTable = document.querySelector('#participants-table');
        const hiddenTable = document.querySelector('#hidden-participants-table');
        let headerCells = 10; // default fallback

        if (mainTable && mainTable.contains(document.getElementById(`participant-row-${participant.id}`))) {
            headerCells = mainTable.querySelectorAll('thead th').length;
        } else if (hiddenTable && hiddenTable.contains(document.getElementById(`hidden-participant-row-${participant.id}`))) {
            headerCells = hiddenTable.querySelectorAll('thead th').length;
        }

        // Create the details row element
        const detailsRow = document.createElement('tr');
        detailsRow.id = `details-row-${participant.id}`;
        detailsRow.className = 'details-row';
        detailsRow.style.maxHeight = '0';
        detailsRow.style.opacity = '0';
        detailsRow.style.transition = 'all 0.3s ease-in-out';
        detailsRow.style.overflow = 'hidden';

        detailsRow.innerHTML = `
            <td colspan="${headerCells}" class="p-0 border-0">
                <div class="bg-gradient-to-r from-accent/10 via-accent/5 to-accent/10 border-l-4 border-r-4 border-accent/30">
                    <div class="p-6">
                        <!-- Header -->
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center space-x-3">
                                <i data-lucide="${config.icon}" class="h-6 w-6 ${config.color}"></i>
                                <h3 class="text-xl font-bold text-foreground">${participant.name}</h3>
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-muted ${config.color}">${config.name}</span>
                            </div>
                            <button onclick="toggleParticipantDetails(document.getElementById('participant-row-${participant.id}'), {id: '${participant.id}'})" 
                                    class="text-muted-foreground hover:text-foreground transition-colors">
                                <i data-lucide="x" class="h-5 w-5"></i>
                            </button>
                        </div>

                        <!-- Stats Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            <!-- Basic Stats -->
                            <div class="space-y-3">
                                <h4 class="font-semibold text-foreground flex items-center">
                                    <i data-lucide="shield" class="h-4 w-4 mr-2"></i>
                                    Combat Stats
                                </h4>
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="bg-white rounded-lg p-3 border">
                                        <div class="text-xs font-medium text-gray-500">AC</div>
                                        <div class="text-lg font-bold">${participant.ac}</div>
                                    </div>
                                    <div class="bg-white rounded-lg p-3 border">
                                        <div class="text-xs font-medium text-gray-500">HP</div>
                                        <div class="text-lg font-bold ${hpColor}">${participant.hpCurrent}/${participant.hpMax}</div>
                                    </div>
                                    <div class="bg-white rounded-lg p-3 border col-span-2">
                                        <div class="text-xs font-medium text-gray-500">Passive Perception</div>
                                        <div class="text-lg font-bold">${participant.passive}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Ability Scores -->
                            <div class="space-y-3">
                                <h4 class="font-semibold text-foreground flex items-center">
                                    <i data-lucide="activity" class="h-4 w-4 mr-2"></i>
                                    Ability Scores
                                </h4>
                                <div class="grid grid-cols-3 gap-2">
                                    ${abilityScoresHTML}
                                </div>
                            </div>

                            <!-- Details -->
                            <div class="space-y-3">
                                <h4 class="font-semibold text-foreground flex items-center">
                                    <i data-lucide="scroll-text" class="h-4 w-4 mr-2"></i>
                                    Details
                                </h4>
                                <div class="space-y-3">
                                    ${detailsSections}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </td>
        `;

        return detailsRow;
    }

    // Hide participant functionality - AJAX
    document.addEventListener('click', function(e) {
        if (e.target.closest('.hide-participant-btn')) {
            const btn = e.target.closest('.hide-participant-btn');
            const participantId = btn.dataset.participantId;
            const participantName = btn.dataset.participantName;
            const battleId = btn.dataset.battleId;

            // Hide immediately with visual feedback
            const row = document.getElementById(`participant-row-${participantId}`);
            if (row) {
                row.style.opacity = '0.5';
                btn.disabled = true;
            }

            // Send AJAX request
            fetch('api/battle_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=hide_participant&participant_id=${participantId}&battle_id=${battleId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove from main table
                        if (row) {
                            row.remove();
                        }

                        // Update participant count
                        updateParticipantCount();

                        // Add to hidden table with real data
                        if (data.html_rows && data.html_rows.length > 0) {
                            addToHiddenTableWithHTML(data.html_rows[0]);
                        } else {
                            // Fallback to placeholder
                            addToHiddenTable(participantId, participantName, battleId);
                        }

                        // Show success message
                        showNotification(`${participantName} has been hidden`, 'success');
                    } else {
                        // Restore visual state on error
                        if (row) {
                            row.style.opacity = '1';
                            btn.disabled = false;
                        }
                        showNotification('Failed to hide participant', 'error');
                    }
                })
                .catch(error => {
                    // Restore visual state on error
                    if (row) {
                        row.style.opacity = '1';
                        btn.disabled = false;
                    }
                    showNotification('Error hiding participant', 'error');
                    console.error('Error:', error);
                });
        }

        if (e.target.closest('.unhide-participant-btn')) {
            const btn = e.target.closest('.unhide-participant-btn');
            const participantId = btn.dataset.participantId;
            const participantName = btn.dataset.participantName;
            const battleId = btn.dataset.battleId;

            // Disable button with visual feedback
            const row = document.getElementById(`hidden-participant-row-${participantId}`);
            if (row) {
                row.style.opacity = '0.5';
                btn.disabled = true;
            }

            // Send AJAX request
            fetch('api/battle_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=unhide_participant&participant_id=${participantId}&battle_id=${battleId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.html_rows && data.html_rows.length > 0) {
                        // Remove from hidden table
                        if (row) {
                            row.remove();
                        }

                        // Add to main table
                        const mainTableBody = document.querySelector('#participants-table tbody');
                        if (mainTableBody) {
                            mainTableBody.insertAdjacentHTML('beforeend', data.html_rows[0]);

                            // Re-initialize Lucide icons for the new row
                            lucide.createIcons();
                        }

                        // Update participant count
                        updateParticipantCount();

                        // Hide hidden table if empty
                        checkAndHideEmptyHiddenTable();

                        // Show success message
                        showNotification(`${participantName} has been unhidden`, 'success');
                    } else {
                        // Restore visual state on error
                        if (row) {
                            row.style.opacity = '1';
                            btn.disabled = false;
                        }
                        showNotification('Failed to unhide participant', 'error');
                    }
                })
                .catch(error => {
                    // Restore visual state on error
                    if (row) {
                        row.style.opacity = '1';
                        btn.disabled = false;
                    }
                    showNotification('Error unhiding participant', 'error');
                    console.error('Error:', error);
                });
        }
    });

    // Unhide all functionality - AJAX
    document.addEventListener('click', function(e) {
        if (e.target.closest('#unhideAllBtn')) {
            const hiddenCheckboxes = document.querySelectorAll('.hidden-participant-checkbox');
            const participantIds = Array.from(hiddenCheckboxes).map(cb => cb.value);
            const battleId = <?= $battleId ?>;

            if (participantIds.length === 0) {
                showNotification('No hidden participants to unhide', 'info');
                return;
            }

            // Disable button and show loading state
            const btn = e.target.closest('#unhideAllBtn');
            btn.disabled = true;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i data-lucide="loader-2" class="mr-1 h-3 w-3 animate-spin"></i>Unhiding...';

            // Re-render the loader icon
            lucide.createIcons();

            // Send AJAX request
            fetch('api/battle_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=unhide_participant&participant_ids=${participantIds.join(',')}&battle_id=${battleId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.html_rows) {
                        // Add all participants back to main table
                        const mainTableBody = document.querySelector('#participants-table tbody');
                        if (mainTableBody) {
                            data.html_rows.forEach(htmlRow => {
                                mainTableBody.insertAdjacentHTML('beforeend', htmlRow);
                            });

                            // Re-initialize Lucide icons for new rows
                            lucide.createIcons();
                        }

                        // Remove all hidden participants and hide table if empty
                        participantIds.forEach(id => {
                            const hiddenRow = document.getElementById(`hidden-participant-row-${id}`);
                            if (hiddenRow) {
                                hiddenRow.remove();
                            }
                        });

                        checkAndHideEmptyHiddenTable();

                        // Update participant count
                        updateParticipantCount();

                        // Show success message
                        showNotification(`${data.unhidden_count} participants have been unhidden`, 'success');
                    } else {
                        showNotification('Failed to unhide participants', 'error');
                    }
                })
                .catch(error => {
                    showNotification('Error unhiding participants', 'error');
                    console.error('Error:', error);
                })
                .finally(() => {
                    // Restore button state
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                    lucide.createIcons();
                });
        }
    });

    // Handle checkbox selections for hidden participants
    document.addEventListener('change', function(e) {
        if (e.target.id === 'selectAllHidden') {
            const checkboxes = document.querySelectorAll('.hidden-participant-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = e.target.checked;
            });
        } else if (e.target.classList.contains('hidden-participant-checkbox')) {
            const selectAllHidden = document.getElementById('selectAllHidden');
            const checkboxes = document.querySelectorAll('.hidden-participant-checkbox');
            const checkedBoxes = document.querySelectorAll('.hidden-participant-checkbox:checked');

            if (selectAllHidden) {
                selectAllHidden.checked = checkboxes.length === checkedBoxes.length;
                selectAllHidden.indeterminate = checkedBoxes.length > 0 && checkedBoxes.length < checkboxes.length;
            }
        }
    });

    // Helper functions
    function addToHiddenTable(participantId, participantName, battleId) {
        // Check if hidden table exists
        let hiddenTable = document.getElementById('hidden-participants-table');

        if (!hiddenTable) {
            // Create hidden table structure
            const hiddenTableContainer = createHiddenTableStructure();
            const mainContainer = document.querySelector('.container .grid');
            if (mainContainer) {
                mainContainer.appendChild(hiddenTableContainer);
            }
            hiddenTable = document.getElementById('hidden-participants-table');
        }

        // Create a placeholder row - will be replaced by proper data on page reload
        // For now, we'll just create a simple row with the participant name
        const tbody = hiddenTable.querySelector('tbody');
        if (tbody) {
            const placeholderRow = document.createElement('tr');
            placeholderRow.id = `hidden-participant-row-${participantId}`;
            placeholderRow.className = 'hidden-participant-row border-b border-border hover:bg-muted/50 transition-colors opacity-75 bg-muted/20';
            placeholderRow.innerHTML = `
                <td class="p-4 align-middle"><input type="checkbox" class="hidden-participant-checkbox h-4 w-4" value="${participantId}"></td>
                <td class="p-4 align-middle">
                    <div class="flex items-center space-x-2">
                        <i data-lucide="eye-off" class="h-3 w-3 text-muted-foreground"></i>
                        <span class="text-sm text-muted-foreground">${participantName}</span>
                    </div>
                </td>
                <td class="p-4 align-middle text-center" colspan="9">
                    <div class="text-sm text-muted-foreground">Loading...</div>
                </td>
                <td class="p-4 align-middle text-center">
                    <button type="button" class="unhide-participant-btn inline-flex items-center justify-center rounded-md h-8 w-8 border border-input bg-background hover:bg-accent hover:text-accent-foreground transition-colors"
                        data-participant-id="${participantId}" data-participant-name="${participantName}" data-battle-id="${battleId}" title="Unhide participant">
                        <i data-lucide="eye" class="h-3 w-3"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(placeholderRow);

            // Re-initialize Lucide icons
            lucide.createIcons();
        }

        updateHiddenTableHeader();
    }

    function addToHiddenTableWithHTML(htmlRow) {
        // Check if hidden table exists
        let hiddenTable = document.getElementById('hidden-participants-table');

        if (!hiddenTable) {
            // Create hidden table structure
            const hiddenTableContainer = createHiddenTableStructure();
            const mainContainer = document.querySelector('.container .grid');
            if (mainContainer) {
                mainContainer.appendChild(hiddenTableContainer);
            }
            hiddenTable = document.getElementById('hidden-participants-table');
        }

        // Add the real HTML row
        const tbody = hiddenTable.querySelector('tbody');
        if (tbody) {
            tbody.insertAdjacentHTML('beforeend', htmlRow);

            // Re-initialize Lucide icons
            lucide.createIcons();
        }

        updateHiddenTableHeader();
    }

    function createHiddenTableStructure() {
        const container = document.createElement('div');
        container.className = 'bg-card rounded-lg border border-border p-6 mt-6';
        container.innerHTML = `
            <div class="rounded-lg border border-border bg-card text-card-foreground shadow-sm">
                <div class="flex flex-col space-y-1.5 p-6 pb-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold leading-none tracking-tight flex items-center">
                            <i data-lucide="eye-off" class="mr-2 h-5 w-5 text-muted-foreground"></i>
                            Hidden Participants
                            <span class="ml-2 text-sm text-muted-foreground" id="hidden-count">(1)</span>
                        </h3>
                        <div class="flex items-center space-x-2">
                            <button id="unhideAllBtn" type="button"
                                class="inline-flex items-center justify-center rounded-md text-sm font-medium border border-input bg-background hover:bg-accent hover:text-accent-foreground h-8 px-3">
                                <i data-lucide="eye" class="mr-1 h-3 w-3"></i>
                                Unhide All
                            </button>
                        </div>
                    </div>
                </div>
                <div class="p-6 pt-0">
                    <div class="rounded-md border border-border overflow-hidden">
                        <table class="w-full caption-bottom text-sm" id="hidden-participants-table">
                            <thead class="[&_tr]:border-b">
                                <tr class="border-b border-border transition-colors hover:bg-muted/50">
                                    <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">
                                        <input type="checkbox" id="selectAllHidden" class="h-4 w-4 shrink-0 rounded-sm border border-primary">
                                    </th>
                                    <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Name</th>
                                    <th class="h-12 px-4 text-center align-middle font-medium text-muted-foreground">AC</th>
                                    <th class="h-12 px-4 text-center align-middle font-medium text-muted-foreground">HP</th>
                                    <th class="h-12 px-4 text-center align-middle font-medium text-muted-foreground">STR</th>
                                    <th class="h-12 px-4 text-center align-middle font-medium text-muted-foreground">DEX</th>
                                    <th class="h-12 px-4 text-center align-middle font-medium text-muted-foreground">CON</th>
                                    <th class="h-12 px-4 text-center align-middle font-medium text-muted-foreground">INT</th>
                                    <th class="h-12 px-4 text-center align-middle font-medium text-muted-foreground">WIS</th>
                                    <th class="h-12 px-4 text-center align-middle font-medium text-muted-foreground">CHA</th>
                                    <th class="h-12 px-4 text-center align-middle font-medium text-muted-foreground">Passive Perception</th>
                                    <th class="h-12 px-4 text-center align-middle font-medium text-muted-foreground">Initiative</th>
                                    <th class="h-12 px-4 text-center align-middle font-medium text-muted-foreground">Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;

        // Re-initialize Lucide icons
        setTimeout(() => lucide.createIcons(), 100);

        return container;
    }

    function updateParticipantCount() {
        const participantTableBody = document.querySelector('#participants-table tbody');
        const participantCount = participantTableBody ? participantTableBody.querySelectorAll('tr').length : 0;
        const countElement = document.getElementById('participants-count');
        if (countElement) {
            countElement.textContent = `${participantCount} participants in battle`;
        }
    }

    function checkAndHideEmptyHiddenTable() {
        const hiddenTable = document.getElementById('hidden-participants-table');
        if (hiddenTable) {
            const tbody = hiddenTable.querySelector('tbody');
            const rows = tbody ? tbody.querySelectorAll('tr') : [];

            if (rows.length === 0) {
                // Remove the entire hidden table container (the bg-card wrapper)
                const container = hiddenTable.closest('.bg-card');
                if (container) {
                    container.remove();
                }
            } else {
                updateHiddenTableHeader();
            }
        }
    }

    function updateHiddenTableHeader() {
        const hiddenCountElement = document.getElementById('hidden-count');
        const hiddenTable = document.getElementById('hidden-participants-table');

        if (hiddenCountElement && hiddenTable) {
            const tbody = hiddenTable.querySelector('tbody');
            const count = tbody ? tbody.querySelectorAll('tr').length : 0;
            hiddenCountElement.textContent = `(${count})`;
        }
    }

    function showNotification(message, type = 'info') {
        // Use SweetAlert for notifications
        const iconType = type === 'success' ? 'success' : type === 'error' ? 'error' : 'info';

        Swal.fire({
            title: message,
            icon: iconType,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            customClass: {
                popup: 'colored-toast'
            }
        });
    }
</script>

<!-- Load the modular JavaScript -->
<script type="module" src="assets/js/battle-detail.js"></script>