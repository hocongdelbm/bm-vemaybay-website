<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
global $current_user;

$sugar_smarty = new Sugar_Smarty();
$sugar_smarty->assign('USER_NAME', $current_user->name);

if (file_exists('modules/Home/tpls/index.tpl')) {
    echo $sugar_smarty->fetch('modules/Home/tpls/index.tpl');
}
