<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/*
 * func: query_module_access
 * param: $moduleName
 *
 * returns 1 if user has access to a module, else returns 0
 *
 */

$modules_exempt_from_availability_check['Activities'] = 'Activities';
$modules_exempt_from_availability_check['History'] = 'History';
$modules_exempt_from_availability_check['Calls'] = 'Calls';
$modules_exempt_from_availability_check['Meetings'] = 'Meetings';
$modules_exempt_from_availability_check['Tasks'] = 'Tasks';
//$modules_exempt_from_availability_check['Notes']='Notes';

$modules_exempt_from_availability_check['EmailMarketing'] = 'EmailMarketing';
$modules_exempt_from_availability_check['EmailMan'] = 'EmailMan';
$modules_exempt_from_availability_check['Users'] = 'Users';
$modules_exempt_from_availability_check['Teams'] = 'Teams';
$modules_exempt_from_availability_check['SchedulersJobs'] = 'SchedulersJobs';
$modules_exempt_from_availability_check['DocumentRevisions'] = 'DocumentRevisions';
function query_module_access_list(&$user)
{
    require_once('modules/MySettings/TabController.php');
    $controller = new TabController();
    $tabArray = $controller->get_tabs($user);

    return $tabArray[0];
}

function query_user_has_roles($user_id)
{
    $role = BeanFactory::newBean('Roles');

    return $role->check_user_role_count($user_id);
}

function get_user_allowed_modules($user_id)
{
    $role = BeanFactory::newBean('Roles');

    $allowed = $role->query_user_allowed_modules($user_id);
    return $allowed;
}

function get_user_disallowed_modules($user_id, &$allowed)
{
    $role = BeanFactory::newBean('Roles');
    $disallowed = $role->query_user_disallowed_modules($user_id, $allowed);
    return $disallowed;
}
// grabs client ip address and returns its value
function query_client_ip()
{
    if (!empty($GLOBALS['sugar_config']['ip_variable']) && !empty($_SERVER[$GLOBALS['sugar_config']['ip_variable']])) {
        return $_SERVER[$GLOBALS['sugar_config']['ip_variable']];
    } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
        return $_SERVER['HTTP_X_FORWARDED'];
    } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_FORWARDED_FOR'];
    } elseif (isset($_SERVER['HTTP_FORWARDED'])) {
        return $_SERVER['HTTP_FORWARDED'];
    } elseif (isset($_SERVER['HTTP_FROM'])) {
        return $_SERVER['HTTP_FROM'];
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        return $_SERVER['REMOTE_ADDR'];
    } else {
        $GLOBALS['log']->warn('query_client_ip(): Unable to detect the IP address of the client.');
        return null;
    }
}

// sets value to key value
function get_val_array($arr)
{
    $new = array();
    if (!empty($arr)) {
        foreach ($arr as $key => $val) {
            $new[$key] = $key;
        }
    }
    return $new;
}
