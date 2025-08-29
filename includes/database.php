<?php
// Database utility functions

class Database
{
    private static $instance = null;
    private $db = null;

    private function __construct()
    {
        $this->connect();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function connect()
    {
        $initNewDb = !file_exists(DB_FILE);
        $this->db = new SQLite3(DB_FILE);

        // Performance optimizations
        $this->db->exec('PRAGMA foreign_keys = ON');
        $this->db->exec('PRAGMA journal_mode = WAL'); // Write-Ahead Logging for better concurrency
        $this->db->exec('PRAGMA cache_size = 10000'); // Increase cache size
        $this->db->exec('PRAGMA temp_store = MEMORY'); // Store temp tables in memory
        $this->db->exec('PRAGMA synchronous = NORMAL'); // Faster than FULL, but still safe

        if ($initNewDb) {
            $this->createTables();
        } else {
            $this->migrateDatabase();
        }
    }

    public function getConnection()
    {
        return $this->db;
    }

    private function migrateDatabase()
    {
        // Check if modifier columns exist and remove them
        $tables = ['presets', 'participants'];
        foreach ($tables as $table) {
            $this->removeModifierColumns($table);
        }

        // Add badge system tables and columns
        $this->addBadgeSystem();

        // Add description field to battles table
        $this->addBattleDescription();
    }

    private function addBadgeSystem()
    {
        // Check if badges table exists
        $result = $this->db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='badges'");
        if (!$result->fetchArray()) {
            // Create badges table
            $this->db->exec('
                CREATE TABLE badges (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL,
                    color TEXT NOT NULL DEFAULT "blue",
                    icon TEXT NOT NULL DEFAULT "zap",
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                );
            ');

            // Insert default badges
            $this->db->exec('
                INSERT INTO badges (id, name, color, icon) VALUES
                (1, "Active Battle", "gold", "zap"),
                (2, "Boss Fight", "crimson", "crown"),
                (3, "Random Encounter", "emerald", "shuffle"),
                (4, "Important", "royal", "star"),
                (5, "In Progress", "stone", "clock"),
                (6, "Completed", "gray", "check-circle")
            ');
        }

        // Check if battles table has badge_id column
        $result = $this->db->query("PRAGMA table_info(battles)");
        $hasBadgeColumn = false;
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            if ($row['name'] === 'badge_id') {
                $hasBadgeColumn = true;
                break;
            }
        }

        if (!$hasBadgeColumn) {
            $this->db->exec('ALTER TABLE battles ADD COLUMN badge_id INTEGER DEFAULT 1');
            $this->db->exec('CREATE INDEX IF NOT EXISTS idx_battles_badge_id ON battles(badge_id)');
        }

        // Check if presets table has character_type column
        $result = $this->db->query("PRAGMA table_info(presets)");
        $hasCharacterTypeColumn = false;
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            if ($row['name'] === 'character_type') {
                $hasCharacterTypeColumn = true;
                break;
            }
        }

        if (!$hasCharacterTypeColumn) {
            $this->db->exec('ALTER TABLE presets ADD COLUMN character_type TEXT DEFAULT "npc"');
            $this->db->exec('CREATE INDEX IF NOT EXISTS idx_presets_character_type ON presets(character_type)');
        }

        // Check if participants table has character_type column
        $result = $this->db->query("PRAGMA table_info(participants)");
        $hasParticipantCharacterTypeColumn = false;
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            if ($row['name'] === 'character_type') {
                $hasParticipantCharacterTypeColumn = true;
                break;
            }
        }

        if (!$hasParticipantCharacterTypeColumn) {
            $this->db->exec('ALTER TABLE participants ADD COLUMN character_type TEXT DEFAULT "enemy"');
            $this->db->exec('CREATE INDEX IF NOT EXISTS idx_participants_character_type ON participants(character_type)');
        }
    }

    private function addBattleDescription()
    {
        // Check if battles table has description column
        $result = $this->db->query("PRAGMA table_info(battles)");
        $hasDescriptionColumn = false;
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            if ($row['name'] === 'description') {
                $hasDescriptionColumn = true;
                break;
            }
        }

