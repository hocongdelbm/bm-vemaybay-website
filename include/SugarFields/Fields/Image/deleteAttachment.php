<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$field = $_REQUEST['field'];
$removeFile = "upload://{$_REQUEST[$field . '_record_id'] }_" . $field;
$bean = BeanFactory::getBean($_REQUEST['module'], $_REQUEST[$field . "_record_id"]);

if (!$bean->ACLAccess('save')){
    throw new RuntimeException('Not authorized');
}

if (file_exists($removeFile)) {
    if (!unlink($removeFile)) {
        $GLOBALS['log']->error("*** Could not unlink() file: [ {$removeFile} ]");
    } else {
        $bean->$field = '';
        $bean->save();
        echo "true";
    }
} else {
    $bean->$field = '';
    $bean->save();
    echo 'true';
}
