<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings;

if (ACLController::checkAccess('EC_Bonus', 'list', true)) {
    $module_menu[] = [
        'index.php?module=EC_Bonus&action=bonus_report',
        $mod_strings['LNK_BONUS_REPORT'],
        'Reports',
        'EC_Bonus'
    ];

    if (isManagerUser()) {
        $module_menu[] = [
            'index.php?module=EC_Bonus&action=calculate_bonus_by_source',
            $mod_strings['LNK_CALCULATE_BONUS_BY_SOURCE'],
            'Reports',
            'EC_Bonus'
        ];
        
        $module_menu[] = [
            'index.php?module=EC_Bonus&action=calculate_bonus',
            $mod_strings['LNK_CALCULATE_BONUS'],
            'Reports',
            'EC_Bonus'
        ];
    }
}