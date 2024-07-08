<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings;

if(ACLController::checkAccess('Contacts', 'edit', true))$module_menu[] = Array("index.php?module=Contacts&action=EditView&return_module=Contacts&return_action=DetailView", $mod_strings['LNK_NEW_CONTACT'],"CreateContacts", 'Contacts');
if(ACLController::checkAccess('Contacts', 'edit', true))$module_menu[] = Array("index.php?module=Contacts&action=BusinessCard", $mod_strings['LBL_ADD_BUSINESSCARD'],"CreateContacts", 'Contacts');
if(ACLController::checkAccess('Accounts', 'edit', true))$module_menu[] = Array("index.php?module=Accounts&action=EditView&return_module=Accounts&return_action=DetailView", $mod_strings['LNK_NEW_ACCOUNT'],"CreateAccounts", 'Accounts');
if(ACLController::checkAccess('Leads', 'edit', true))$module_menu[] =	Array("index.php?module=Leads&action=EditView&return_module=Leads&return_action=DetailView", $mod_strings['LNK_NEW_LEAD'],"CreateLeads", 'Leads');
if(ACLController::checkAccess('Opportunities', 'edit', true))$module_menu[] =Array("index.php?module=Opportunities&action=EditView&return_module=Opportunities&return_action=DetailView", $mod_strings['LNK_NEW_OPPORTUNITY'],"CreateOpportunities", 'Opportunities');

if(ACLController::checkAccess('Cases', 'edit', true))$module_menu[] =Array("index.php?module=Cases&action=EditView&return_module=Cases&return_action=DetailView", $mod_strings['LNK_NEW_CASE'],"CreateCases", 'Cases');
if(ACLController::checkAccess('Bugs', 'edit', true))$module_menu[] = Array("index.php?module=Bugs&action=EditView&return_module=Bugs&return_action=DetailView", $mod_strings['LNK_NEW_BUG'],"CreateBugs", 'Bugs');
if(ACLController::checkAccess('Meetings', 'edit', true))$module_menu[] = Array("index.php?module=Meetings&action=EditView&return_module=Meetings&return_action=DetailView", $mod_strings['LNK_NEW_MEETING'],"CreateMeetings", 'Meetings');
if(ACLController::checkAccess('Calls', 'edit', true))$module_menu[] = Array("index.php?module=Calls&action=EditView&return_module=Calls&return_action=DetailView", $mod_strings['LNK_NEW_CALL'],"CreateCalls", 'Calls');
if(ACLController::checkAccess('Tasks', 'edit', true))$module_menu[] = Array("index.php?module=Tasks&action=EditView&return_module=Tasks&return_action=DetailView", $mod_strings['LNK_NEW_TASK'],"CreateTasks", 'Tasks');
if(ACLController::checkAccess('Emails', 'edit', true))$module_menu[] =Array("index.php?module=Emails&action=Compose", $mod_strings['LNK_COMPOSE_EMAIL'],"CreateEmails", 'Emails');

