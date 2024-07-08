<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}


/*
**this is the ajax call that is called from RebuildJSLang.php.  It processes
**the Request object in order to call correct methods for repairing/rebuilding JSfiles
*Note that minify.php has already been included as part of index.php, so no need to include again.
*/

 
//set default root directory
$from = getcwd();
if (isset($_REQUEST['root_directory'])  && !empty($_REQUEST['root_directory'])) {
    $from = $_REQUEST['root_directory'];
}
//this script can take a while, change max execution time to 10 mins
$tmp_time = ini_get('max_execution_time');
ini_set('max_execution_time', '600');
    
    //figure out which commands to call.
    if ($_REQUEST['js_admin_repair'] == 'concat') {
        //concatenate mode, call the files that will concatenate javascript group files
        $_REQUEST['js_rebuild_concat'] = 'rebuild';
        require_once('jssource/minify.php');
    } else {
        $_REQUEST['root_directory'] = getcwd();
        require_once('jssource/minify.php');
    
        if ($_REQUEST['js_admin_repair'] == 'replace') {
            //should replace compressed JS with source js
            reverseScripts("$from/jssource/src_files", (string)$from);
        } elseif ($_REQUEST['js_admin_repair'] == 'mini') {
            //should replace compressed JS with minified version of source js
            reverseScripts("$from/jssource/src_files", (string)$from);
            BackUpAndCompressScriptFiles((string)$from, "", false);
            ConcatenateFiles((string)$from);
        } elseif ($_REQUEST['js_admin_repair'] == 'repair') {
            //should compress existing javascript (including changes done) without overwriting original source files
            BackUpAndCompressScriptFiles((string)$from, "", false);
            ConcatenateFiles((string)$from);
        }
    }
//set execution time back to what it was
ini_set('max_execution_time', $tmp_time);
