<?php

class PresetHandler
{
    private $presetManager;

    public function __construct()
    {
        $this->presetManager = new PresetManager();
    }

    public function handle()
    {
        $action = $_POST['action'] ?? $_GET['action'] ?? '';

        switch ($action) {
            case 'add_preset':
                $this->addPreset();
                break;
            case 'edit_preset':
                $this->editPreset();
                break;
            case 'delete_preset':
                $this->deletePreset();
                break;
        }
    }

    private function addPreset()
    {
        $presetData = $this->collectPresetData();
        $this->presetManager->createPreset($presetData);
        redirect('index.php?page=presets');
    }

    private function editPreset()
    {
        $presetId = intval($_POST['preset_id']);
        $presetData = $this->collectPresetData();
        $this->presetManager->updatePreset($presetId, $presetData);
        redirect('index.php?page=presets');
    }

    private function deletePreset()
    {
        $presetId = intval($_GET['id']);
        $this->presetManager->deletePreset($presetId);
        redirect('index.php?page=presets');
    }

    private function collectPresetData()
    {
        $stats = ['str', 'dex', 'con', 'int', 'wis', 'cha'];
        $data = [
            'name' => $_POST['name'] ?? 'Unnamed',
            'ac' => intval($_POST['ac'] ?? 0),
            'hp' => $_POST['hp'] ?? '0',
            'passive' => intval($_POST['passive'] ?? 0),
            'skills' => $_POST['skills'] ?? '',
            'actions' => $_POST['actions'] ?? '',
            'notes' => $_POST['notes'] ?? '',
            'character_type' => $_POST['character_type'] ?? 'enemy'
        ];

        foreach ($stats as $stat) {
            $data[$stat] = intval($_POST[$stat] ?? 0);
        }

        return $data;
    }
}
