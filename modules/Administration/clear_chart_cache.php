<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $sugar_config, $mod_strings;

print($mod_strings['LBL_CLEAR_CHART_DATA_CACHE_FINDING'] . "<br>");

$search_dir=sugar_cached("");
$all_src_files  = findAllFiles($search_dir.'/xml', array());

print($mod_strings['LBL_CLEAR_CHART_DATA_CACHE_DELETING1'] . "<br>");
foreach ($all_src_files as $src_file) {
    if (preg_match('/\.xml$/', $src_file)) {
        print($mod_strings['LBL_CLEAR_CHART_DATA_CACHE_DELETING2'] . " $src_file<BR>") ;
        unlink((string)$src_file);
    }
}

echo "\n--- " . $mod_strings['LBL_DONE'] . "---<br />\n";
