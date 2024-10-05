<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$GLOBALS['studioDefs']['Employees'] = array(
    'LBL_DETAILVIEW' => array(
        'template' => 'xtpl',
        'template_file' => 'modules/Employees/DetailView.html',
        'php_file' => 'modules/Employees/DetailView.php',
        'type' => 'DetailView',
    ),
    'LBL_EDITVIEW' => array(
        'template' => 'xtpl',
        'template_file' => 'modules/Employees/EditView.html',
        'php_file' => 'modules/Employees/EditView.php',
        'type' => 'EditView',
    ),
    'LBL_LISTVIEW' => array(
        'template' => 'listview',
        'meta_file' => 'modules/Employees/listviewdefs.php',
        'type' => 'ListView',
    ),
    'LBL_SEARCHFORM' => array(
        'template' => 'xtpl',
        'template_file' => 'modules/Employees/SearchForm.html',
        'php_file' => 'modules/Employees/ListView.php',
        'type' => 'SearchForm',
    ),
);
