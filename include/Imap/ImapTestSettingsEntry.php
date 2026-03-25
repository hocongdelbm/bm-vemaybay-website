<?php


if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

include_once __DIR__ . '/ImapTestSettingsEntryHandler.php';

global $sugar_config;

$handler = new ImapTestSettingsEntryHandler();
$output = $handler->handleEntryPointRequest($sugar_config, $_REQUEST);

echo $output;
exit;
