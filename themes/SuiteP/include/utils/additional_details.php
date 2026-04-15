<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

function additional_details($fields, SugarBean $bean, $params)
{
    global $current_language, $timedate, $app_list_strings;
    $mod_strings = return_module_language($current_language, $bean->module_name);

    // Create DB Date versions of each date field
    foreach ($fields as $i => $f) {
        if (empty($f)) {
            continue;
        }

        if (!isset($bean->field_name_map[strtolower($i)])) {
            continue;
        }

        if ($bean->field_name_map[strtolower($i)]['type'] == 'datetime' or $bean->field_name_map[strtolower($i)]['type'] == 'datetimecombo') {
            $db_date = $timedate->fromUser($f);
            $db_date_format = $db_date->format('Y-m-d H:i:s');
            $fields['DB_' . $i] = $db_date_format;
        }
    }

    // Load smarty templates
    $templateCaption = new Sugar_Smarty();
    $templateCaption->assign('APP', $app_list_strings);
    $templateCaption->assign('MOD', $mod_strings);
    $templateCaption->assign('FIELD', $fields);
    $templateCaption->assign('ACL_EDIT_VIEW', $bean->ACLAccess('EditView'));
    $templateCaption->assign('ACL_DETAIL_VIEW', $bean->ACLAccess('DetailView'));
    $templateCaption->assign('PARAM', $params);
    $templateCaption->assign('MODULE_NAME', $bean->module_name);
    $templateCaption->assign('OBJECT_NAME', $bean->object_name);
    $caption = $templateCaption->fetch('modules/' . $bean->module_name . '/tpls/additionalDetails.caption.tpl');

    $templateBody = new Sugar_Smarty();
    $templateBody->assign('APP', $app_list_strings);
    $templateBody->assign('MOD', $mod_strings);
    $templateBody->assign('FIELD', $fields);
    $templateBody->assign('ACL_EDIT_VIEW', $bean->ACLAccess('EditView'));
    $templateBody->assign('ACL_DETAIL_VIEW', $bean->ACLAccess('DetailView'));
    $templateBody->assign('PARAM', $params);
    $templateBody->assign('MODULE_NAME', $bean->module_name);
    $templateBody->assign('OBJECT_NAME', $bean->object_name);
    $body = $templateBody->fetch('modules/' . $bean->module_name . '/tpls/additionalDetails.body.tpl');

    $editLink = "index.php?action=EditView&module='. $bean->module_name .'&record={$fields['ID']}";
    $viewLink = "index.php?action=DetailView&module='. $bean->module_name .'&record={$fields['ID']}";

    return array(
        'fieldToAddTo' => 'NAME',
        'string' => $body,
        'editLink' => $editLink,
        'viewLink' => $viewLink,
        'caption' => $caption,
        'body' => $body,
        'version' => '7.7.6'
    );
}
