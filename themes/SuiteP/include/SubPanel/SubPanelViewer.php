<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $beanList;
global $beanFiles;

if (empty($_REQUEST['module'])) {
    die("'module' was not defined");
}

if (empty($_REQUEST['record'])) {
    die("'record' was not defined");
}

if (!isset($beanList[$_REQUEST['module']])) {
    die("'" . $_REQUEST['module'] . "' is not defined in \$beanList");
}

if (!isset($_REQUEST['subpanel'])) {
    sugar_die('Subpanel was not defined');
}

$subpanel = $_REQUEST['subpanel'];
$record = $_REQUEST['record'];
$module = $_REQUEST['module'];

$collection = array();

if (isset($_REQUEST['collection_basic']) && $_REQUEST['collection_basic'][0] !== 'null') {
    $_REQUEST['collection_basic'] = explode(',', $_REQUEST['collection_basic'][0]);
    $collection = $_REQUEST['collection_basic'];
}

if (empty($_REQUEST['inline'])) {
    insert_popup_header();
}

include 'include/SubPanel/SubPanel.php';
$layout_def_key = '';
if (!empty($_REQUEST['layout_def_key'])) {
    $layout_def_key = $_REQUEST['layout_def_key'];
}
require_once 'include/SubPanel/SubPanelDefinitions.php';
// retrieve the definitions for all the available subpanels for this module from the subpanel
$bean = BeanFactory::getBean($module);
$spd = new SubPanelDefinitions($bean);
$aSubPanelObject = $spd->load_subpanel($subpanel, false, false, '', $collection);

$subpanel_object = new SubPanel($module, $record, $subpanel, $aSubPanelObject, $layout_def_key, $collection);
$subpanel_object->setTemplateFile('include/SubPanel/tpls/SubPanelDynamic.tpl');

echo empty($_REQUEST['inline']) ? $subpanel_object->get_buttons() : '';

$countOnly = isset($_REQUEST['countOnly']) && $_REQUEST['countOnly'];
$subpanel_object->display($countOnly);

if (!$countOnly) {
    $jsAlerts = new jsAlerts();
    if (!isset($_SESSION['isMobile'])) {
        echo $jsAlerts->getScript();
    }

    if (empty($_REQUEST['inline'])) {
        insert_popup_footer();
    }
}
