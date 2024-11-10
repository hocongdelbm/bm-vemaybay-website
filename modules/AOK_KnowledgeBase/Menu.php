<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
global $mod_strings, $app_strings, $sugar_config;

if (ACLController::checkAccess('AOK_KnowledgeBase', 'edit', true)) {
    $module_menu[] = array("index.php?module=AOK_KnowledgeBase&action=EditView", $mod_strings['LNK_NEW_RECORD'], "Create", 'AOK_KnowledgeBase');
}
if (ACLController::checkAccess('AOK_KnowledgeBase', 'list', true)) {
    $module_menu[] = array("index.php?module=AOK_KnowledgeBase&action=index", $mod_strings['LNK_LIST'], "List", 'AOK_KnowledgeBase');
}
if (ACLController::checkAccess('AOK_Knowledge_Base_Categories', 'list', true)) {
    $module_menu[] = array("index.php?module=AOK_Knowledge_Base_Categories&action=index", $mod_strings['LBL_LIST_CATE'], "List", 'AOK_Knowledge_Base_Categories');
}
