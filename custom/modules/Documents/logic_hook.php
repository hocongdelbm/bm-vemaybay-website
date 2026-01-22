<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
$hook_array['before_save'] = Array(
    1,
    'Auto assign to creator',
    'custom/modules/Documents/AssignToCreator.php', //file path contain logic code
    'AssignToCreator', //class name
    'assign', //function name (method)
);
?>