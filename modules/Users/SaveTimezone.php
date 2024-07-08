<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $current_user;
global $sugar_config;

if (isset($_POST['timezone']) || isset($_GET['timezone'])) {
    if (isset($_POST['timezone'])) {
        $timezone = $_POST['timezone'];
    } else {
        $timezone = $_GET['timezone'];
    }

    $current_user->setPreference('timezone', $timezone);
    $current_user->setPreference('ut', 1);
    $current_user->savePreferencesToDB();
    session_write_close();
    header('Location: index.php?action=index&module=Home');
    exit();
}
