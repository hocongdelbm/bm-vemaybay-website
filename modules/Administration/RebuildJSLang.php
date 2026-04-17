<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

if (is_admin($current_user)) {
    global $mod_strings, $sugar_config;
    echo $mod_strings['LBL_REBUILD_JAVASCRIPT_LANG_DESC'];

    //remove the js language files
    LanguageManager::removeJSLanguageFiles();

    //remove language cache files
    LanguageManager::clearLanguageCache();
} else {
    sugar_die($GLOBALS['app_strings']['ERR_NOT_ADMIN']);
}
