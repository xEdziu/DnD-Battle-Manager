<?php
// Utility functions

// Parse dice formula like "2d6+2" and return (average, randomRoll)
function parseDiceFormula($formula)
{
    // Remove spaces and lowercase
    $formula = str_replace(' ', '', strtolower($formula));
    if (!preg_match('/^(\d+)d(\d+)([+-]\d+)?$/', $formula, $matches)) {
        // not a dice formula, treat as number
        $num = intval($formula);
        return ['avg' => $num, 'roll' => $num];
    }

    $count = intval($matches[1]);
    $faces = intval($matches[2]);
    $modifier = 0;
    if (isset($matches[3]) && $matches[3] !== '') {
        $modifier = intval($matches[3]);
    }

    // average of one die is (faces+1)/2
    $avg = intval(floor($count * ($faces + 1) / 2 + $modifier));

    // roll random
    $sum = 0;
    for ($i = 0; $i < $count; $i++) {
        $sum += random_int(1, $faces);
    }
    $roll = $sum + $modifier;

    return ['avg' => $avg, 'roll' => $roll];
}

// Get default presets data
function getDefaultPresets()
{
    return [
        [
            'id' => 0, // id will be set when inserting (0 as placeholder)
            'name' => 'Goblin',
            'str' => 8,
            'dex' => 14,
            'con' => 10,
            'int' => 10,
            'wis' => 8,
            'cha' => 8,
            'ac' => 15,
            'hp' => '2d6', // stored as formula string
            'passive' => 9,
            'skills' => 'Stealth +6',
            'actions' => 'Scimitar (melee attack +4, 1d6+2 damage); Shortbow (ranged attack +4, 1d6+2 damage)',
            'notes' => 'Small humanoid, cunning and stealthy.',
            'character_type' => 'enemy'
        ],
        [
            'id' => 0,
            'name' => 'Wolf',
            'str' => 12,
            'dex' => 15,
            'con' => 12,
            'int' => 3,
            'wis' => 12,
            'cha' => 6,
            'ac' => 13,
            'hp' => '2d8+2',
            'passive' => 13,
            'skills' => 'Perception +5',
            'actions' => 'Bite (melee attack +4, 2d4+2 piercing damage, chance to knock prone)',
            'notes' => 'Medium beast, often fights in packs.',
            'character_type' => 'enemy'
        ],
        [
            'id' => 0,
            'name' => 'Alice (Player)',
            'str' => 15,
            'dex' => 12,
            'con' => 14,
            'int' => 10,
            'wis' => 13,
            'cha' => 8,
            'ac' => 16,
            'hp' => '20', // players typically have fixed max HP
            'passive' => 11,
            'skills' => 'Athletics +4, Perception +3',
            'actions' => 'Longsword (melee attack +5, 1d8+2 slashing); Light Crossbow (ranged attack +3, 1d8+1 piercing)',
            'notes' => 'Human Fighter, Level 3',
            'character_type' => 'pc'
        ]
    ];
}

// Calculate ability modifier based on ability score
function calculateModifier($abilityScore)
{
    return floor(($abilityScore - 10) / 2);
}

// Format modifier with + or - sign
function formatModifier($modifier)
{
    if ($modifier >= 0) {
        return '+' . $modifier;
    } else {
        return (string)$modifier;
    }
}

// Escape HTML output
function h($text)
{
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

// Redirect helper
function redirect($url)
{
    header("Location: $url");
    exit;
}
