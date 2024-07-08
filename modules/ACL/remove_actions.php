<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
global $current_user,$beanList, $beanFiles;
$actionarr = ACLAction::getDefaultActions();
if (is_admin($current_user)) {
    $foundOne = false;
    foreach ($actionarr as $actionobj) {
        if (!isset($beanList[$actionobj->category]) || !file_exists($beanFiles[$beanList[$actionobj->category]])) {
            if (!isset($_REQUEST['upgradeWizard'])) {
                echo 'Removing for ' . $actionobj->category . '<br>';
            }
            $foundOne = true;
            ACLAction::removeActions($actionobj->category);
        }
    }
    if (!$foundOne) {
        echo 'No ACL modules found that needed to be removed';
    }
}
