<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('include/JSON.php');
require_once('include/entryPoint.php');
require_once('include/upload_file.php');

global $sugar_config;
$supportedExtensions = array('jpg', 'png', 'jpeg');
$json           = getJSONobj();
$rmdir          = true;
$returnArray    = array();

if ($json->decode(html_entity_decode($_REQUEST['forQuotes']))) {
    $returnArray['forQuotes']="quotes";
} else {
    $returnArray['forQuotes']="company";
}

$upload_ok      = false;
$upload_path    = 'tmp_logo_' . $returnArray['forQuotes'] . '_upload';

if (isset($_FILES['file_1'])) {
    $upload = new UploadFile('file_1');
    if ($upload->confirm_upload()) {
        $upload_dir  = 'upload://' . $upload_path;
        UploadStream::ensureDir($upload_dir);
        if (!verify_uploaded_image($upload->temp_file_location, $returnArray['forQuotes'] == 'quotes')) {
            $returnArray['data']='other';
            $returnArray['path'] = '';
            echo $json->encode($returnArray);
            sugar_cleanup();
            exit();
        }
        $file_name = $upload_dir . '/' . str_replace(' ', '_', $upload->get_stored_file_name());
        if ($upload->final_move($file_name)) {
            $upload_ok = true;
        }
    }
}
if (!$upload_ok) {
    $returnArray['data']='not_recognize';
    echo $json->encode($returnArray);
    sugar_cleanup();
    exit();
}
if (file_exists($file_name) && is_file($file_name)) {
    $encoded_file_name = rawurlencode(str_replace(' ', '_', $upload->get_stored_file_name()));
    $returnArray['path'] = $upload_path . '/' . $encoded_file_name;
    $returnArray['url']= 'cache/images/'.$encoded_file_name;
    if (!verify_uploaded_image($file_name, $returnArray['forQuotes'] == 'quotes')) {
        $returnArray['data']='other';
        $returnArray['path'] = '';
        unlink($file_name);
    } else {
        $img_size = getimagesize($file_name);
        $filetype = $img_size['mime'];
        $test=$img_size[0]/$img_size[1];
        if (($test>10 || $test<1) && $returnArray['forQuotes'] == 'company') {
            $rmdir=false;
            $returnArray['data']='size';
        }
        if (($test>20 || $test<3)&& $returnArray['forQuotes'] == 'quotes') {
            $returnArray['data']='size';
        }

        if (!is_dir(sugar_cached('images'))) {
            try {
                sugar_mkdir(sugar_cached('images'));
            } catch (Exception $exception) {
                $GLOBALS['log']->fatal('Unable to create cache directory \'images\'');
            }
        }

        copy($file_name, sugar_cached('images/' . str_replace(' ', '_', $upload->get_stored_file_name())));
    }
    if (!empty($returnArray['data'])) {
        echo $json->encode($returnArray);
    } else {
        $rmdir=false;
        $returnArray['data']='ok';
        echo $json->encode($returnArray);
    }
} else {
    $returnArray['data']='file_error';
    echo $json->encode($returnArray);
}
sugar_cleanup();
exit();
