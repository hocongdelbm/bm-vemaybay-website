<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once "include/utils/additional_details.php";


function additionalDetailsCall($fields, SugarBean $bean = null, $params = array())
{
    return additional_details($fields, $bean, $params);
}
