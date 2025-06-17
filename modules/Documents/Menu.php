<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $current_user;


if (ACLController::checkAccess('Documents', 'edit', true)) {
    $module_menu[]=array("index.php?module=Documents&action=EditView&return_module=Documents&return_action=DetailView", $mod_strings['LNK_NEW_DOCUMENT'],"Create");
}
if (ACLController::checkAccess('Documents', 'list', true)) {
    $module_menu[]=array("index.php?module=Documents&action=index", $mod_strings['LNK_DOCUMENT_LIST'],"List");
}
if (ACLController::checkAccess('Documents', 'edit', true)) {
    $admin = BeanFactory::newBean('Administration');
    $admin->retrieveSettings();
    $user_merge = $current_user->getPreference('mailmerge_on');
    if ($user_merge == 'on' && isset($admin->settings['system_mailmerge_on']) && $admin->settings['system_mailmerge_on']) {
        $module_menu[]=array("index.php?module=MailMerge&action=index&reset=true", $mod_strings['LNK_NEW_MAIL_MERGE'],"Documents");
    }
}
