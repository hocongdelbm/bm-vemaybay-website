<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once __DIR__ . '/../include/EntryPointConfirmOptInHandler.php';
new EntryPointConfirmOptInHandler();
