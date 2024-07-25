<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

include_once __DIR__ . '/../../include/utils/BaseHandler.php';
include_once __DIR__ . '/GoogleCalendarSettingsHandler.php';

global $current_user;
global $mod_strings;
global $app_strings;

$tplPath = __DIR__ . '/GoogleCalendarSettings.tpl';
$request = $_REQUEST;

new GoogleCalendarSettingsHandler(
    $tplPath,
    $current_user,
    $request,
    $mod_strings,
    new Configurator(),
    new Sugar_Smarty(),
    new javascript()
);
