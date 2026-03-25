<?php
include_once __DIR__ . '/../../../vendor/autoload.php';

// Prevent errors from being echoed out to the client
// We MUST use the exceptions instead to pass the errors object
// back to the client
ini_set('error_reporting', ~E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);

chdir(__DIR__ . '/../../../');

include_once __DIR__ . '/../../../include/utils/array_utils.php';
include_once __DIR__ . '/../../../include/SugarObjects/SugarConfig.php';
include_once __DIR__ . '/../../../include/SugarLogger/SugarLogger.php';
include_once __DIR__ . '/../../../include/SugarLogger/LoggerManager.php';

SuiteCRM\ErrorMessage::log('Calling this area of API is depricated. Use http://[SuiteCRM_instance]/Api/V8... ', 'deprecated');

require_once __DIR__ . '/../../../include/entryPoint.php';
global $sugar_config;
global $version;
global $container;

preg_match("/\/api\/(.*?)\//", $_SERVER['REQUEST_URI'], $matches);

$GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);

$_SERVER['REQUEST_URI'] = $_SERVER['PHP_SELF'];

$version = 8;

require_once __DIR__ . '/containers.php';

$app = new \Slim\App($container);
$paths = new \SuiteCRM\Utility\Paths();


// Load Core Routes
$routeFiles = (array) glob($paths->getLibraryPath() . '/API/v8/route/*.php');
foreach ($routeFiles as $routeFile) {
    require $routeFile;
}

// Load Custom Routes
$customRouteFiles = (array) glob($paths->getCustomLibraryPath() . '/API/v8/route/*.php');
foreach ($customRouteFiles as $routeFile) {
    require $routeFile;
}

// Load callables
$callableFiles = (array) glob($paths->getLibraryPath() . '/API/v8/callable/*.php');
foreach ($callableFiles as $callableFile) {
    require $callableFile;
}

$customCallableFiles = (array) glob($paths->getCustomLibraryPath() . '/API/v8/callable/*.php');
foreach ($customCallableFiles as $callableFile) {
    require $callableFile;
}

$app->run();
