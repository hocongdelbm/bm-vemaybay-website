<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('modules/Contacts/ContactOpportunityRelationship.php');

$focus = new ContactOpportunityRelationship();

$focus->retrieve($_REQUEST['record']);

foreach ($focus->column_fields as $field) {
    safe_map($field, $focus, true);
}

foreach ($focus->additional_column_fields as $field) {
    safe_map($field, $focus, true);
}

// send them to the edit screen.
if (isset($_REQUEST['record']) && $_REQUEST['record'] != "") {
    $recordID = $_REQUEST['record'];
}

$focus->save();
$recordID = $focus->id;

$GLOBALS['log']->debug("Saved record with id of ".$recordID);

$header_URL = "Location: index.php?action={$_REQUEST['return_action']}&module={$_REQUEST['return_module']}&record={$_REQUEST['return_id']}";
$GLOBALS['log']->debug("about to post header URL of: $header_URL");

header($header_URL);
