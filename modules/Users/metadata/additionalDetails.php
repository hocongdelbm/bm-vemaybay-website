<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
function additionalDetailsUser($fields)
{
    static $mod_strings;
    if (empty($mod_strings)) {
        global $current_language;
        $mod_strings = return_module_language($current_language, 'Users');
    }
        
    $overlib_string = '';
    if (!empty($fields['ID'])) {
        $overlib_string .= '<input type="hidden" value="'. $fields['ID'];
        $overlib_string .= '">';
    }

    $overlib_string .= '<h2><img src="index.php?entryPoint=getImage&themeName=' . SugarThemeRegistry::current()->name .'&imageName=Users.gif"/> '.$mod_strings['LBL_MODULE_NAME'].':</h2>';

    if (!empty($fields['NAME'])) {
        $overlib_string .= '<b>'. $mod_strings['LBL_NAME'] . '</b> ' . $fields['NAME'];
        $overlib_string .= '<br>';
    }

    if (!empty($fields['TITLE'])) {
        $overlib_string .= '<b>'. $mod_strings['LBL_TITLE'] . '</b> ' . $fields['TITLE'];
        $overlib_string .= '<br>';
    }

    if (!empty($fields['DEPARTMENT'])) {
        $overlib_string .= '<b>'. $mod_strings['LBL_DEPARTMENT'] . '</b> ' . $fields['DEPARTMENT'];
        $overlib_string .= '<br>';
    }

    if (!empty($fields['PHONE_HOME'])) {
        $overlib_string .= '<b>'. $mod_strings['LBL_HOME_PHONE'] . '</b> ' . $fields['PHONE_HOME'];
        $overlib_string .= '<br>';
    }

    if (!empty($fields['PHONE_MOBILE'])) {
        $overlib_string .= '<b>'. $mod_strings['LBL_MOBILE_PHONE'] . '</b> ' . $fields['PHONE_MOBILE'];
        $overlib_string .= '<br>';
    }
    if (!empty($fields['EMAIL1'])) {
        $overlib_string .= '<b>'. $mod_strings['LBL_EMAIL'] . '</b> ' . $fields['EMAIL1'];
        $overlib_string .= '<br>';
    }

    $editLink = "index.php?action=EditView&module=Users&record={$fields['ID']}";
    $viewLink = "index.php?action=DetailView&module=Users&record={$fields['ID']}";

    return array('fieldToAddTo' => 'NAME',
                 'string' => $overlib_string,
                 'editLink' => $editLink,
                 'viewLink' => $viewLink);
}
