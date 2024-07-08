<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$GLOBALS['studioDefs']['Users'] = array(
    'LBL_DETAILVIEW'=>array(
        'template'=>'DetailView',
        'meta_file'=>'modules/Users/detailviewdefs.php',
        'type'=>'Detailview',
    ),
    'LBL_EDITVIEW'=>array(
        'template'=>'EditView',
        'meta_file'=>'modules/Users/editviewdefs.php',
        'type'=>'EditView',
    ),
    'LBL_LISTVIEW'=>array(
        'template'=>'listview',
        'meta_file'=>'modules/Users/listviewdefs.php',
        'type'=>'ListView',
    ),
    'LBL_SEARCHFORM'=>array(
        'template'=>'xtpl',
        'template_file'=>'modules/Users/SearchForm.html',
        'php_file'=>'modules/Users/ListView.php',
        'type'=>'SearchForm',
    ),
);
