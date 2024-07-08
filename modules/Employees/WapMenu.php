<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
global $mod_strings;
global $current_user;

$module_menu=array();
if (is_admin($current_user)) {
    $module_menu = array(

    );
}
