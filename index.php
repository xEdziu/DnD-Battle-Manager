<?php
// D&D Battle Manager - Refactored main entry point

// Include all required files
require_once 'config/config.php';
require_once 'includes/database.php';
require_once 'includes/utils.php';
require_once 'classes/PresetManager.php';
require_once 'classes/BattleManager.php';
require_once 'classes/ParticipantManager.php';
require_once 'includes/BadgeManager.php';
require_once 'handlers/preset_handler.php';
require_once 'handlers/battle_handler.php';
require_once 'handlers/import_export_handler.php';

// Initialize managers and handlers
$presetManager = new PresetManager();
$battleManager = new BattleManager();
$badgeManager = new BadgeManager();
$importExportHandler = new ImportExportHandler();
$presetHandler = new PresetHandler();
$battleHandler = new BattleHandler();

// Initialize default presets if needed
$presetManager->initializeDefaultPresets();

// Handle actions
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Route to appropriate handler
if (in_array($action, ['add_preset', 'edit_preset', 'delete_preset', 'clone_preset'])) {
    $presetHandler->handle();
} elseif (in_array($action, ['create_battle', 'delete_battle', 'update_battle', 'update_battle_info', 'update_battle_badge', 'add_participant', 'remove_participant', 'damage', 'heal'])) {
    $battleHandler->handle();
} elseif (in_array($action, ['create_badge', 'edit_badge', 'delete_badge'])) {
    // Handle badge management
    if ($action === 'create_badge') {
        $name = trim($_POST['name'] ?? '');
        $color = $_POST['color'] ?? 'blue';
        $icon = $_POST['icon'] ?? 'zap';

        if ($name) {
            $badgeId = $badgeManager->createBadge($name, $color, $icon);
            if ($badgeId) {
                setSuccess('Badge created successfully');
            } else {
                setError('Failed to create badge');
            }
        } else {
            setError('Badge name is required');
        }
    } elseif ($action === 'edit_badge') {
        $badgeId = intval($_POST['badge_id']);
        $name = trim($_POST['name'] ?? '');
        $color = $_POST['color'] ?? 'blue';
        $icon = $_POST['icon'] ?? 'zap';

        if ($name && $badgeManager->updateBadge($badgeId, $name, $color, $icon)) {
            setSuccess('Badge updated successfully');
        } else {
            setError('Failed to update badge');
        }
    } elseif ($action === 'delete_badge') {
        $badgeId = intval($_POST['badge_id']);

        if ($badgeManager->deleteBadge($badgeId)) {
            setSuccess('Badge deleted successfully');
        } else {
            setError('Cannot delete default badges');
        }
    }

    // Determine redirect URL - stay on badges page if we're working with badges
    $redirectUrl = 'index.php';
    if (isset($_GET['page']) && $_GET['page'] === 'badges') {
        $redirectUrl = 'index.php?page=badges';
    }
    redirect($redirectUrl);
} elseif (in_array($action, ['export_json', 'use_json', 'use_sqlite']) || isset($_FILES['import_file'])) {
    $importExportHandler->handle();
}

// Include header and navigation
include 'views/layout/header.php';

// Determine which view to show
if (isset($_GET['page']) && $_GET['page'] === 'presets') {
    include 'views/presets/list.php';
} elseif (isset($_GET['page']) && $_GET['page'] === 'badges') {
    include 'views/badges/list.php';
} elseif (isset($_GET['battle'])) {
    include 'views/battles/detail.php';
} else {
    include 'views/battles/list.php';
}

// Include footer
include 'views/layout/footer.php';