        if (!$hasDescriptionColumn) {
            $this->db->exec('ALTER TABLE battles ADD COLUMN description TEXT DEFAULT ""');
        }
    }

    private function removeModifierColumns($tableName)
    {
        // Check if modifier columns exist
        $result = $this->db->query("PRAGMA table_info($tableName)");
        $hasModColumns = false;
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            if (strpos($row['name'], '_mod') !== false) {
                $hasModColumns = true;
                break;
            }
        }

        if ($hasModColumns) {
            // Create new table without modifier columns
            $this->db->exec('BEGIN TRANSACTION');

            if ($tableName === 'presets') {
                $this->db->exec('
                    CREATE TABLE presets_new (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        name TEXT,
                        str INTEGER,
                        dex INTEGER,
                        con INTEGER,
                        int INTEGER,
                        wis INTEGER,
                        cha INTEGER,
                        ac INTEGER,
                        hp TEXT,
                        passive INTEGER,
                        skills TEXT,
                        actions TEXT,
                        notes TEXT
                    );
                ');
                $this->db->exec('
                    INSERT INTO presets_new (id, name, str, dex, con, int, wis, cha, ac, hp, passive, skills, actions, notes)
                    SELECT id, name, str, dex, con, int, wis, cha, ac, hp, passive, skills, actions, notes FROM presets;
                ');
            } else {
                $this->db->exec('
                    CREATE TABLE participants_new (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        battle_id INTEGER,
                        name TEXT,
                        str INTEGER,
                        dex INTEGER,
                        con INTEGER,
                        int INTEGER,
                        wis INTEGER,
                        cha INTEGER,
                        ac INTEGER,
                        hp_current INTEGER,
                        hp_max INTEGER,
                        passive INTEGER,
                        skills TEXT,
                        actions TEXT,
                        notes TEXT,
                        initiative INTEGER DEFAULT 0
                    );
                ');
                $this->db->exec('
                    INSERT INTO participants_new (id, battle_id, name, str, dex, con, int, wis, cha, ac, hp_current, hp_max, passive, skills, actions, notes, initiative)
                    SELECT id, battle_id, name, str, dex, con, int, wis, cha, ac, hp_current, hp_max, passive, skills, actions, notes, initiative FROM participants;
                ');
            }

            $this->db->exec("DROP TABLE $tableName");
            $this->db->exec("ALTER TABLE {$tableName}_new RENAME TO $tableName");
            $this->db->exec('COMMIT');
        }
    }

    private function createTables()
    {
        $this->db->exec('
            CREATE TABLE IF NOT EXISTS presets (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT,
                str INTEGER,
                dex INTEGER,
                con INTEGER,
                int INTEGER,
                wis INTEGER,
                cha INTEGER,
                ac INTEGER,
                hp TEXT,
                passive INTEGER,
                skills TEXT,
                actions TEXT,
                notes TEXT,
                character_type TEXT DEFAULT "npc"
            );
        ');

        $this->db->exec('
            CREATE TABLE IF NOT EXISTS battles (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT,
                description TEXT DEFAULT "",
                badge_id INTEGER DEFAULT NULL,
                FOREIGN KEY (badge_id) REFERENCES badges(id)
            );
        ');

        $this->db->exec('
            CREATE TABLE IF NOT EXISTS participants (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                battle_id INTEGER,
                name TEXT,
                str INTEGER,
                dex INTEGER,
                con INTEGER,
                int INTEGER,
                wis INTEGER,
                cha INTEGER,
                ac INTEGER,
                hp_current INTEGER,
                hp_max INTEGER,
                passive INTEGER,
                skills TEXT,
                actions TEXT,
                notes TEXT,
                initiative INTEGER DEFAULT 0
            );
        ');

        $this->db->exec('
            CREATE TABLE IF NOT EXISTS badges (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                color TEXT NOT NULL DEFAULT "blue",
                icon TEXT NOT NULL DEFAULT "zap",
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
        ');

        // Insert default badges
        $this->db->exec('
            INSERT OR IGNORE INTO badges (id, name, color, icon) VALUES
            (1, "Active Battle", "gold", "zap"),
            (2, "Boss Fight", "crimson", "crown"),
            (3, "Random Encounter", "emerald", "shuffle"),
            (4, "Important", "royal", "star"),
            (5, "In Progress", "stone", "clock"),
            (6, "Completed", "gray", "check-circle")
        ');

        // Create indexes for better performance
        $this->db->exec('CREATE INDEX IF NOT EXISTS idx_participants_battle_id ON participants(battle_id)');
        $this->db->exec('CREATE INDEX IF NOT EXISTS idx_participants_initiative ON participants(battle_id, initiative DESC)');
        $this->db->exec('CREATE INDEX IF NOT EXISTS idx_battles_badge_id ON battles(badge_id)');

        // Migration: Add character_type to participants table
        $result = $this->db->query("PRAGMA table_info(participants)");
        $hasCharacterType = false;
        while ($row = $result->fetchArray()) {
            if ($row['name'] === 'character_type') {
                $hasCharacterType = true;
                break;
            }
        }

        if (!$hasCharacterType) {
            $this->db->exec('ALTER TABLE participants ADD COLUMN character_type TEXT DEFAULT "enemy"');
        }
    }

    public function close()
    {
        if ($this->db) {
            $this->db->close();
            $this->db = null;
        }
    }

    public function __destruct()
    {
        $this->close();
    }
}
