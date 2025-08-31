<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/utils.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../classes/ParticipantManager.php';
require_once __DIR__ . '/../classes/BattleManager.php';
require_once __DIR__ . '/../includes/participant-row-helper.php';

header('Content-Type: application/json');

// Handle CORS if needed
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$action = $_POST['action'] ?? '';
$response = ['success' => false];

try {
    switch ($action) {
        case 'update_hp':
            $participantIds = isset($_POST['participant_ids']) ?
                array_map('intval', explode(',', $_POST['participant_ids'])) :
                [intval($_POST['participant_id'])];
            $battleId = intval($_POST['battle_id']);
            $hpAction = $_POST['hp_action']; // 'damage' or 'heal'
            $amount = intval($_POST['amount']);

            $participantManager = new ParticipantManager();
            $updatedParticipants = [];
            $successCount = 0;

            foreach ($participantIds as $participantId) {
                $result = $participantManager->updateParticipantHP($participantId, $battleId, $hpAction, $amount);
                if ($result) {
                    $successCount++;
                    // Get updated participant data
                    $battleManager = new BattleManager();
                    $battle = $battleManager->getBattleById($battleId);
                    foreach ($battle['participants'] as $p) {
                        if ($p['id'] == $participantId) {
                            $updatedParticipants[] = $p;
                            break;
                        }
                    }
                }
            }

            $response = [
                'success' => $successCount > 0,
                'updated_count' => $successCount,
                'total_count' => count($participantIds),
                'participants' => $updatedParticipants
            ];
            break;

        case 'remove_participant':
            $participantIds = isset($_POST['participant_ids']) ?
                array_map('intval', explode(',', $_POST['participant_ids'])) :
                [intval($_POST['participant_id'])];
            $battleId = intval($_POST['battle_id']);

            $participantManager = new ParticipantManager();
            $successCount = 0;

            foreach ($participantIds as $participantId) {
                $result = $participantManager->removeParticipant($participantId, $battleId);
                if ($result) {
                    $successCount++;
                }
            }

            $response = [
                'success' => $successCount > 0,
                'removed_count' => $successCount,
                'total_count' => count($participantIds)
            ];
            break;

        case 'update_initiative':
            $participantId = intval($_POST['participant_id']);
            $battleId = intval($_POST['battle_id']);
            $initiative = intval($_POST['initiative']);

            $participantManager = new ParticipantManager();
            $result = $participantManager->updateParticipantInitiative($participantId, $initiative);

            $response = ['success' => $result];
            break;

        case 'update_name':
            $participantId = intval($_POST['participant_id']);
            $battleId = intval($_POST['battle_id']);
            $name = trim($_POST['name']);

            $participantManager = new ParticipantManager();
            $result = $participantManager->updateParticipantName($participantId, $name);

            $response = ['success' => $result];
            break;

        case 'hide_participant':
            $participantIds = isset($_POST['participant_ids']) ?
                array_map('intval', explode(',', $_POST['participant_ids'])) :
                [intval($_POST['participant_id'])];
            $battleId = intval($_POST['battle_id']);

            $participantManager = new ParticipantManager();
            $successCount = 0;

            // First, get the participant data before hiding
            $battleManager = new BattleManager();
            $battle = $battleManager->getBattleById($battleId);
            $hiddenParticipants = [];

            foreach ($battle['participants'] as $p) {
                if (in_array($p['id'], $participantIds)) {
                    $hiddenParticipants[] = $p;
                }
            }

            // Now hide the participants
            foreach ($participantIds as $participantId) {
                $result = $participantManager->hideParticipant($participantId, $battleId);
                if ($result) {
                    $successCount++;
                }
            }

            $response = [
                'success' => $successCount > 0,
                'hidden_count' => $successCount,
                'total_count' => count($participantIds),
                'participant_ids' => $participantIds,
                'participants' => $hiddenParticipants,
                'html_rows' => array_map(function ($p) use ($battleId) {
                    return generateParticipantRowHTML($p, $battleId, true);
                }, $hiddenParticipants)
            ];
            break;

        case 'unhide_participant':
            $participantIds = isset($_POST['participant_ids']) ?
                array_map('intval', explode(',', $_POST['participant_ids'])) :
                [intval($_POST['participant_id'])];
            $battleId = intval($_POST['battle_id']);

            $participantManager = new ParticipantManager();
            $successCount = 0;

            foreach ($participantIds as $participantId) {
                $result = $participantManager->unhideParticipant($participantId, $battleId);
                if ($result) {
                    $successCount++;
                }
            }

            // Get updated participant data for the unhidden participants
            $battleManager = new BattleManager();
            $battle = $battleManager->getBattleById($battleId);
            $unhiddenParticipants = [];

            foreach ($battle['participants'] as $p) {
                if (in_array($p['id'], $participantIds)) {
                    $unhiddenParticipants[] = $p;
                }
            }

            $response = [
                'success' => $successCount > 0,
                'unhidden_count' => $successCount,
                'total_count' => count($participantIds),
                'participant_ids' => $participantIds,
                'participants' => $unhiddenParticipants,
                'html_rows' => array_map(function ($p) use ($battleId) {
                    return generateParticipantRowHTML($p, $battleId, false);
                }, $unhiddenParticipants)
            ];
            break;

        default:
            $response = ['error' => 'Unknown action'];
            break;
    }
} catch (Exception $e) {
    $response = ['error' => $e->getMessage()];
}

echo json_encode($response);
