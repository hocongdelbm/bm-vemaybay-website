<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config;

if (ACLController::checkAccess('EC_LeaveAbsenceTypes', 'edit', true)) $module_menu[] = array("index.php?module=EC_LeaveAbsenceTypes&action=EditView&return_module=EC_LeaveAbsenceTypes&return_action=DetailView", $mod_strings['LNK_NEW_RECORD'], "CreateEC_LeaveAbsenceTypes", 'EC_LeaveAbsenceTypes');
if (ACLController::checkAccess('EC_LeaveAbsenceTypes', 'list', true)) $module_menu[] = array("index.php?module=EC_LeaveAbsenceTypes&action=index&return_module=EC_LeaveAbsenceTypes&return_action=DetailView", $mod_strings['LNK_LIST'], "EC_LeaveAbsenceTypes", 'EC_LeaveAbsenceTypes');
if (ACLController::checkAccess('EC_LeaveAbsences', 'list', true)) $module_menu[] = array("index.php?module=EC_LeaveAbsences&action=index&return_module=EC_LeaveAbsences&return_action=DetailView", $mod_strings['LNK_LIST_LEAVE'], "EC_LeaveAbsences", 'EC_LeaveAbsences');
