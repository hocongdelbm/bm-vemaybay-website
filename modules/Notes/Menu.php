<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}


global $mod_strings, $app_strings;

// if (ACLController::checkAccess('Notes', 'edit', true)) {
//     $module_menu[]=array("index.php?module=Notes&action=EditView&return_module=Notes&return_action=DetailView", $mod_strings['LNK_NEW_NOTE'],"Create");
// }
// if (ACLController::checkAccess('Notes', 'list', true)) {
//     $module_menu[]=array("index.php?module=Notes&action=index&return_module=Notes&return_action=DetailView", $mod_strings['LNK_NOTE_LIST'],"List");
// }
// if (ACLController::checkAccess('Notes', 'import', true)) {
//     $module_menu[]=array("index.php?module=Import&action=Step1&import_module=Notes&return_module=Notes&return_action=index", $mod_strings['LNK_IMPORT_NOTES'],"Import", 'Notes');
// }

if (ACLController::checkAccess('Calls', 'edit', true)) $module_menu[] = array("index.php?module=Calls&action=EditView&return_module=Calls&return_action=DetailView", $mod_strings['LNK_NEW_CALL'], "CreateCalls");
if (ACLController::checkAccess('Meetings', 'edit', true)) $module_menu[] = array("index.php?module=Meetings&action=EditView&return_module=Meetings&return_action=DetailView", $mod_strings['LNK_NEW_MEETING'], "CreateMeetings");
if (ACLController::checkAccess('Tasks', 'edit', true)) $module_menu[] = array("index.php?module=Tasks&action=EditView&return_module=Tasks&return_action=DetailView", $mod_strings['LNK_NEW_TASK'], "CreateTasks");
if (ACLController::checkAccess('Notes', 'edit', true)) $module_menu[] = array("index.php?module=Notes&action=EditView&return_module=Notes&return_action=DetailView", $mod_strings['LNK_NEW_NOTE'], "CreateNotes");
if (ACLController::checkAccess('Calls', 'list', true)) $module_menu[] = array("index.php?module=Calls&action=index&return_module=Calls&return_action=DetailView", $mod_strings['LNK_CALL_LIST'], "Calls");
if (ACLController::checkAccess('Meetings', 'list', true)) $module_menu[] = array("index.php?module=Meetings&action=index&return_module=Meetings&return_action=DetailView", $mod_strings['LNK_MEETING_LIST'], "Meetings");
if (ACLController::checkAccess('Tasks', 'list', true)) $module_menu[] = array("index.php?module=Tasks&action=index&return_module=Tasks&return_action=DetailView", $mod_strings['LNK_TASK_LIST'], "Tasks");
if (ACLController::checkAccess('Notes', 'list', true)) $module_menu[] = array("index.php?module=Notes&action=index&return_module=Notes&return_action=DetailView", $mod_strings['LNK_NOTE_LIST'], "Notes");
if (ACLController::checkAccess('Emails', 'list', true)) $module_menu[] = array("index.php?module=Emails&action=index&return_module=Emails&return_action=DetailView", $mod_strings['LNK_EMAIL_LIST'], "Emails");
if (ACLController::checkAccess('Calendar', 'list', true)) $module_menu[] = array("index.php?module=Calendar&action=index&view=day", $mod_strings['LNK_VIEW_CALENDAR'], "Calendar");
if (ACLController::checkAccess('Notes', 'import', true)) $module_menu[] = array("index.php?module=Import&action=Step1&import_module=Notes&return_module=Notes&return_action=index", $app_strings['LBL_IMPORT'], "Import", 'Notes');
