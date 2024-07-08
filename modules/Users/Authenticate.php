<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

if (!defined('SUITE_PHPUNIT_RUNNER')) {
    session_regenerate_id(false);
}
global $mod_strings;
$login_vars = $GLOBALS['app']->getLoginVars(false);

$user_name = isset($_REQUEST['user_name'])
    ? $_REQUEST['user_name'] : '';

$password = isset($_REQUEST['username_password'])
    ? $_REQUEST['username_password'] : '';

$authController->login($user_name, $password);
// authController will set the authenticated_user_id session variable
if (isset($_SESSION['authenticated_user_id'])) {
    // Login is successful
    if ($_SESSION['hasExpiredPassword'] == '1' && $_REQUEST['action'] != 'Save') {
        $GLOBALS['module'] = 'Users';
        $GLOBALS['action'] = 'ChangePassword';
        ob_clean();
        header("Location: index.php?module=Users&action=ChangePassword");
        sugar_cleanup(true);
    }
    global $record;
    global $current_user;
    global $sugar_config;

    global $current_user;

    if (isset($current_user)  && empty($login_vars)) {
        if (!empty($GLOBALS['sugar_config']['default_module']) && !empty($GLOBALS['sugar_config']['default_action'])) {
            $url = "index.php?module={$GLOBALS['sugar_config']['default_module']}&action={$GLOBALS['sugar_config']['default_action']}";
        } else {
            $modListHeader = query_module_access_list($current_user);
            //try to get the user's tabs
            $tempList = $modListHeader;
            $idx = array_shift($tempList);
            if (!empty($modListHeader[$idx])) {
                $url = "index.php?module={$modListHeader[$idx]}&action=index";
            }
        }
    } else {
        $url = $GLOBALS['app']->getLoginRedirect();
    }
} else {
    // Login has failed
    if (isset($_POST['login_language']) && !empty($_POST['login_language'])) {
        $url ="index.php?module=Users&action=Login&login_language=". $_POST['login_language'];
    } else {
        $url ="index.php?module=Users&action=Login";
    }

    if (!empty($login_vars)) {
        $url .= '&' . http_build_query($login_vars);
    }
}

// construct redirect url
$url = 'Location: '.$url;

//adding this for bug: 21712.
if (!empty($GLOBALS['app'])) {
    $GLOBALS['app']->headerDisplayed = true;
}
if (!defined('SUITE_PHPUNIT_RUNNER')) {
    sugar_cleanup();
    header($url);
}
