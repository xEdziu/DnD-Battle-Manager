<?php
class BadgeManager
{
    public function getAllBadges()
    {
        $db = Database::getInstance()->getConnection();
        $result = $db->query('SELECT * FROM badges ORDER BY name');

        $badges = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $badges[] = $row;
        }
        return $badges;
    }

    public function getBadgeById($id)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT * FROM badges WHERE id = :id');
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $result = $stmt->execute();

        return $result->fetchArray(SQLITE3_ASSOC) ?: null;
    }

    public function createBadge($name, $color, $icon)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('INSERT INTO badges (name, color, icon) VALUES (:name, :color, :icon)');
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        $stmt->bindValue(':color', $color, SQLITE3_TEXT);
        $stmt->bindValue(':icon', $icon, SQLITE3_TEXT);
        $result = $stmt->execute();

        if ($result) {
            return $db->lastInsertRowID();
        }
        return false;
    }

    public function updateBadge($id, $name, $color, $icon)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('UPDATE badges 
                              SET name = :name, color = :color, icon = :icon 
                              WHERE id = :id');
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        $stmt->bindValue(':color', $color, SQLITE3_TEXT);
        $stmt->bindValue(':icon', $icon, SQLITE3_TEXT);

        return $stmt->execute() !== false;
    }

    public function deleteBadge($id)
    {
        // Do not allow deleting default badges (1-6)
        if ($id <= 6) {
            return false;
        }

        $db = Database::getInstance()->getConnection();

        // First, set badge_id = 1 (Active Battle) for battles using this badge
        $stmt = $db->prepare('UPDATE battles SET badge_id = 1 WHERE badge_id = :id');
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->execute();

        // Then delete the badge itself
        $stmt = $db->prepare('DELETE FROM badges WHERE id = :id');
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);

        return $stmt->execute() !== false;
    }

    public function getAvailableColors()
    {
        return [
            'crimson' => '#8B0000',
            'gold' => '#DAA520',
            'emerald' => '#228B22',
            'royal' => '#4169E1',
            'shadow' => '#2F2F2F',
            'stone' => '#696969',
            'purple' => '#9333EA',
            'pink' => '#EC4899',
            'orange' => '#EA580C',
            'cyan' => '#0891B2',
            'gray' => '#6B7280'
        ];
    }

    public function getAvailableIcons()
    {
        return [
            'zap',
            'crown',
            'star',
            'sword',
            'shield',
            'heart',
            'clock',
            'check-circle',
            'x-circle',
            'alert-triangle',
            'shuffle',
            'target',
            'flame',
            'lightning',
            'gem',
            'trophy',
            'flag',
            'map-pin',
            'compass',
            'bookmark'
        ];
    }
}
