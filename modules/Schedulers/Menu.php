<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/**

 * Description:
 */
global $mod_strings;
global $current_user;
//array("index.php?module=ProductTemplates&action=EditView&return_module=ProductTemplates&return_action=DetailView", $mod_strings['LNK_NEW_PRODUCT'],"Products"),
if (is_admin($current_user)) {
    $module_menu = array(
    array("index.php?module=Schedulers&action=EditView", $mod_strings['LNK_NEW_SCHEDULER'],"Add"),
    array("index.php?module=Schedulers&action=index", $mod_strings['LNK_LIST_SCHEDULER'],"List"),

);
}
