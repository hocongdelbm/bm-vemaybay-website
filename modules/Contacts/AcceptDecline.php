<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $sugar_config, $dbconfig, $beanList, $beanFiles, $app_strings, $app_list_strings, $current_user;

global $currentModule, $focus;

if (!empty($_REQUEST['user_id'])) {
    $current_user = BeanFactory::newBean('Users');
    $result = $current_user->retrieve($_REQUEST['user_id']);
    if ($result == null) {
        session_destroy();
        sugar_cleanup();
        die("The user id doesn't exist");
    }
    $current_entity = $current_user;
} elseif (! empty($_REQUEST['contact_id'])) {
    $current_entity = BeanFactory::newBean('Contacts');
    $current_entity->disable_row_level_security = true;
    $result = $current_entity->retrieve($_REQUEST['contact_id']);
    if ($result == null) {
        session_destroy();
        sugar_cleanup();
        die("The contact id doesn't exist");
    }
} 

$bean = $beanList[clean_string($_REQUEST['module'])];
require_once($beanFiles[$bean]);
$focus = new $bean;
$focus->disable_row_level_security = true;
$result = $focus->retrieve($_REQUEST['record']);

if ($result == null) {
    session_destroy();
    sugar_cleanup();
    die("The focus id doesn't exist");
}

$focus->set_accept_status($current_entity, $_REQUEST['accept_status']);

print $app_strings['LBL_STATUS_UPDATED']."<BR><BR>";
print $app_strings['LBL_STATUS']. " ". $app_list_strings['dom_meeting_accept_status'][$_REQUEST['accept_status']];
print "<BR><BR>";

print "<a href='?module=$currentModule&action=DetailView&record=$focus->id'>".$app_strings['LBL_MEETING_GO_BACK']."</a><br>";
sugar_cleanup();
exit;
