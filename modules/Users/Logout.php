<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

// record the last theme the user used
$current_user->setPreference('lastTheme', $theme);
$GLOBALS['current_user']->call_custom_logic('before_logout');

if (method_exists($authController->authController, 'preLogout')) {
    $authController->authController->preLogout();
}

// submitted by Tim Scott from SugarCRM forums
foreach ($_SESSION as $key => $val) {
    $_SESSION[$key] = ''; // cannot just overwrite session data, causes segfaults in some versions of PHP
}
if (isset($_COOKIE[session_name()])) {
    SugarApplication::setCookie(session_name(), '', time()-42000, '/', null, isSSL(), true);
}

//Update the tracker_sessions table
// clear out the authenticating flag
session_destroy();

LogicHook::initialize();
$GLOBALS['logic_hook']->call_custom_logic('Users', 'after_logout');

/** @var AuthenticationController $authController */
$authController->authController->logout();
