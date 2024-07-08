<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings;
if (ACLController::checkAccess('Campaigns', 'edit', true)) {
    $module_menu[] = array(
        "index.php?module=Campaigns&action=WizardHome&return_module=Campaigns&return_action=index",
        $mod_strings['LNL_NEW_CAMPAIGN_WIZARD'], "Create"
    );
}
/*
if(ACLController::checkAccess('Campaigns', 'edit', true))
    $module_menu[]=	array(
        "index.php?module=Campaigns&action=EditView&return_module=Campaigns&return_action=index",
        $mod_strings['LNK_NEW_CAMPAIGN'],"CreateCampaigns"
    );
*/
if (ACLController::checkAccess('Campaigns', 'list', true)) {
    $module_menu[] =    array(
        "index.php?module=Campaigns&action=index&return_module=Campaigns&return_action=index",
        $mod_strings['LNK_CAMPAIGN_LIST'], "List"
    );
}
//if(ACLController::checkAccess('Campaigns', 'list', true))
//	$module_menu[]= array(
//		"index.php?module=Campaigns&action=newsletterlist&return_module=Campaigns&return_action=index",
//		$mod_strings['LBL_NEWSLETTERS'], "Newsletters"
//	);
if (ACLController::checkAccess('EmailTemplates', 'edit', true)) {
    $module_menu[] = array(
        "index.php?module=EmailTemplates&action=EditView&return_module=EmailTemplates&return_action=DetailView",
        $mod_strings['LNK_NEW_EMAIL_TEMPLATE'], "View_Create_Email_Templates", "Emails"
    );
}
if (ACLController::checkAccess('EmailTemplates', 'list', true)) {
    $module_menu[] = array(
        "index.php?module=EmailTemplates&action=index",
        $mod_strings['LNK_EMAIL_TEMPLATE_LIST'], "View_Email_Templates", 'Emails'
    );
}
if (is_admin($GLOBALS['current_user']) || is_admin_for_module($GLOBALS['current_user'], 'Campaigns')) {
    $module_menu[] = array(
        "index.php?module=Campaigns&action=WizardEmailSetup&return_module=Campaigns&return_action=index",
        $mod_strings['LBL_EMAIL_SETUP_WIZARD'], "Setup_Email"
    );
}
if (ACLController::checkAccess('Campaigns', 'edit', true)) {
    $module_menu[] = array(
        "index.php?module=Campaigns&action=CampaignDiagnostic&return_module=Campaigns&return_action=index",
        $mod_strings['LBL_DIAGNOSTIC_WIZARD'], "View_Diagnostics"
    );
}
if (ACLController::checkAccess('Campaigns', 'edit', true)) {
    $module_menu[] = array(
        "index.php?module=Campaigns&action=WebToLeadCreation&return_module=Campaigns&return_action=index" . (isset($_REQUEST['record']) ? ('&campaign_id=' . $_REQUEST['record']) : ''),
        $mod_strings['LBL_WEB_TO_LEAD'], "Create_Person_Form"
    );
}
// if (ACLController::checkAccess('Campaigns', 'import', true)) {
//     $module_menu[] = array(
//         "index.php?module=Import&action=Step1&import_module=Campaigns&return_module=Campaigns&return_action=index",
//         $mod_strings['LNK_IMPORT_CAMPAIGNS'],
//         "Import",
//         'Campaigns'
//     );
// }
