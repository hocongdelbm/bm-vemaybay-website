<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings;
$module_menu = array();
$module_menu[]= array("index.php?module=InboundEmail&action=EditView&is_personal=1&type=personal", $mod_strings['LNK_LIST_CREATE_NEW_PERSONAL'],"Create");

// if (is_admin($GLOBALS['current_user'])) {
//     $module_menu[]= array("index.php?module=InboundEmail&action=EditView&type=group", $mod_strings['LNK_LIST_CREATE_NEW_GROUP'],"Create");
//     $module_menu[]= array("index.php?module=InboundEmail&action=EditView&mailbox_type=bounce&type=bounce", $mod_strings['LNK_LIST_CREATE_NEW_BOUNCE'],"Create");
// }
$module_menu[]= array("index.php?module=InboundEmail&action=index", $mod_strings['LNK_LIST_MAILBOXES'],"List");

$module_menu[]= array("index.php?module=Emails&action=index", $mod_strings['LNK_LIST_MAIL'],"List");
// $module_menu[]= array("index.php?module=OutboundEmailAccounts&action=index", $mod_strings['LNK_LIST_OUTBOUND_EMAILS'],"List");
// $module_menu[]= array("index.php?module=ExternalOAuthConnection&action=index", $mod_strings['LNK_EXTERNAL_OAUTH_CONNECTIONS'],"List");


// if (is_admin($GLOBALS['current_user'])) {
//     $module_menu[]= array("index.php?module=Schedulers&action=index", $mod_strings['LNK_LIST_SCHEDULER'],"Schedulers");
// }
//array("index.php?module=Queues&action=Seed", $mod_strings['LNK_SEED_QUEUES'],"CustomQueries"),
