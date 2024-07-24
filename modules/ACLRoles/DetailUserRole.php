<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $app_list_strings, $app_strings, $current_user;

$mod_strings = return_module_language($GLOBALS['current_language'], 'Users');

$focus = BeanFactory::newBean('Users');

$focus->retrieve($_REQUEST['record']);
// if (!is_admin($focus)) {
    $sugar_smarty = new Sugar_Smarty();
    $sugar_smarty->assign('MOD', $mod_strings);
    $sugar_smarty->assign('APP', $app_strings);
    $sugar_smarty->assign('APP_LIST', $app_list_strings);
    
    $categories = ACLAction::getUserActions($_REQUEST['record'], true);
    
    //clear out any removed tabs from user display
    if (!$GLOBALS['current_user']->isAdminForModule('Users')) {
        $tabs = $focus->getPreference('display_tabs');
        global $modInvisList;
        if (!empty($tabs)) {
            foreach ($categories as $key=>$value) {
                if (!in_array($key, $tabs) &&  !in_array($key, $modInvisList)) {
                    unset($categories[$key]);
                }
            }
        }
    }
    
    $names = array();
    $names = ACLAction::setupCategoriesMatrix($categories);
    if (!empty($names)) {
        $tdwidth = 100 / count($names);
    }
    $sugar_smarty->assign('APP', $app_list_strings);
    $sugar_smarty->assign('CATEGORIES', $categories);
    $sugar_smarty->assign('TDWIDTH', $tdwidth);
    $sugar_smarty->assign('ACTION_NAMES', $names);
    
    $title = getClassicModuleTitle('', array($mod_strings['LBL_MODULE_NAME'],$mod_strings['LBL_ROLES_SUBPANEL_TITLE']), '');
    
    $sugar_smarty->assign('TITLE', $title);
    $sugar_smarty->assign('USER_ID', $focus->id);
    $sugar_smarty->assign('LAYOUT_DEF_KEY', 'UserRoles');
    echo $sugar_smarty->fetch('modules/ACLRoles/DetailViewUser.tpl');
    
    //this gets its layout_defs.php file from the user not from ACLRoles so look in modules/Users for the layout defs
    require_once('include/SubPanel/SubPanelTiles.php');
    $modules_exempt_from_availability_check=array('Users'=>'Users','ACLRoles'=>'ACLRoles',);
    $subpanel = new SubPanelTiles($focus, 'UserRoles');
    
    echo $subpanel->display(true, true);
// }
