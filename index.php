<?php
if (!defined('sugarEntry')) {
    define('sugarEntry', true);
}

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
