<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/* BEGIN - SECURITY GROUPS */
if (file_exists("modules/ACLActions/actiondefs.override.php")) {
    require_once("modules/ACLActions/actiondefs.override.php");
} else {
    require_once('modules/ACLActions/actiondefs.php');
}
/* END - SECURITY GROUPS */
require_once('modules/ACL/ACLJSController.php');

class ACLController
{
    /* BEGIN - SECURITY GROUPS - added $in_group */


    /**
     * Checks that the user should be allowed to access a category
     *
     * @param string $category
     * @param string $action
     * @param bool $is_owner
     * @param string $type
     * @param bool $in_group
     * @return bool
     */
    public static function checkAccess($category, $action, $is_owner = false, $type = 'module', $in_group = false)
    {
        global $current_user;
        if (is_admin($current_user)) {
            return true;
        }

        // Line items checks for parent modules to determine ACL
        if ($category === AOS_Products_Quotes::class) {
            return (
                ACLAction::userHasAccess($current_user->id, AOS_Quotes::class, $action, $type, $is_owner, $in_group)
                || ACLAction::userHasAccess($current_user->id, AOS_Invoices::class, $action, $type, $is_owner, $in_group)
                || ACLAction::userHasAccess($current_user->id, AOS_Contracts::class, $action, $type, $is_owner, $in_group)
            );
        }

        //calendar is a special case since it has 3 modules in it (calls, meetings, tasks)
        if ($category === 'Calendar') {
            return ACLAction::userHasAccess(
                $current_user->id,
                'Calls',
                $action,
                $type,
                $is_owner,
                $in_group
                ) || ACLAction::userHasAccess(
                    $current_user->id,
                    'Meetings',
                    $action,
                    'module',
                    $is_owner,
                    $in_group
                ) || ACLAction::userHasAccess(
                    $current_user->id,
                    'Tasks',
                    $action,
                    'module',
                    $is_owner,
                    $in_group
                );
        }
        if ($category === 'Activities') {
            return ACLAction::userHasAccess(
                $current_user->id,
                'Calls',
                $action,
                $type,
                $is_owner,
                $in_group
                ) || ACLAction::userHasAccess(
                    $current_user->id,
                    'Meetings',
                    $action,
                    'module',
                    $is_owner,
                    $in_group
                ) || ACLAction::userHasAccess(
                    $current_user->id,
                    'Tasks',
                    $action,
                    'module',
                    $is_owner,
                    $in_group
                ) || ACLAction::userHasAccess(
                    $current_user->id,
                    'Emails',
                    $action,
                    'module',
                    $is_owner,
                    $in_group
                ) || ACLAction::userHasAccess(
                    $current_user->id,
                    'Notes',
                    $action,
                    'module',
                    $is_owner,
                    $in_group
                );
        }

        return ACLAction::userHasAccess($current_user->id, $category, $action, $type, $is_owner, $in_group);
    }

    /* END - SECURITY GROUPS */

    /**
     * Determines if user requires ownership
     *
     * @param string $category
     * @param Bool $value
     * @param string $type
     * @return bool
     */
    public static function requireOwner($category, $value, $type = 'module')
    {
        global $current_user;
        if (is_admin($current_user)) {
            return false;
        }

        return ACLAction::userNeedsOwnership($current_user->id, $category, $value, $type);
    }

    /* BEGIN - SECURITY GROUPS */

    /**
     * Determines if user requires a security group
     *
     * @param string $category
     * @param Bool $value
     * @param string $type
     * @return bool
     */
    public static function requireSecurityGroup($category, $value, $type = 'module')
    {
        global $current_user;
        if (is_admin($current_user)) {
            return false;
        }

        return ACLAction::userNeedsSecurityGroup($current_user->id, $category, $value, $type);
    }

    /* END - SECURITY GROUPS */

    /**
     * Filters module list
     *
     * @param array $moduleList
     * @param Bool $by_value
     */
    public static function filterModuleList(&$moduleList, $by_value = true)
    {
        global $aclModuleList, $current_user;
        if (is_admin($current_user)) {
            return;
        }
        $actions = ACLAction::getUserActions($current_user->id, false);

        $compList = array();
        if ($by_value) {
            foreach ($moduleList as $key => $value) {
                $compList[$value] = $key;
            }
        } else {
            $compList =& $moduleList;
        }
        foreach ($actions as $action_name => $action) {
            if (!empty($action['module'])) {
                $aclModuleList[$action_name] = $action_name;
                if (isset($compList[$action_name])) {
                    if ($action['module']['access']['aclaccess'] < ACL_ALLOW_ENABLED) {
                        if ($by_value) {
                            unset($moduleList[$compList[$action_name]]);
                        } else {
                            unset($moduleList[$action_name]);
                        }
                    }
                }
            }
        }
        if (isset($compList['Calendar']) &&
            !(ACLController::checkModuleAllowed('Calls', $actions)
                || ACLController::checkModuleAllowed(
                    'Meetings',
                    $actions
                ) || ACLController::checkModuleAllowed('Tasks', $actions))
        ) {
            if ($by_value) {
                unset($moduleList[$compList['Calendar']]);
            } else {
                unset($moduleList['Calendar']);
            }
            if (isset($compList['Activities']) && !ACLController::checkModuleAllowed('Notes', $actions)) {
                if ($by_value) {
                    unset($moduleList[$compList['Activities']]);
                } else {
                    unset($moduleList['Activities']);
                }
            }
        }
    }

