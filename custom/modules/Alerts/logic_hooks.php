<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$hook_array = array();

$hook_array['after_save'] = array();
$hook_array['after_save'][] = array(
    1,
    'Upload document to NextCloud',
    'custom/modules/Alerts/NextCloudUploadAlert.php',
    'NextCloudUploadAlert',
    'handleUpload'
);

$hook_array['before_delete'] = array();
$hook_array['before_delete'][] = array(
    1,
    'Move document to trash on NextCloud',
    'custom/modules/Alerts/NextCloudUploadAlert.php',
    'NextCloudUploadAlert',
    'handleDelete'
);