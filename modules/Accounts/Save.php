<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('modules/Accounts/AccountFormBase.php');
$accountForm = new AccountFormBase();
$prefix = empty($_REQUEST['dup_checked']) ? '' : 'Accounts';
$accountForm->handleSave($prefix, true, false);
