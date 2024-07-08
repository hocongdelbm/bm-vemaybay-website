<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

ini_set('display_errors', 1);

global $beanList;
global $beanFiles;


if (empty($_REQUEST['module'])) {
    die("'module' was not defined");
}

if (!isset($beanList[$_REQUEST['module']])) {
    die("'".$_REQUEST['module']."' is not defined in \$beanList");
}

if (!isset($_REQUEST['subpanel'])) {
    sugar_die('Subpanel was not defined');
}

$subpanel   = $_REQUEST['subpanel'];
$record     = '';
$module     = $_REQUEST['module'];

$collection = array();

include('include/SubPanel/SubPanel.php');
$layout_def_key = '';
if (!empty($_REQUEST['layout_def_key'])) {
    $layout_def_key = $_REQUEST['layout_def_key'];
}

$subpanel_object = new SubPanel($module, $record, $subpanel, null, $layout_def_key, $collection);

echo $subpanel_object->getSearchForm();
