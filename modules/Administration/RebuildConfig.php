<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings;

// the initial settings for the template variables to fill
$config_check           = '';
$config_file_ready      = false;
$lbl_rebuild_config     = $mod_strings['LBL_REBUILD_CONFIG'];
$btn_rebuild_config     = $mod_strings['BTN_REBUILD_CONFIG'];
$disable_config_rebuild = 'disabled="disabled"';

// check the status of the config file
if (is_writable('config.php')) {
    $config_check = $mod_strings['MSG_CONFIG_FILE_READY_FOR_REBUILD'];
    $disable_config_rebuild = '';
    $config_file_ready = true;
} else {
    $config_check = $mod_strings['MSG_MAKE_CONFIG_FILE_WRITABLE'];
}

// only do the rebuild if config file checks out and user has posted back
if (!empty($_POST['perform_rebuild']) && $config_file_ready) {

    // retrieve configuration from file so that contents of config_override.php
    // is not merged (bug #54403)
    $clean_config = loadCleanConfig();
    if (rebuildConfigFile($clean_config, $sugar_version)) {
        $config_check = $mod_strings['MSG_CONFIG_FILE_REBUILD_SUCCESS'];
        $disable_config_rebuild = 'disabled="disabled"';
    } else {
        $config_check = $mod_strings['MSG_CONFIG_FILE_REBUILD_FAILED'];
    }
}

/////////////////////////////////////////////////////////////////////
// TEMPLATE ASSIGNING
$smarty = new Sugar_Smarty();
$smarty->assign('LBL_CONFIG_CHECK', $mod_strings['LBL_CONFIG_CHECK']);
$smarty->assign('CONFIG_CHECK', $config_check);
$smarty->assign('LBL_PERFORM_REBUILD', $lbl_rebuild_config);
$smarty->assign('DISABLE_CONFIG_REBUILD', $disable_config_rebuild);
$smarty->assign('BTN_PERFORM_REBUILD', $btn_rebuild_config);
$smarty->display('modules/Administration/templates/RebuildConfig.tpl');
