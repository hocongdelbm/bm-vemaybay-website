<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}


global $current_user;
if (is_admin($current_user)) {
    if (!isset($_REQUEST['process'])) {
        global $mod_strings;
        echo '<br>'.$mod_strings['LBL_REPAIR_JS_FILES_PROCESSING'];
        echo'<div id="msgDiv"></div>';
        $ss = new Sugar_Smarty();
        $ss->display('modules/Administration/templates/RebuildSprites.tpl');
    }
}
