<?php

class ImportExportHandler
{

    public function handle()
    {
        $action = $_GET['action'] ?? '';

        switch ($action) {
            case 'export_json':
                $this->exportJson();
                break;
        }

        // Handle file upload
        if (isset($_FILES['import_file']) && $_FILES['import_file']['error'] === UPLOAD_ERR_OK) {
            $this->handleImport();
        }
    }

    private function exportJson()
    {
        $exportData = $this->exportFromSqlite();

        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="dnd_battle_data.json"');
        echo json_encode($exportData);
        exit;
    }

    private function handleImport()
    {
        $fileTmp = $_FILES['import_file']['tmp_name'];
        $fileName = $_FILES['import_file']['name'];

        if (preg_match('/\.sqlite$/i', $fileName)) {
            $this->importSqliteFile($fileTmp);
        } elseif (preg_match('/\.json$/i', $fileName)) {
            $this->importJsonFile($fileTmp);
        }

        // Redirect to avoid re-posting on refresh
        $params = '';
        if (getError()) {
            $params .= 'error=' . urlencode(getError());
        }
        if (getSuccess()) {
            $params .= ($params ? '&' : '') . 'success=' . urlencode(getSuccess());
        }
        redirect('index.php' . ($params ? '?' . $params : ''));
    }

    private function importSqliteFile($fileTmp)
    {
        try {
            // Verify SQLite file
            $newdb = new SQLite3($fileTmp);
            $tableCount = $newdb->querySingle("SELECT COUNT(*) FROM sqlite_master WHERE type='table' AND name='presets'");
            $newdb->close();

            if ($tableCount === null) {
                setError('Invalid SQLite database file.');
                return;
            }

            // Replace current database
            if (file_exists(DB_FILE)) {
                Database::getInstance()->close();
                unlink(DB_FILE);
            }

            rename($fileTmp, DB_FILE);
            setSuccess('Data imported successfully.');
        } catch (Exception $e) {
            setError('Error importing SQLite file: ' . $e->getMessage());
        }
    }

    private function importJsonFile($fileTmp)
    {
        $jsonStr = file_get_contents($fileTmp);
        $importData = json_decode($jsonStr, true);

        if ($importData === null || !isset($importData['presets']) || !isset($importData['battles'])) {
            setError('Invalid JSON file format.');
            return;
        }

        $this->importJsonToSqlite($importData);
        setSuccess('Data imported successfully.');
    }

