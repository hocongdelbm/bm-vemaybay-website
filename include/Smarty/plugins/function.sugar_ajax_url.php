<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}




function smarty_function_sugar_ajax_url($params, &$smarty)
{
    if (empty($params['url'])) {
        $smarty->trigger_error("ajax_url: missing required param (module)");
        return "";
    }
    return ajaxLink($params['url']);
}
