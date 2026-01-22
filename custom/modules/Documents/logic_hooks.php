<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$hook_array = array();
$hook_array['before_save'] = array();
$hook_array['before_save'][] = array(
    1,
    'Auto assign to creator',
    'custom/modules/Documents/AssignToCreator.php',
    'AssignToCreator',
    'assign'
);
