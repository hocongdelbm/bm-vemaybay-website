<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings;
global $current_user;

$default = 'index.php?module=Emails&action=ListView&assigned_user_id='.$current_user->id;

$e = BeanFactory::newBean('Emails');

// my inbox
// if (ACLController::checkAccess('Emails', 'edit', true)) {
//     $module_menu[]=array("index.php?module=Emails&action=ComposeView&return_module=Emails&return_action=index", $mod_strings['LNK_NEW_SEND_EMAIL'],"Create", 'Emails');
// }
// if (ACLController::checkAccess('Emails', 'list', true)) {
//     $module_menu[]=array("index.php?module=Emails&action=index&return_module=Emails&return_action=DetailView", $mod_strings['LNK_VIEW_MY_INBOX'],"List", 'Emails');
// }


// my inbox
if(ACLController::checkAccess('Emails', 'edit', true)) {
	$module_menu[] = array('index.php?module=Emails&action=index', $mod_strings['LNK_MY_INBOX'],"EmailFolder","Emails");
}
if(ACLController::checkAccess('Emails', 'edit', true)) {
	$module_menu[] = array('index.php?module=InboundEmail&action=index', $mod_strings['LNK_LIST_MAILBOXES'],"EmailFolder","Emails");
}



// create email template
// if(ACLController::checkAccess('EmailTemplates', 'edit', true)) $module_menu[] = array("index.php?module=EmailTemplates&action=EditView&return_module=EmailTemplates&return_action=DetailView", $mod_strings['LNK_NEW_EMAIL_TEMPLATE'],"CreateEmails","Emails");

// email templates
// if(ACLController::checkAccess('Emails', 'list', true)) $module_menu[] = array("index.php?module=Emails&action=ListViewAll&all=true", $mod_strings['LNK_ALL_EMAIL_LIST'],"EmailFolder","Emails");
// if(ACLController::checkAccess('EmailTemplates', 'edit', true)) $module_menu[] = array("index.php?module=EmailTemplates&action=index", $mod_strings['LNK_EMAIL_TEMPLATE_LIST'],"EmailFolder", 'Emails');

