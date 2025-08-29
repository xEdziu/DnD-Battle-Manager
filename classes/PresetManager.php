<?php

class PresetManager
{

    public function getAllPresets($characterType = null)
    {
        return $this->getPresetsFromSqlite($characterType);
    }

    public function getCharacterTypes()
    {
        return [
            'pc' => ['name' => 'Player Character', 'icon' => 'user-check', 'color' => 'emerald'],
            'npc' => ['name' => 'NPC', 'icon' => 'user', 'color' => 'royal'],
            'enemy' => ['name' => 'Enemy/Mob', 'icon' => 'skull', 'color' => 'crimson']
        ];
    }

    public function getPresetById($id)
    {
        $db = Database::getInstance()->getConnection();
        $res = $db->query('SELECT * FROM presets WHERE id = ' . intval($id));
        return $res->fetchArray(SQLITE3_ASSOC);
    }

    public function createPreset($presetData)
    {
        return $this->createPresetInSqlite($presetData);
    }

    public function updatePreset($id, $presetData)
    {
        return $this->updatePresetInSqlite($id, $presetData);
    }

    public function deletePreset($id)
    {
        $db = Database::getInstance()->getConnection();
        $db->exec("DELETE FROM presets WHERE id = " . intval($id));
    }

    public function initializeDefaultPresets()
    {
        $db = Database::getInstance()->getConnection();
        $res = $db->querySingle('SELECT COUNT(*) FROM presets');
        if ($res == 0) {
            $this->createDefaultPresetsInSqlite();
        }
    }

    private function getPresetsFromSqlite($characterType = null)
    {
        $db = Database::getInstance()->getConnection();
        $presets = [];

        if ($characterType) {
            $stmt = $db->prepare('SELECT * FROM presets WHERE character_type = :character_type ORDER BY name');
            $stmt->bindValue(':character_type', $characterType, SQLITE3_TEXT);
            $res = $stmt->execute();
        } else {
            $res = $db->query('SELECT * FROM presets ORDER BY character_type, name');
        }

        while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
            // Ensure character_type has a default value for older records
            if (!isset($row['character_type']) || empty($row['character_type'])) {
                $row['character_type'] = 'npc';
            }
            $presets[] = $row;
        }
        return $presets;
    }

    private function createPresetInSqlite($presetData)
    {
        $db = Database::getInstance()->getConnection();
        $stats = ['str', 'dex', 'con', 'int', 'wis', 'cha'];

        $stmt = $db->prepare('INSERT INTO presets (name, str, dex, con, int, wis, cha, ac, hp, passive, skills, actions, notes, character_type)
                               VALUES (:name, :str, :dex, :con, :int, :wis, :cha, :ac, :hp, :passive, :skills, :actions, :notes, :character_type)');

        $stmt->bindValue(':name', $presetData['name'], SQLITE3_TEXT);
        foreach ($stats as $st) {
            $stmt->bindValue(':' . $st, $presetData[$st], SQLITE3_INTEGER);
        }
        $stmt->bindValue(':ac', $presetData['ac'], SQLITE3_INTEGER);
        $stmt->bindValue(':hp', $presetData['hp'], SQLITE3_TEXT);
        $stmt->bindValue(':passive', $presetData['passive'], SQLITE3_INTEGER);
        $stmt->bindValue(':skills', $presetData['skills'], SQLITE3_TEXT);
        $stmt->bindValue(':actions', $presetData['actions'], SQLITE3_TEXT);
        $stmt->bindValue(':notes', $presetData['notes'], SQLITE3_TEXT);
        $stmt->bindValue(':character_type', $presetData['character_type'] ?? 'npc', SQLITE3_TEXT);

        $stmt->execute();
        return $db->lastInsertRowID();
    }

    private function updatePresetInSqlite($id, $presetData)
    {
        $db = Database::getInstance()->getConnection();
        $stats = ['str', 'dex', 'con', 'int', 'wis', 'cha'];

        $stmt = $db->prepare('UPDATE presets SET name=:name, str=:str, dex=:dex, con=:con, int=:int, wis=:wis, cha=:cha, ac=:ac, hp=:hp, passive=:passive, skills=:skills, actions=:actions, notes=:notes, character_type=:character_type
                               WHERE id=:id');

        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->bindValue(':name', $presetData['name'], SQLITE3_TEXT);
        foreach ($stats as $st) {
            $stmt->bindValue(':' . $st, $presetData[$st], SQLITE3_INTEGER);
        }
        $stmt->bindValue(':ac', $presetData['ac'], SQLITE3_INTEGER);
        $stmt->bindValue(':hp', $presetData['hp'], SQLITE3_TEXT);
        $stmt->bindValue(':passive', $presetData['passive'], SQLITE3_INTEGER);
        $stmt->bindValue(':skills', $presetData['skills'], SQLITE3_TEXT);
        $stmt->bindValue(':actions', $presetData['actions'], SQLITE3_TEXT);
        $stmt->bindValue(':notes', $presetData['notes'], SQLITE3_TEXT);
        $stmt->bindValue(':character_type', $presetData['character_type'] ?? 'npc', SQLITE3_TEXT);

        $stmt->execute();
    }

    private function createDefaultPresetsInSqlite()
    {
        $db = Database::getInstance()->getConnection();
        $defaults = getDefaultPresets();

        $db->exec('BEGIN');
        foreach ($defaults as $preset) {
            $this->createPresetInSqlite($preset);
        }
        $db->exec('COMMIT');
    }
}
