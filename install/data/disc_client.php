<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

// array elements are regexp patterns, and must not contain '#' signs
$disc_client_ignore = array(
    // dirs
    "\\./cache/.*",
    "\\./examples/.*",
    // files
    "\\.*config\\.php\$",
    "\\.*sugarcrm\\.log\\.*",
    
     "\\.*sync\\.log\\.*",
    "\\.htaccess\$",
    "\\.*\\.tmp\$",
    "\\.*\\.bak\$",
    "\\.*\\.zip\$",
    
);

$disc_client_no_sync = array(
);
