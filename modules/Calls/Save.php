<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('modules/Calls/CallFormBase.php');
$formBase = new CallFormBase();
$formBase->handleSave('', true, false);
