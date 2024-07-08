<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$GLOBALS['studioDefs']['Calls'] = array(
    'LBL_DETAILVIEW' => array(
        'template' => 'xtpl',
        'template_file' => 'modules/Calls/DetailView.html',
        'php_file' => 'modules/Calls/DetailView.php',
        'type' => 'DetailView',
    ),
    'LBL_EDITVIEW' => array(
        'template' => 'xtpl',
        'template_file' => 'modules/Calls/EditView.html',
        'php_file' => 'modules/Calls/EditView.php',
        'type' => 'EditView',
    ),
    'LBL_LISTVIEW' => array(
        'template' => 'listview',
        'meta_file' => 'modules/Calls/listviewdefs.php',
        'type' => 'ListView',
    ),
    'LBL_SEARCHFORM' => array(
        'template' => 'xtpl',
        'template_file' => 'modules/Calls/SearchForm.html',
        'php_file' => 'modules/Calls/ListView.php',
        'type' => 'SearchForm',
    ),
);
