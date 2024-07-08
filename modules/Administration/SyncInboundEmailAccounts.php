<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}


// include the dependencies and related or used beans at least once to let IDE to see it
include_once 'modules/InboundEmail/InboundEmail.php';
include_once 'modules/Emails/Email.php';
include_once 'modules/Administration/SyncInboundEmailAccounts/SyncInboundEmailAccountsEmptyException.php';
include_once 'modules/Administration/SyncInboundEmailAccounts/SyncInboundEmailAccountsNoMethodException.php';
include_once 'modules/Administration/SyncInboundEmailAccounts/SyncInboundEmailAccountsInvalidMethodTypeException.php';
include_once 'modules/Administration/SyncInboundEmailAccounts/SyncInboundEmailAccountsInvalidSubActionArgumentsException.php';
include_once 'modules/Administration/SyncInboundEmailAccounts/SyncInboundEmailAccountsIMapConnectionException.php';
include_once 'modules/Administration/SyncInboundEmailAccounts/SyncInboundEmailAccountsException.php';
include_once 'modules/Administration/SyncInboundEmailAccounts/SyncInboundEmailAccountsSubActionHandler.php';
include_once 'modules/Administration/SyncInboundEmailAccounts/SyncInboundEmailAccountsPage.php';
include_once __DIR__ . '/../../include/Imap/ImapHandlerFactory.php';

new SyncInboundEmailAccountsPage(get_defined_vars());
