<?php

class BattleManager
{

    public function getAllBattles()
    {
        return $this->getBattlesFromSqlite();
    }

    public function getBattleById($id)
    {
        return $this->getBattleFromSqlite($id);
    }

    public function createBattle($name = '', $description = '', $badgeId = 1)
    {
        if (empty($name)) {
            $count = count($this->getAllBattles());
            $name = 'Battle ' . ($count + 1);
        }

        return $this->createBattleInSqlite($name, $description, $badgeId);
    }

    public function updateBattleBadge($battleId, $badgeId)
    {
        return $this->updateBattleBadgeInSqlite($battleId, $badgeId);
    }

    public function updateBattleInfo($battleId, $name, $description)
    {
        return $this->updateBattleInfoInSqlite($battleId, $name, $description);
    }

    public function updateBattleParticipants($battleId, $updates)
    {
        $this->updateBattleParticipantsInSqlite($battleId, $updates);
    }

    public function deleteBattle($battleId)
    {
        return $this->deleteBattleFromSqlite($battleId);
    }

    private function getBattlesFromSqlite()
    {
        $db = Database::getInstance()->getConnection();
        $battles = [];
        $res = $db->query('SELECT b.*, COUNT(p.id) as participant_count FROM battles b LEFT JOIN participants p ON b.id = p.battle_id GROUP BY b.id');
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
            // Convert participant_count to participants array for compatibility
            $row['participants'] = array_fill(0, $row['participant_count'], null);

            // Get badge information
            if ($row['badge_id']) {
                $badgeRes = $db->query('SELECT * FROM badges WHERE id = ' . intval($row['badge_id']));
                $row['badge'] = $badgeRes->fetchArray(SQLITE3_ASSOC);
            } else {
                $row['badge'] = null;
            }

            $battles[] = $row;
        }
        return $battles;
    }

    private function getBattleFromSqlite($id)
    {
        $db = Database::getInstance()->getConnection();
        $res = $db->query('SELECT * FROM battles WHERE id = ' . intval($id));
        $battle = $res->fetchArray(SQLITE3_ASSOC);

        if ($battle) {
            // Get visible participants (not hidden)
            $partsRes = $db->query("SELECT * FROM participants WHERE battle_id = " . intval($id) . " AND (is_hidden IS NULL OR is_hidden = 0)");
            $participants = [];
            while ($pr = $partsRes->fetchArray(SQLITE3_ASSOC)) {
                $participants[] = $pr;
            }
            $battle['participants'] = $participants;

            // Get hidden participants
            $hiddenPartsRes = $db->query("SELECT * FROM participants WHERE battle_id = " . intval($id) . " AND is_hidden = 1");
            $hiddenParticipants = [];
            while ($pr = $hiddenPartsRes->fetchArray(SQLITE3_ASSOC)) {
                $hiddenParticipants[] = $pr;
            }
            $battle['hidden_participants'] = $hiddenParticipants;

            // Get badge information
            if ($battle['badge_id']) {
                $badgeRes = $db->query('SELECT * FROM badges WHERE id = ' . intval($battle['badge_id']));
                $battle['badge'] = $badgeRes->fetchArray(SQLITE3_ASSOC);
            } else {
                $battle['badge'] = null;
            }
        }

        return $battle;
    }

    private function createBattleInSqlite($name, $description = '', $badgeId = 1)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('INSERT INTO battles (name, description, badge_id) VALUES (:name, :description, :badge_id)');
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        $stmt->bindValue(':description', $description, SQLITE3_TEXT);
        $stmt->bindValue(':badge_id', $badgeId, SQLITE3_INTEGER);
        $stmt->execute();
        return $db->lastInsertRowID();
    }

    private function updateBattleBadgeInSqlite($battleId, $badgeId)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('UPDATE battles SET badge_id = :badge_id WHERE id = :id');
        $stmt->bindValue(':badge_id', $badgeId, SQLITE3_INTEGER);
        $stmt->bindValue(':id', $battleId, SQLITE3_INTEGER);
        return $stmt->execute();
    }

    private function updateBattleInfoInSqlite($battleId, $name, $description)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('UPDATE battles SET name = :name, description = :description WHERE id = :id');
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        $stmt->bindValue(':description', $description, SQLITE3_TEXT);
        $stmt->bindValue(':id', $battleId, SQLITE3_INTEGER);
        return $stmt->execute();
    }

    private function updateBattleParticipantsInSqlite($battleId, $updates)
    {
        $db = Database::getInstance()->getConnection();

        foreach ($updates['names'] as $pid => $nameVal) {
            $pid = intval($pid);
            $nameVal = SQLite3::escapeString($nameVal);
            $initVal = isset($updates['initiatives'][$pid]) ? intval($updates['initiatives'][$pid]) : 0;
            $db->exec("UPDATE participants SET name = '$nameVal', initiative = $initVal WHERE id = $pid");
        }
    }

    private function deleteBattleFromSqlite($battleId)
    {
        $db = Database::getInstance()->getConnection();
        $db->exec('BEGIN');

        // Delete participants first (foreign key constraint)
        $db->exec("DELETE FROM participants WHERE battle_id = " . intval($battleId));

        // Delete the battle
        $result = $db->exec("DELETE FROM battles WHERE id = " . intval($battleId));

        $db->exec('COMMIT');
        return $result !== false;
    }
}
