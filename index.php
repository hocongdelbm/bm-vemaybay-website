<?php
if (!defined('sugarEntry')) {
    define('sugarEntry', true);
}

ini_set('display_errors', 0);
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);


// // Fix ERR_CACHE_MISS
// header('Cache-Control: no cache'); // no cache
// session_cache_limiter('private_no_expire'); // works

include 'include/MVC/preDispatch.php';
$startTime = microtime(true);
require_once 'include/entryPoint.php';
ob_start();
require_once 'include/MVC/SugarApplication.php';
$app = new SugarApplication();
$app->startSession();
$app->execute();
