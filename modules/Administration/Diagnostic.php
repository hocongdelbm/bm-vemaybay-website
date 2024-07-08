<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}


global $mod_strings;
global $app_list_strings;
global $app_strings;
global $theme;

global $current_user;

if (!is_admin($current_user)) {
    sugar_die("Unauthorized access to administration.");
}
if (isset($GLOBALS['sugar_config']['hide_admin_diagnostics']) && $GLOBALS['sugar_config']['hide_admin_diagnostics']) {
    sugar_die("Unauthorized access to diagnostic tool.");
}

$db = DBManagerFactory::getInstance();
if (empty($db)) {
    $db = DBManagerFactory::getInstance();
}

echo getClassicModuleTitle(
    "Administration",
    array(
            "<a href='index.php?module=Administration&action=index'>{$mod_strings['LBL_MODULE_NAME']}</a>",
           translate('LBL_DIAGNOSTIC_TITLE')
           ),
    false
        );

global $currentModule;

$GLOBALS['log']->info("Administration Diagnostic");

$sugar_smarty = new Sugar_Smarty();
$sugar_smarty->assign("MOD", $mod_strings);
$sugar_smarty->assign("APP", $app_strings);

$sugar_smarty->assign("RETURN_MODULE", "Administration");
$sugar_smarty->assign("RETURN_ACTION", "index");
$sugar_smarty->assign("DB_NAME", $db->dbName);

$sugar_smarty->assign("MODULE", $currentModule);
$sugar_smarty->assign("PRINT_URL", "index.php?".$GLOBALS['request_string']);


$sugar_smarty->assign("ADVANCED_SEARCH_PNG", SugarThemeRegistry::current()->getImage('advanced_search', 'border="0"', null, null, '.gif', $app_strings['LNK_ADVANCED_FILTER']));
$sugar_smarty->assign("BASIC_SEARCH_PNG", SugarThemeRegistry::current()->getImage('basic_search', 'border="0"', null, null, '.gif', $app_strings['LNK_BASIC_FILTER']));

$sugar_smarty->display("modules/Administration/Diagnostic.tpl");
