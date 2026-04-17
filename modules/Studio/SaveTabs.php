<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
require_once('modules/Studio/TabGroups/TabGroupHelper.php');

// TabGroupHelper::saveTabGroups($_POST);
$TabGroup = new TabGroupHelper();
$newTabGroup = $TabGroup->saveTabGroups($_POST);

ob_clean();
if (!empty($_POST['grouptab_lang'])) {
    header('Location: index.php?module=Studio&action=TabGroups&lang='.$_POST['grouptab_lang']);
} else {
    header('Location: index.php?module=Studio&action=TabGroups');
}
