<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
require_once 'include/utils.php';

global $current_user, $timedate;
$db = DBManagerFactory::getInstance();


if (isset($_SESSION['rebuild_relationships'])) {
    displayAdminError(translate('MSG_REBUILD_RELATIONSHIPS', 'Administration'));
}

if (isset($_SESSION['rebuild_extensions'])) {
    displayAdminError(translate('MSG_REBUILD_EXTENSIONS', 'Administration'));
}

if (!isset($_SERVER['SERVER_SOFTWARE'])) {
    LoggerManager::getLogger()->warn('SERVER_SOFTVARE is undefined got Display Warnings');
}

if (isset($_SERVER['SERVER_SOFTWARE']) && (strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS') !== false) && (php_sapi_name() == 'cgi-fcgi') && (ini_get('fastcgi.logging') != '0')) {
    displayAdminError(translate('LBL_FASTCGI_LOGGING', 'Administration'));
}
if (is_admin($current_user)) {
    if (!empty($_SESSION['COULD_NOT_CONNECT'])) {
        displayAdminError(translate('LBL_COULD_NOT_CONNECT', 'Administration') . ' ' . $timedate->to_display_date_time($_SESSION['COULD_NOT_CONNECT']));
    }

    //No SMTP server is set up Error.
    $admin = BeanFactory::newBean('Administration');
    $smtp_error = $admin->checkSmtpError();

    if (!isset($sugar_config['installer_locked']) || $sugar_config['installer_locked'] == false) {
        displayAdminError(translate('WARN_INSTALLER_LOCKED', 'Administration'));
    }

    if (empty($sugar_config['admin_access_control'])) {
        if (isset($_SESSION['invalid_versions'])) {
            $invalid_versions = $_SESSION['invalid_versions'];
            foreach ($invalid_versions as $invalid) {
                displayAdminError(translate('WARN_UPGRADE', 'Administration') . $invalid['name'] . translate('WARN_UPGRADE2', 'Administration'));
            }
        }
    }

    if (isset($_SESSION['administrator_error'])) {
        // Only print DB errors once otherwise they will still look broken
        // after they are fixed.
        displayAdminError($_SESSION['administrator_error']);
    }

    unset($_SESSION['administrator_error']);
}