    /**
     * Check to see if the module is available for this user.
     *
     * @param String $module_name
     * @return true if they are allowed.  false otherwise.
     */
    public static function checkModuleAllowed($module_name, $actions)
    {
        if (!empty($actions[$module_name]['module']['access']['aclaccess']) &&
            ACL_ALLOW_ENABLED == $actions[$module_name]['module']['access']['aclaccess']
        ) {
            return true;
        }

        return false;
    }

    /**
     * Checks if module is disabled
     *
     * @param array $moduleList
     * @param Bool $by_value
     * @param String $view
     * @return array
     */
    public static function disabledModuleList($moduleList, $by_value = true, $view = 'list')
    {
        global $aclModuleList, $current_user;
        if (is_admin($GLOBALS['current_user'])) {
            return array();
        }
        $actions = ACLAction::getUserActions($current_user->id, false);
        $disabled = array();
        $compList = array();

        if ($by_value) {
            foreach ($moduleList as $key => $value) {
                $compList[$value] = $key;
            }
        } else {
            $compList =& $moduleList;
        }
        if (isset($moduleList['ProductTemplates'])) {
            $moduleList['Products'] = 'Products';
        }

        foreach ($actions as $action_name => $action) {
            if (!empty($action['module'])) {
                $aclModuleList[$action_name] = $action_name;
                if (isset($compList[$action_name])) {
                    if ($action['module']['access']['aclaccess'] < ACL_ALLOW_ENABLED
                        || $action['module'][$view]['aclaccess'] < 0
                    ) {
                        if ($by_value) {
                            $disabled[$compList[$action_name]] = $compList[$action_name];
                        } else {
                            $disabled[$action_name] = $action_name;
                        }
                    }
                }
            }
        }
        if (isset($compList['Calendar']) && !(ACL_ALLOW_ENABLED == $actions['Calls']['module']['access']['aclaccess']
                || ACL_ALLOW_ENABLED == $actions['Meetings']['module']['access']['aclaccess']
                || ACL_ALLOW_ENABLED == $actions['Tasks']['module']['access']['aclaccess'])
        ) {
            if ($by_value) {
                $disabled[$compList['Calendar']] = $compList['Calendar'];
            } else {
                $disabled['Calendar'] = 'Calendar';
            }
            if (isset($compList['Activities']) &&
                !(ACL_ALLOW_ENABLED == $actions['Notes']['module']['access']['aclaccess']
                    || ACL_ALLOW_ENABLED == $actions['Notes']['module']['access']['aclaccess'])
            ) {
                if ($by_value) {
                    $disabled[$compList['Activities']] = $compList['Activities'];
                } else {
                    $disabled['Activities'] = 'Activities';
                }
            }
        }
        if (isset($disabled['Products'])) {
            $disabled['ProductTemplates'] = 'ProductTemplates';
        }


        return $disabled;
    }


    public function addJavascript($category, $form_name = '', $is_owner = false)
    {
        $jscontroller = new ACLJSController($category, $form_name, $is_owner);
        echo $jscontroller->getJavascript();
    }

    /**
     * Returns true if module support ACL
     *
     * @param String $module
     * @return Bool
     */
    public static function moduleSupportsACL($module)
    {
        static $checkModules = array();
        global $beanFiles, $beanList;
        if (isset($checkModules[$module])) {
            return $checkModules[$module];
        }
        if (!isset($beanList[$module])) {
            $checkModules[$module] = false;
        } else {
            $class = $beanList[$module];
            require_once($beanFiles[$class]);
            $mod = new $class();
            if (!is_subclass_of($mod, 'SugarBean')) {
                $checkModules[$module] = false;
            } else {
                $checkModules[$module] = $mod->bean_implements('ACL');
            }
        }

        return $checkModules[$module];
    }


    /**
     * Redirect the user to home.
     *
     * @param Bool $redirect_home
     */
    public static function displayNoAccess($redirect_home = false)
    {
        echo '<script>function set_focus(){}</script><p class="error">' . translate('LBL_NO_ACCESS', 'ACL') . '</p>';
        if ($redirect_home) {
            echo translate(
                'LBL_REDIRECT_TO_HOME',
                'ACL'
                ) . ' <span id="seconds_left">3</span> ' . translate(
                    'LBL_SECONDS',
                    'ACL'
                ) . '<script> function redirect_countdown(left){document.getElementById("seconds_left").innerHTML = left; if(left == 0){document.location.href = "index.php";}else{left--; setTimeout("redirect_countdown("+ left+")", 1000)}};setTimeout("redirect_countdown(3)", 1000)</script>';
        }
    }
}