    private function importJsonToSqlite($importData)
    {
        $db = Database::getInstance()->getConnection();

        $db->exec('BEGIN');
        $db->exec('DELETE FROM participants');
        $db->exec('DELETE FROM battles');
        $db->exec('DELETE FROM presets');

        // Import presets
        foreach ($importData['presets'] as $p) {
            $stmt = $db->prepare('INSERT INTO presets (id, name, str, dex, con, int, wis, cha, ac, hp, passive, skills, actions, notes)
                                   VALUES (:id, :name, :str, :dex, :con, :int, :wis, :cha, :ac, :hp, :passive, :skills, :actions, :notes)');

            $stmt->bindValue(':id', $p['id'], SQLITE3_INTEGER);
            $stmt->bindValue(':name', $p['name'], SQLITE3_TEXT);
            $stmt->bindValue(':str', $p['str'], SQLITE3_INTEGER);
            $stmt->bindValue(':dex', $p['dex'], SQLITE3_INTEGER);
            $stmt->bindValue(':con', $p['con'], SQLITE3_INTEGER);
            $stmt->bindValue(':int', $p['int'], SQLITE3_INTEGER);
            $stmt->bindValue(':wis', $p['wis'], SQLITE3_INTEGER);
            $stmt->bindValue(':cha', $p['cha'], SQLITE3_INTEGER);
            $stmt->bindValue(':ac', $p['ac'], SQLITE3_INTEGER);
            $stmt->bindValue(':hp', $p['hp'], SQLITE3_TEXT);
            $stmt->bindValue(':passive', $p['passive'], SQLITE3_INTEGER);
            $stmt->bindValue(':skills', $p['skills'], SQLITE3_TEXT);
            $stmt->bindValue(':actions', $p['actions'], SQLITE3_TEXT);
            $stmt->bindValue(':notes', $p['notes'], SQLITE3_TEXT);
            $stmt->execute();
        }

        // Import battles
        foreach ($importData['battles'] as $b) {
            $stmtB = $db->prepare('INSERT INTO battles (id, name, description) VALUES (:id, :name, :description)');
            $stmtB->bindValue(':id', $b['id'], SQLITE3_INTEGER);
            $stmtB->bindValue(':name', $b['name'], SQLITE3_TEXT);
            $stmtB->bindValue(':description', $b['description'] ?? '', SQLITE3_TEXT);
            $stmtB->execute();

            // Import participants for this battle
            if (isset($b['participants'])) {
                foreach ($b['participants'] as $pr) {
                    $stmtPr = $db->prepare('INSERT INTO participants (id, battle_id, name, str, dex, con, int, wis, cha, ac, hp_current, hp_max, passive, skills, actions, notes, initiative)
                                            VALUES (:id, :battle_id, :name, :str, :dex, :con, :int, :wis, :cha, :ac, :hp_current, :hp_max, :passive, :skills, :actions, :notes, :initiative)');

                    $stmtPr->bindValue(':id', $pr['id'], SQLITE3_INTEGER);
                    $stmtPr->bindValue(':battle_id', $pr['battle_id'], SQLITE3_INTEGER);
                    $stmtPr->bindValue(':name', $pr['name'], SQLITE3_TEXT);
                    $stmtPr->bindValue(':str', $pr['str'], SQLITE3_INTEGER);
                    $stmtPr->bindValue(':dex', $pr['dex'], SQLITE3_INTEGER);
                    $stmtPr->bindValue(':con', $pr['con'], SQLITE3_INTEGER);
                    $stmtPr->bindValue(':int', $pr['int'], SQLITE3_INTEGER);
                    $stmtPr->bindValue(':wis', $pr['wis'], SQLITE3_INTEGER);
                    $stmtPr->bindValue(':cha', $pr['cha'], SQLITE3_INTEGER);
                    $stmtPr->bindValue(':ac', $pr['ac'], SQLITE3_INTEGER);
                    $stmtPr->bindValue(':hp_current', $pr['hp_current'], SQLITE3_INTEGER);
                    $stmtPr->bindValue(':hp_max', $pr['hp_max'], SQLITE3_INTEGER);
                    $stmtPr->bindValue(':passive', $pr['passive'], SQLITE3_INTEGER);
                    $stmtPr->bindValue(':skills', $pr['skills'], SQLITE3_TEXT);
                    $stmtPr->bindValue(':actions', $pr['actions'], SQLITE3_TEXT);
                    $stmtPr->bindValue(':notes', $pr['notes'], SQLITE3_TEXT);
                    $stmtPr->bindValue(':initiative', $pr['initiative'], SQLITE3_INTEGER);
                    $stmtPr->execute();
                }
            }
        }

        $db->exec('COMMIT');
    }

    private function exportFromSqlite()
    {
        $db = Database::getInstance()->getConnection();

        // Load all presets
        $presetsArr = [];
        $presRes = $db->query('SELECT * FROM presets');
        while ($row = $presRes->fetchArray(SQLITE3_ASSOC)) {
            $presetsArr[] = $row;
        }

        // Load battles and participants
        $battlesArr = [];
        $batRes = $db->query('SELECT * FROM battles');
        while ($bat = $batRes->fetchArray(SQLITE3_ASSOC)) {
            $battleId = $bat['id'];
            $partsRes = $db->query("SELECT * FROM participants WHERE battle_id = $battleId");
            $participants = [];
            while ($pr = $partsRes->fetchArray(SQLITE3_ASSOC)) {
                $participants[] = $pr;
            }
            $bat['participants'] = $participants;
            $battlesArr[] = $bat;
        }

        return ['presets' => $presetsArr, 'battles' => $battlesArr];
    }
}
