<?php
if (!defined('sugarEntry')) {
    define('sugarEntry', true);
}
require_once 'modules/AOP_Case_Updates/util.php';
if (!isAOPEnabled()) {
    return;
}
global $sugar_config, $mod_strings;

require_once('modules/Contacts/Contact.php');

$bean = BeanFactory::newBean('Contacts');
$bean->retrieve($_REQUEST['record']);

if (array_key_exists("aop", $sugar_config) && array_key_exists("joomla_url", $sugar_config['aop'])) {
    $portalURL = $sugar_config['aop']['joomla_url'];
    $wbsv = file_get_contents($portalURL.'/index.php?option=com_advancedopenportal&task=create&sug='.$_REQUEST['record']);
    $res = json_decode($wbsv);
    if (!$res->success) {
        $msg = $res->error ? $res->error : $mod_strings['LBL_CREATE_PORTAL_USER_FAILED'];
        SugarApplication::appendErrorMessage($msg);
    } else {
        SugarApplication::appendErrorMessage($mod_strings['LBL_CREATE_PORTAL_USER_SUCCESS']);
    }
} else {
    SugarApplication::appendErrorMessage($mod_strings['LBL_NO_JOOMLA_URL']);
}

SugarApplication::redirect("index.php?module=Contacts&action=DetailView&record=".$_REQUEST['record']);
