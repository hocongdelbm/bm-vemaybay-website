<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
ob_start();
chdir(dirname(__FILE__) . '/../../');
require('include/entryPoint.php');
require_once('SoapHelperWebService.php');
require_once('SugarRestUtils.php');
require_once($webservice_path);
require_once($registry_path);
if (isset($webservice_impl_class_path)) {
    require_once($webservice_impl_class_path);
}
$url = $GLOBALS['sugar_config']['site_url'] . $location;
$service = new $webservice_class($url);
$service->registerClass($registry_class);
$service->register();
$service->registerImplClass($webservice_impl_class);

// set the service object in the global scope so that any error, if happens, can be set on this object
global $service_object;
$service_object = $service;

$service->serve();
