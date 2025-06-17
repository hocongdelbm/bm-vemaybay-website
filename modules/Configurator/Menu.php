<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings;
$module_menu[]=array("index.php?module=Configurator&action=EditView",$mod_strings['LBL_CONFIGURE_SETTINGS_TITLE'], "Administration");
$module_menu[]=array("index.php?module=Configurator&action=LogView",$mod_strings['LBL_LOGVIEW'], "Leads");
