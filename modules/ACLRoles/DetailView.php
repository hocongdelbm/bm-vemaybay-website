<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$sugar_smarty = new Sugar_Smarty();
$sugar_smarty->assign('MOD', $mod_strings);
$sugar_smarty->assign('APP', $app_strings);
$sugar_smarty->assign('APP_LIST', $app_list_strings);

$role = BeanFactory::newBean('ACLRoles');
$categories = $role->getRoleActions($_REQUEST['record']);
$role->retrieve($_REQUEST['record']);
$names = ACLAction::setupCategoriesMatrix($categories);
if (!empty($names)) {
    $tdWidth = 100 / count($names);
}
$sugar_smarty->assign('ROLE', $role->toArray());
$sugar_smarty->assign('CATEGORIES', $categories);
$sugar_smarty->assign('TDWIDTH', $tdWidth);
$sugar_smarty->assign('ACTION_NAMES', $names);

$return     = ['module' => 'ACLRoles', 'action' => 'DetailView', 'record' => $role->id];
$sugar_smarty->assign('RETURN', $return);
$params     = [];
$params[]   = "<a href='index.php?module=ACLRoles&action=index'>{$mod_strings['LBL_MODULE_NAME']}</a>";
$params[]   = $role->get_summary_text();
echo getClassicModuleTitle("ACLRoles", $params, true);
$hide_hide_supanels = true;

echo $sugar_smarty->fetch('modules/ACLRoles/DetailView.tpl');
// For subpanels the variable must be named focus;
$focus =& $role;
$_REQUEST['module'] = 'ACLRoles';
require_once __DIR__ . '/../../include/SubPanel/SubPanelTiles.php';

$subPanel = new SubPanelTiles($role, 'ACLRoles');

echo $subPanel->display();
