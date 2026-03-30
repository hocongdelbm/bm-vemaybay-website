<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}



function smarty_modifier_json($value)
{
    return json_encode($value);
}
