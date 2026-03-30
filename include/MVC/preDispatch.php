<?php
if (!defined('sugarEntry')) {
    define('sugarEntry', true);
}



/*
 * First step in removing getimage and getYUIComboFile -- at least this bypasses most of the app,
 * making assets load faster.
 */
if (isset($_GET["entryPoint"])) {
    if ($_GET["entryPoint"] == "getImage") {
        require_once('include/SugarTheme/SugarTheme.php');
        require_once('include/utils.php');
        include("include/SugarTheme/getImage.php");
        die();
    } else {
        if ($_GET["entryPoint"] == "getYUIComboFile") {
            include("include/javascript/getYUIComboFile.php");
            die();
        }
    }
}
