<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $currentModule;
global $current_language;
global $current_user;
global $sugar_flavor;


if (!is_admin($current_user) && !is_admin_for_any_module($current_user)) {
    sugar_die("Unauthorized access to administration.");
}

echo getClassicModuleTitle(
    translate('LBL_MODULE_NAME', 'Administration'),
    array(translate('LBL_MODULE_NAME', 'Administration')),
    false
);

$access = get_admin_modules_for_user($current_user);

//get the module links..
require('modules/Administration/metadata/adminpaneldefs.php');
global $admin_group_header;  ///variable defined in the file above.


$tab = array();
$icons = array();
$url = array();
$label_tab = array();
$id_tab = array();
$description = array();
$group = array();
$sugar_smarty = new Sugar_Smarty();
$values_3_tab = array();
$admin_group_header_tab = array();
$j=0;

foreach ($admin_group_header as $key=>$values) {
    $module_index = array_keys($values[3]);
    $addedHeaderGroups = array();
    foreach ($module_index as $mod_key=>$mod_val) {
        if (
        (!isset($addedHeaderGroups[$values[0]]))) {
            $admin_group_header_tab[]=$values;
            $group_header_value=get_form_header(translate($values[0], 'Administration'), $values[1], $values[2]);
            $group[$j][0] = '<h3>' . translate($values[0]) . '</h3>';
            $addedHeaderGroups[$values[0]] = 1;
            if (isset($values[4])) {
                $group[$j][1] = '' . translate($values[4]) . '';
            } else {
                $group[$j][2] = '';
            }
            $colnum=0;
            $i=0;
            $fix = array_keys($values[3]);
            if (count($values[3])>1) {

                //////////////////
                $tmp_array = $values[3];
                $return_array = array();
                foreach ($tmp_array as $mod => $value) {
                    $keys = array_keys($value);
                    foreach ($keys as $key) {
                        $return_array[$key] = $value[$key];
                    }
                }
                $values_3_tab[]= $return_array;
                $mod = $return_array;
            } else {
                $mod = $values[3][$fix[0]];
                $values_3_tab[]= $mod;
            }

            foreach ($mod as $link_idx =>$admin_option) {
                if (!empty($GLOBALS['admin_access_control_links']) && in_array($link_idx, $GLOBALS['admin_access_control_links'])) {
                    unset($values_3_tab[$j][$link_idx]);
                    continue;
                }
                $colnum+=1;
                $icons[$j][$i] = isset($admin_option[4]) ? $admin_option[4] : 'default';
                $url[$j][$i] = $admin_option[3];
                $label = translate($admin_option[1], 'Administration');
                if (!empty($admin_option['additional_label'])) {
                    $label.= ' '. $admin_option['additional_label'];
                }

                $label_tab[$j][$i]= $label;
                $id_tab[$j][$i] = $link_idx;
                
                $description[$j][$i]= translate($admin_option[2], 'Administration');

                if (($colnum % 2) == 0) {
                    $tab[$j][$i]= ($colnum % 2);
                } else {
                    $tab[$j][$i]= 10;
                }
                $i+=1;
            }

            //if the loop above ends with an odd entry add a blank column.
            if (($colnum % 2) != 0) {
                $tab[$j][$i]= 10;
            }
            $j+=1;
        }
    }
}

$sugar_smarty->assign("VALUES_3_TAB", $values_3_tab);
$sugar_smarty->assign("ADMIN_GROUP_HEADER", $admin_group_header_tab);
$sugar_smarty->assign("GROUP_HEADER", $group);
$sugar_smarty->assign("ICONS", $icons);
$sugar_smarty->assign("ITEM_URL", $url);
$sugar_smarty->assign("ITEM_HEADER_LABEL", $label_tab);
$sugar_smarty->assign("ITEM_DESCRIPTION", $description);
$sugar_smarty->assign("COLNUM", $tab);
$sugar_smarty->assign('ID_TAB', $id_tab);

echo $sugar_smarty->fetch('modules/Administration/index.tpl');
