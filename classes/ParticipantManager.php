<?php

class ParticipantManager
{
    public function addParticipant($battleId, $presetId, $useRoll = false)
    {
        $presetManager = new PresetManager();
        $preset = $presetManager->getPresetById($presetId);

        if (!$preset) {
            return false;
        }

        // Oblicz HP
        $hpFormula = $preset['hp'];
        if (is_numeric($hpFormula)) {
            $hpVal = intval($hpFormula);
        } else {
            $dice = parseDiceFormula($hpFormula);
            $hpVal = $useRoll ? $dice['roll'] : $dice['avg'];
        }

        // Unikalna nazwa
        $newName = $this->generateUniqueName($battleId, $preset['name']);

        return $this->addParticipantToSqlite($battleId, $preset, $newName, $hpVal);
    }

    public function removeParticipant($participantId, $battleId)
    {
        $db = Database::getInstance()->getConnection();
        $result = $db->exec("DELETE FROM participants WHERE id = " . intval($participantId));
        return $result !== false;
    }

    public function updateParticipantHP($participantId, $battleId, $action, $amount)
    {
        return $this->updateParticipantHPInSqlite($participantId, $action, abs(intval($amount)));
    }

    private function generateUniqueName($battleId, $baseName)
    {
        $existingNames = $this->getExistingNames($battleId);

        $instances = [];
        foreach ($existingNames as $name) {
            if ($name === $baseName) {
                $instances[] = 0;
            } elseif (strpos($name, $baseName . ' ') === 0) {
                $rest = trim(substr($name, strlen($baseName . ' ')));
                if (preg_match('/^(\d+)$/', $rest, $m)) {
                    $instances[] = intval($m[1]);
                }
            }
        }

        if (empty($instances)) {
            return $baseName;
        }

        if (count($instances) === 1 && $instances[0] === 0) {
            $this->renameExistingParticipant($battleId, $baseName, $baseName . ' 1');
            return $baseName . ' 2';
        }

        $maxNum = max($instances);
        return $baseName . ' ' . ($maxNum + 1);
    }

    private function renameExistingParticipant($battleId, $oldName, $newName)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE participants 
                              SET name = :new_name 
                              WHERE battle_id = :battle_id AND name = :old_name");
        $stmt->bindValue(':new_name', $newName, SQLITE3_TEXT);
        $stmt->bindValue(':battle_id', $battleId, SQLITE3_INTEGER);
        $stmt->bindValue(':old_name', $oldName, SQLITE3_TEXT);
        $stmt->execute();
    }

    private function getExistingNames($battleId)
    {
        $db = Database::getInstance()->getConnection();
        $res = $db->query("SELECT name FROM participants WHERE battle_id = " . intval($battleId));
        $existing = [];
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
            $existing[] = $row['name'];
        }
        return $existing;
    }

    private function addParticipantToSqlite($battleId, $preset, $name, $hpVal)
    {
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare('INSERT INTO participants 
            (battle_id, name, str, dex, con, int, wis, cha, ac, hp_current, hp_max, passive, skills, actions, notes, initiative, character_type)
            VALUES (:battle_id, :name, :str, :dex, :con, :int, :wis, :cha, :ac, :hp_current, :hp_max, :passive, :skills, :actions, :notes, 0, :character_type)');

        $stmt->bindValue(':battle_id', $battleId, SQLITE3_INTEGER);
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        $stmt->bindValue(':str', $preset['str'], SQLITE3_INTEGER);
        $stmt->bindValue(':dex', $preset['dex'], SQLITE3_INTEGER);
        $stmt->bindValue(':con', $preset['con'], SQLITE3_INTEGER);
        $stmt->bindValue(':int', $preset['int'], SQLITE3_INTEGER);
        $stmt->bindValue(':wis', $preset['wis'], SQLITE3_INTEGER);
        $stmt->bindValue(':cha', $preset['cha'], SQLITE3_INTEGER);
        $stmt->bindValue(':ac', $preset['ac'], SQLITE3_INTEGER);
        $stmt->bindValue(':hp_current', $hpVal, SQLITE3_INTEGER);
        $stmt->bindValue(':hp_max', $hpVal, SQLITE3_INTEGER);
        $stmt->bindValue(':passive', $preset['passive'], SQLITE3_INTEGER);
        $stmt->bindValue(':skills', $preset['skills'], SQLITE3_TEXT);
        $stmt->bindValue(':actions', $preset['actions'], SQLITE3_TEXT);
        $stmt->bindValue(':notes', $preset['notes'], SQLITE3_TEXT);
        $stmt->bindValue(':character_type', $preset['character_type'] ?? 'enemy', SQLITE3_TEXT);

        $stmt->execute();
        return $db->lastInsertRowID();
    }

    private function updateParticipantHPInSqlite($participantId, $action, $amount)
    {
        $db = Database::getInstance()->getConnection();

        $res = $db->querySingle("SELECT hp_current, hp_max 
                                 FROM participants 
                                 WHERE id = " . intval($participantId), true);
        if ($res) {
            $currentHP = $res['hp_current'];
            $maxHP = $res['hp_max'];

            $newHP = ($action === 'damage')
                ? max(0, $currentHP - $amount)
                : min($maxHP, $currentHP + $amount);

            $db->exec("UPDATE participants 
                       SET hp_current = $newHP 
                       WHERE id = " . intval($participantId));
            return true;
        }
        return false;
    }

    public function updateParticipantInitiative($participantId, $initiative)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE participants 
                              SET initiative = :initiative 
                              WHERE id = :id");
        $stmt->bindValue(':initiative', intval($initiative), SQLITE3_INTEGER);
        $stmt->bindValue(':id', intval($participantId), SQLITE3_INTEGER);
        return $stmt->execute();
    }

    public function updateParticipantName($participantId, $name)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE participants 
                              SET name = :name 
                              WHERE id = :id");
        $stmt->bindValue(':name', trim($name), SQLITE3_TEXT);
        $stmt->bindValue(':id', intval($participantId), SQLITE3_INTEGER);
        return $stmt->execute();
    }
}
