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

$hook_array['before_save'][] = array(
    2,
    'Auto generate document name',
    'custom/modules/Documents/DocumentNameLogicHook.php',
    'DocumentNameLogicHook',
    'handleDocumentName'
);

$hook_array['after_save'] = array();
$hook_array['after_save'][] = array(
    1,
    'Upload document to NextCloud',
    'custom/modules/Documents/NextCloudUpload.php',
    'NextCloudUpload',
    'handleUpload'
);

$hook_array['before_delete'] = array();
$hook_array['before_delete'][] = array(
    1,
    'Move document to trash on NextCloud',
    'custom/modules/Documents/NextCloudUpload.php',
    'NextCloudUpload',
    'handleDelete'
);