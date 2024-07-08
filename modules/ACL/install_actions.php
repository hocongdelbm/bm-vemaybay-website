<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $current_user, $beanList, $mod_strings;

$installed_classes = array();

if (is_admin($current_user)) {
    foreach ($beanList as $module => $class) {
        if (empty($installed_classes[$class]) && $class !== 'Tracker') {
            $bean = BeanFactory::newBean($module);
            if ($bean) {
                $GLOBALS['log']->debug("ACL Processing: $class");
                if (empty($bean->acl_display_only) && $bean->bean_implements('ACL')) {
                    if (!defined('SUGARCRM_IS_INSTALLING') && !isset($_REQUEST['upgradeWizard'])) {
                        echo translate('LBL_ADDING', 'ACL', '') . $bean->module_dir . '<br>';
                    }
                    if (!empty($bean->acltype)) {
                        ACLAction::addActions($bean->getACLCategory(), $bean->acltype);
                    } else {
                        ACLAction::addActions($bean->getACLCategory());
                    }

                    $installed_classes[$class] = true;
                }
            }
        }
    }
}
