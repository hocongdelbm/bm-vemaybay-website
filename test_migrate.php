<?php
// test_migrate.php - XOÁ SAU KHI TEST
define('sugarEntry', true);
require_once('include/entryPoint.php');
require_once('custom/modules/Schedulers/_AddJobsHere.php');

echo "<pre>";
$result = migrateZaloImagesToNextCloud();
echo "Result: " . ($result ? 'true' : 'false') . "\n";
echo "</pre>";