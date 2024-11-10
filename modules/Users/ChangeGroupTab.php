<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $app_strings;
global $app_list_strings;
global $mod_strings;

$current_user->setPreference('theme_current_group', $_REQUEST['newGroup']);
$current_user->incrementETag("mainMenuETag");
