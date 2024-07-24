<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('modules/Administration/Administration.php');
require_once('modules/SecurityGroups/SecurityGroup.php');

if (!empty($_REQUEST['remove_default_id'])) {
    SecurityGroup::removeDefaultGroup($_REQUEST['remove_default_id']);
} else {
    if (!empty($_REQUEST['default_group'])) {
        SecurityGroup::saveDefaultGroup($_REQUEST['default_group'], $_REQUEST['default_module']);
    }

    require_once('modules/Configurator/Configurator.php');
    $cfg = new Configurator();
    
    // save securitysuite_additive setting
    $cfg->config['securitysuite_additive'] = ($_REQUEST['securitysuite_additive'] == 1) ? true : false;
    // save securitysuite_strict_rights setting
    $cfg->config['securitysuite_strict_rights'] = ($_REQUEST['securitysuite_strict_rights'] == 1) ? true : false;
    // save securitysuite_filter_user_list setting
    $cfg->config['securitysuite_filter_user_list'] = ($_REQUEST['securitysuite_filter_user_list'] == 1) ? true : false;
    // save securitysuite_user_role_precedence setting
    $cfg->config['securitysuite_user_role_precedence'] = ($_REQUEST['securitysuite_user_role_precedence'] == 1) ? true : false;
    // save securitysuite_user_popup setting
    $cfg->config['securitysuite_user_popup'] = ($_REQUEST['securitysuite_user_popup'] == 1) ? true : false;
    // save securitysuite_popup_select setting
    $cfg->config['securitysuite_popup_select'] = ($_REQUEST['securitysuite_popup_select'] == 1) ? true : false;
    // save securitysuite_inherit_creator setting
    $cfg->config['securitysuite_inherit_creator'] = ($_REQUEST['securitysuite_inherit_creator'] == 1) ? true : false;
    // save securitysuite_inherit_parent setting
    $cfg->config['securitysuite_inherit_parent'] = ($_REQUEST['securitysuite_inherit_parent'] == 1) ? true : false;
    // save securitysuite_inherit_assigned setting
    $cfg->config['securitysuite_inherit_assigned'] = ($_REQUEST['securitysuite_inherit_assigned'] == 1) ? true : false;
    // save securitysuite_inbound_email setting
    $cfg->config['securitysuite_inbound_email'] = ($_REQUEST['securitysuite_inbound_email'] == 1) ? true : false;

    if (!isset($cfg->config['addAjaxBannedModules'])) {
        $cfg->config['addAjaxBannedModules'] = array();
    }
    if (!in_array('SecurityGroups', $cfg->config['addAjaxBannedModules'])) {
        $cfg->config['addAjaxBannedModules'][] = 'SecurityGroups';
    }

    $cfg->handleOverride();
}

header("Location: index.php?action={$_POST['return_action']}&module={$_POST['return_module']}");
