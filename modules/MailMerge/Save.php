<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('modules/MailMerge/MailMerge.php');

global $beanList, $beanFiles;

$module = $_POST['mailmerge_module'];
$document_id = $_POST['document_id'];
$selObjs = urldecode($_POST['selected_objects_def']);

$item_ids = array();
parse_str($selObjs, $item_ids);

$class_name = $beanList[$module];
$includedir = $beanFiles[$class_name];
require_once($includedir);
$seed = new $class_name();

// $fields = get_field_list($seed);

$document = BeanFactory::newBean('Documents');
$document->retrieve($document_id);

$items = array();
foreach ($item_ids as $key => $value) {
    $seed->retrieve($key);
    $items[] = $seed;
}

$maxExecutionTime = ini_get('max_execution_time');

set_time_limit(600);
$dataDir = create_cache_directory("MergedDocuments/");
$fileName = UploadFile::realpath("upload://$document->document_revision_id");
$outfile = pathinfo($document->filename, PATHINFO_FILENAME);

$mm = new MailMerge(null, null, $dataDir);
$mm->SetDataList($items);
$mm->SetFieldList($fields);
$mm->Template(array($fileName, $outfile));
$file = $mm->Execute();
$mm->CleanUp();

set_time_limit($maxExecutionTime);

header("Location: index.php?module=MailMerge&action=Step4&file=" . urlencode($file));
