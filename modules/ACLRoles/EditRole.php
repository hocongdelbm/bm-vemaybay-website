<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $app_list_strings;// $modInvisList

$sugar_smarty = new Sugar_Smarty();

$sugar_smarty->assign('MOD', $mod_strings);
$sugar_smarty->assign('APP', $app_strings);

//mass localization
/*foreach($modInvisList as $modinvisname){
    $app_list_strings['moduleList'][$modinvisname] = $modinvisname;
}*/

$sugar_smarty->assign('APP_LIST', $app_list_strings);

/*foreach($modInvisList as $modinvisname){
    unset($app_list_strings['moduleList'][$modinvisname]);
}*/

$role       = BeanFactory::newBean('ACLRoles');
$role_name  = '';
$return     = array('module'=>'ACLRoles', 'action'=>'index', 'record'=>'');

if (!empty($_REQUEST['record'])) {
    $role->retrieve($_REQUEST['record']);
    $categories     = $role->getRoleActions($_REQUEST['record']);
    $role_name      = $role->name;

    if (!empty($_REQUEST['isDuplicate'])) {
        //role id is stripped here in duplicate so anything using role id after this will not have it
        $role->id = '';
    } else {
        $return['record']= $role->id;
        $return['action']='DetailView';
    }
} else {
    $categories = $role->getRoleActions('');
}

$sugar_smarty->assign('ROLE', $role->toArray());
$tdwidth = 10;

if (isset($_REQUEST['return_module'])) {
    $return['module']=$_REQUEST['return_module'];
    if (isset($_REQUEST['return_action'])) {
        $return['action']=$_REQUEST['return_action'];
    }
    if (isset($_REQUEST['return_record'])) {
        $return['record']=$_REQUEST['return_record'];
    }
}

$sugar_smarty->assign('RETURN', $return);
$names = ACLAction::setupCategoriesMatrix($categories);

if (!empty($names)) {
    $tdwidth = 100 / count($names);
}

$sugar_smarty->assign('CATEGORIES', $categories);
$sugar_smarty->assign('CATEGORY_NAME', $_REQUEST['category_name']);
$sugar_smarty->assign('TDWIDTH', $tdwidth);
$sugar_smarty->assign('ACTION_NAMES', $names);
$actions = !empty($categories[$_REQUEST['category_name']]['module']) ? $categories[$_REQUEST['category_name']]['module'] : '' ;
$sugar_smarty->assign('ACTIONS', $actions);

ob_clean();

if ($_REQUEST['category_name'] == 'All') {
    echo $sugar_smarty->fetch('modules/ACLRoles/EditAllBody.tpl');
} else {
    //WDong Bug 23195: Strings not localized in Role Management.
    echo getClassicModuleTitle($_REQUEST['category_name'], array($app_list_strings['moduleList'][$_REQUEST['category_name']]), false);
    echo $sugar_smarty->fetch('modules/ACLRoles/EditRole.tpl');
    // echo '</form>';
}

sugar_cleanup(true);
