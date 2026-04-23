<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
require_once('modules/MailMerge/MailMerge.php');
require_once('include/upload_file.php');

global  $beanList, $beanFiles;
global $app_strings;
global $app_list_strings;
global $mod_strings;

$xtpl = new XTemplate('modules/MailMerge/Merge.html');
$xtpl->assign("MAILMERGE_IS_REDIRECT", false);

$mTime = microtime();
$redirectUrl = 'index.php?action=index&step=5&module=MailMerge&mtime=' . $mTime;

/**
 * Bug #42275
 * Just refresh download page to get file which was banned by IE security
 */
if (empty($_SESSION['MAILMERGE_MODULE']) && !empty($_SESSION['mail_merge_file_location']) && !empty($_SESSION['mail_merge_file_name'])) {
    $xtpl->assign("MAILMERGE_REDIRECT", true);
} else {
    $module = $_SESSION['MAILMERGE_MODULE'];
    $document_id = $_SESSION['MAILMERGE_DOCUMENT_ID'];
    $selObjs = urldecode($_SESSION['SELECTED_OBJECTS_DEF']);
    $relObjs = (isset($_SESSION['MAILMERGE_RELATED_CONTACTS']) ? $_SESSION['MAILMERGE_RELATED_CONTACTS'] : '');

    $relModule = '';
    if (!empty($_SESSION['MAILMERGE_CONTAINS_CONTACT_INFO'])) {
        $relModule = $_SESSION['MAILMERGE_CONTAINS_CONTACT_INFO'];
    }

    if ($_SESSION['MAILMERGE_MODULE'] == null) {
        sugar_die("Error during Mail Merge process.  Please try again.");
    }

    $_SESSION['MAILMERGE_MODULE'] = null;
    $_SESSION['MAILMERGE_DOCUMENT_ID'] = null;
    $_SESSION['SELECTED_OBJECTS_DEF'] = null;
    $_SESSION['MAILMERGE_SKIP_REL'] = null;
    $_SESSION['MAILMERGE_CONTAINS_CONTACT_INFO'] = null;
    $item_ids = array();
    parse_str(stripslashes(html_entity_decode($selObjs, ENT_QUOTES)), $item_ids);

    $class_name = $beanList[$module];
    $includedir = $beanFiles[$class_name];
    require_once($includedir);
    $seed = new $class_name();

    $document = BeanFactory::newBean('DocumentRevisions');
    $document->retrieve($document_id);

    if (!empty($relModule)) {
        $rel_class_name = $beanList[$relModule];
        require_once($beanFiles[$rel_class_name]);
        $rel_seed = new $rel_class_name();
    }

    global $sugar_config;

    $filter = array();
    array_push($filter, 'link');

    $merge_array = array();
    $merge_array['master_module'] = $module;
    $merge_array['related_module'] = $relModule;
    $ids = array();

    foreach ($item_ids as $key => $value) {
        if (!empty($relObjs[$key])) {
            $ids[$key] = $relObjs[$key];
        } else {
            $ids[$key] = '';
        }
    } //rof
    $merge_array['ids'] = $ids;

    $dataDir = getcwd() . '/' . sugar_cached('MergedDocuments/');
    if (!file_exists($dataDir)) {
        sugar_mkdir($dataDir);
    }
    mt_srand((float)microtime() * 1000000);
    $dataFileName = 'sugardata' . $mTime . '.php';
    write_array_to_file('merge_array', $merge_array, $dataDir . $dataFileName);
    //Save the temp file so we can remove when we are done
    $_SESSION['MAILMERGE_TEMP_FILE_' . $mTime] = $dataDir . $dataFileName;
    $site_url = $sugar_config['site_url'];
    $templateFile = $site_url . '/' . UploadFile::get_url(from_html($document->filename), $document->id);
    $dataFile = $dataFileName;
    $startUrl = 'index.php?action=index&module=MailMerge&reset=true';

    $relModule = trim($relModule);
    $contents = "SUGARCRM_MAIL_MERGE_TOKEN#$templateFile#$dataFile#$module#$relModule";

    $rtfFileName = 'sugartokendoc' . $mTime . '.doc';
    $fp = sugar_fopen($dataDir . $rtfFileName, 'w');
    fwrite($fp, $contents);
    fclose($fp);

    $_SESSION['mail_merge_file_location'] = sugar_cached('MergedDocuments/') . $rtfFileName;
    $_SESSION['mail_merge_file_name'] = $rtfFileName;

    $xtpl->assign("MAILMERGE_FIREFOX_URL", $site_url . '/' . $GLOBALS['sugar_config']['cache_dir'] . 'MergedDocuments/' . $rtfFileName);
    $xtpl->assign("MAILMERGE_START_URL", $startUrl);
    $xtpl->assign("MAILMERGE_TEMPLATE_FILE", $templateFile);
    $xtpl->assign("MAILMERGE_DATA_FILE", $dataFile);
    $xtpl->assign("MAILMERGE_MODULE", $module);

    $xtpl->assign("MAILMERGE_REL_MODULE", $relModule);
}

$xtpl->assign("MOD", $mod_strings);
$xtpl->assign("APP", $app_strings);
$xtpl->assign("MAILMERGE_REDIRECT_URL", $redirectUrl);
$xtpl->parse("main");
$xtpl->out("main");
