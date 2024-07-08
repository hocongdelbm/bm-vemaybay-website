<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
/**

 * Description:
 */

global $mod_strings;
$module_menu = array(
    array("index.php?module=Releases&action=EditView&return_module=Releases&return_action=DetailView", $mod_strings['LNK_NEW_RELEASE'],"Create"),
    );
