<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
function smarty_function_sugar_connector_display($params, &$smarty)
{
    $bean = $params['bean'];
    $field = $params['field'];
    $type = $bean->field_name_map[$field]['type'];
    if($type == 'text') {
       echo strlen($bean->$field) > 50 ? substr($bean->$field, 0, 47) . '...' : $bean->field;
    } else if($type == 'link') {
       echo "<a href='{$bean->$field}' target='_blank'>{$bean->$field}</a>"; 
    } else {
       echo $bean->$field;
    }
}

?>
