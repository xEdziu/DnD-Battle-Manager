<?php

class BattleHandler
{
    private $battleManager;
    private $participantManager;

    public function __construct()
    {
        $this->battleManager = new BattleManager();
        $this->participantManager = new ParticipantManager();
    }

    public function handle()
    {
        $action = $_POST['action'] ?? $_GET['action'] ?? '';

        switch ($action) {
            case 'create_battle':
                $this->createBattle();
                break;
            case 'delete_battle':
                $this->deleteBattle();
                break;
            case 'update_battle':
                // Legacy method - now using AJAX for initiative/name updates
                $this->updateBattle();
                break;
            case 'update_battle_info':
                $this->updateBattleInfo();
                break;
            case 'update_battle_badge':
                $this->updateBattleBadge();
                break;
            case 'add_participant':
                $this->addParticipant();
                break;
            case 'remove_participant':
                $this->removeParticipant();
                break;
            case 'damage':
            case 'heal':
                $this->updateHP();
                break;
        }
    }

    private function createBattle()
    {
        $battleName = trim($_POST['name'] ?? $_GET['name'] ?? '');
        $battleDescription = trim($_POST['description'] ?? $_GET['description'] ?? '');
        $battleId = $this->battleManager->createBattle($battleName, $battleDescription);
        redirect('index.php?battle=' . $battleId);
    }

    private function deleteBattle()
    {
        error_log("deleteBattle method called with POST data: " . print_r($_POST, true));
        $battleId = intval($_POST['battle_id'] ?? $_GET['battle_id']);

        if ($this->battleManager->deleteBattle($battleId)) {
            setSuccess('Battle deleted successfully');
        } else {
            setError('Failed to delete battle');
        }

        redirect('index.php');
    }

    private function updateBattle()
    {
        $battleId = intval($_POST['battle_id']);
        $updates = [
            'names' => $_POST['name'] ?? [],
            'initiatives' => $_POST['init'] ?? []
        ];

        $this->battleManager->updateBattleParticipants($battleId, $updates);

        // Don't redirect for AJAX requests (now using individual API calls)
        // This method is kept for backward compatibility but should not be used for new features
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }

        redirect('index.php?battle=' . $battleId . '&saved=1');
    }

    private function updateBattleBadge()
    {
        $battleId = intval($_POST['battle_id']);
        $badgeId = intval($_POST['badge_id']);
        $source = $_POST['source'] ?? '';

        if ($this->battleManager->updateBattleBadge($battleId, $badgeId)) {
            setSuccess('Battle badge updated successfully');
        } else {
            setError('Failed to update battle badge');
        }

        // Redirect based on source context
        if ($source === 'battle_detail') {
            // If called from battle detail page, stay in battle detail
            redirect('index.php?battle=' . $battleId);
        } else {
            // If called from main battles list, stay on main page
            redirect('index.php');
        }
    }

    private function updateBattleInfo()
    {
        $battleId = intval($_POST['battle_id']);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        // Check if this is an AJAX request
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

        if (empty($name)) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Battle name cannot be empty']);
                exit;
            } else {
                setError('Battle name cannot be empty');
                redirect('index.php');
                return;
            }
        }

        $success = $this->battleManager->updateBattleInfo($battleId, $name, $description);

        if ($isAjax) {
            header('Content-Type: application/json');
            if ($success) {
                echo json_encode(['success' => true, 'message' => 'Battle information updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update battle information']);
            }
            exit;
        } else {
            // Fallback for non-AJAX requests
            if ($success) {
                setSuccess('Battle information updated successfully');
            } else {
                setError('Failed to update battle information');
            }
            redirect('index.php');
        }
    }

    private function addParticipant()
    {
        $battleId = intval($_POST['battle_id']);
        $presetId = intval($_POST['preset_id']);
        $quantity = max(1, min(20, intval($_POST['quantity'] ?? 1))); // Limit 1-20
        $useRoll = isset($_POST['use_roll']);

        for ($i = 0; $i < $quantity; $i++) {
            $this->participantManager->addParticipant($battleId, $presetId, $useRoll);
        }

        redirect('index.php?battle=' . $battleId);
    }

    private function removeParticipant()
    {
        // Support both single and bulk removal
        $participantIds = isset($_POST['participant_ids']) ?
            array_map('intval', explode(',', $_POST['participant_ids'])) :
            [intval($_POST['participant_id'])];
        $battleId = intval($_POST['battle_id']);

        $successCount = 0;
        foreach ($participantIds as $participantId) {
            $result = $this->participantManager->removeParticipant($participantId, $battleId);
            if ($result) {
                $successCount++;
            }
        }

        redirect('index.php?battle=' . $battleId);
    }

    private function updateHP()
    {
        // Support both single and bulk HP updates
        $participantIds = isset($_POST['participant_ids']) ?
            array_map('intval', explode(',', $_POST['participant_ids'])) :
            [intval($_POST['participant_id'])];
        $battleId = intval($_POST['battle_id']);
        $action = $_POST['action'];
        $amount = intval($_POST['amount']);

        $successCount = 0;
        foreach ($participantIds as $participantId) {
            $result = $this->participantManager->updateParticipantHP($participantId, $battleId, $action, $amount);
            if ($result) {
                $successCount++;
            }
        }

        redirect('index.php?battle=' . $battleId);
    }
}