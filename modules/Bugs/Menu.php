<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings,$app_strings;
if (ACLController::checkAccess('Bugs', 'edit', true)) {
    $module_menu [] =	array("index.php?module=Bugs&action=EditView&return_module=Bugs&return_action=DetailView", $mod_strings['LNK_NEW_BUG'],"Create", 'Bugs');
}
if (ACLController::checkAccess('Bugs', 'list', true)) {
    $module_menu [] =		array("index.php?module=Bugs&action=index&return_module=Bugs&return_action=DetailView", $mod_strings['LNK_BUG_LIST'],"List", 'Bugs');
}
// if (ACLController::checkAccess('Bugs', 'import', true)) {
//     $module_menu[] =array("index.php?module=Import&action=Step1&import_module=Bugs&return_module=Bugs&return_action=index", $mod_strings['LNK_IMPORT_BUGS'],"Import", 'Bugs');
// }
if (ACLController::checkAccess('AOK_KnowledgeBase', 'list', true)) {
    $module_menu[] = array("index.php?module=AOK_KnowledgeBase&action=index", $mod_strings['LBL_LIST_KNOWLEDGE_BASE'], "List", 'AOK_KnowledgeBase');
}