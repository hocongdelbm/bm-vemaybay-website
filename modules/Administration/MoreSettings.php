<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $current_user, $app_strings, $sugar_config;
if (!is_admin($current_user)) {
    sugar_die($app_strings['ERR_NOT_ADMIN']);
}

echo getClassicModuleTitle(
    "Administration",
    array(
        "<a href='index.php?module=Administration&action=index'>" . translate('LBL_MODULE_NAME', 'Administration') . "</a>",
        $mod_strings['LBL_MANAGE_CONFIG_TITLE'],
    ),
    false
);

require_once('modules/Configurator/Configurator.php');
$configurator   = new Configurator();
$errors         = array();
$focus          = BeanFactory::newBean('Administration');

if (isset($_REQUEST['saveConfig']) && !empty($_REQUEST['saveConfig'])) {
    // Misa
    $configurator->config['misa']['app_id'] = trim($_POST['misa_app_id']);
    $configurator->config['misa']['branch_id'] = trim($_POST['misa_branch_id']);
    $configurator->config['misa']['base_url'] = trim($_POST['misa_base_url']);
    $configurator->config['misa']['company_code'] = trim($_POST['misa_company_code']);
    $configurator->config['misa']['access_code'] = trim($_POST['misa_access_code']);

    // PBX
    $configurator->config['webrtc']['ip'] = trim($_POST['pbx_ip']);
    $configurator->config['webrtc']['ip_old'] = trim($_POST['pbx_ip_old']);
    $configurator->config['webrtc']['domain_name'] = trim($_POST['pbx_domain_name']);
    $configurator->config['webrtc']['port'] = trim($_POST['pbx_port']);
    $configurator->config['webrtc']['tokenapi'] = trim($_POST['pbx_tokenapi']);

    // Vnbackup
    $configurator->config['vnbackup']['url_upload'] = trim($_POST['vnbackup_url_upload']);
    $configurator->config['vnbackup']['url_share'] = trim($_POST['vnbackup_url_share']);
    $configurator->config['vnbackup']['username'] = trim($_POST['vnbackup_username']);
    $configurator->config['vnbackup']['password'] = trim($_POST['vnbackup_password']);

    $configurator->saveConfig();
    $focus->saveConfig();

    header('Location: index.php?module=Administration&action=MoreSettings');
}
$focus->retrieveSettings();

$sugar_smarty   = new Sugar_Smarty();
$sugar_smarty->assign('MOD', $mod_strings);
$sugar_smarty->assign('APP', $app_strings);
$sugar_smarty->assign('config', $configurator->config);
$sugar_smarty->display('modules/Administration/templates/MoreSettings.tpl');
