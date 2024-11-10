<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

if (empty($fields_array['User'])) {
    include('modules/Users/field_arrays.php');
}
$fields_array['Employee']=$fields_array['User'];
