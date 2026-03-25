<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('include/formbase.php');

global $beanFiles, $beanList;
$bean_name = $beanList[$_REQUEST['module']];
require_once($beanFiles[$bean_name]);
$focus = new $bean_name();
if (empty($_REQUEST['linked_id']) || empty($_REQUEST['linked_field'])  || empty($_REQUEST['record'])) {
    die("need linked_field, linked_id and record fields");
}
$linked_field = $_REQUEST['linked_field'];
$record = $_REQUEST['record'];
$linked_id = $_REQUEST['linked_id'];
if ($linked_field === 'aclroles') {
    if (!ACLController::checkAccess($bean_name, 'edit', true)) {
        ACLController::displayNoAccess();
        sugar_cleanup(true);
    }
}
if ($linked_field === 'aclroles') {
    if (!ACLController::checkAccess($bean_name, 'edit', true)) {
        ACLController::displayNoAccess();
        sugar_cleanup(true);
    }
}

$focus->retrieve($record);
if ($bean_name === 'Team') {
    $focus->remove_user_from_team($linked_id);
} else {

    // cut it off:
    $focus->load_relationship($linked_field);
    if ($focus->$linked_field->_relationship->relationship_name === 'quotes_contacts_shipto') {
        unset($focus->$linked_field->_relationship->relationship_role_column);
    }
    $focus->$linked_field->delete($record, $linked_id);
}

if ($bean_name === "Meeting") {
    $focus->retrieve($record);
    $user = BeanFactory::newBean('Users');
    $user->retrieve($linked_id);
}

if ($bean_name === "User" && $linked_field === 'eapm') {
    $eapm = BeanFactory::newBean('EAPM');
    $eapm->mark_deleted($linked_id);
}

if (!empty($_REQUEST['return_url'])) {
    $_REQUEST['return_url'] = urldecode($_REQUEST['return_url']);
}

$GLOBALS['log']->debug("deleted relationship: bean: $bean_name, linked_field: $linked_field, linked_id:$linked_id");

if (empty($_REQUEST['refresh_page'])) {
    handleRedirect();
}

exit;
