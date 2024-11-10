<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config;

if (ACLController::checkAccess('AOK_Knowledge_Base_Categories', 'edit', true)) {
    $module_menu[] = array("index.php?module=AOK_Knowledge_Base_Categories&action=EditView", $mod_strings['LNK_NEW_RECORD'], "Create", 'AOK_Knowledge_Base_Categories');
}
if (ACLController::checkAccess('AOK_Knowledge_Base_Categories', 'list', true)) {
    $module_menu[] = array("index.php?module=AOK_Knowledge_Base_Categories&action=index", $mod_strings['LNK_LIST'], "List", 'AOK_Knowledge_Base_Categories');
}
if (ACLController::checkAccess('AOK_KnowledgeBase', 'list', true)) {
    $module_menu[] = array("index.php?module=AOK_KnowledgeBase&action=index", $mod_strings['LBL_LIST_KNOWLEDGE_BASE'], "List", 'AOK_KnowledgeBase');
}
