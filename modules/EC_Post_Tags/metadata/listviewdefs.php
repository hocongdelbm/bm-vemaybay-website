<?php


if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = 'EC_Post_Tags';
$listViewDefs[$module_name] = array(
    'NAME' => array(
        'width' => '32',
        'label' => 'LBL_NAME',
        'default' => true,
        'link' => true
    ),
    'DATE_MODIFIED' => array(
        'width' => '15',
        'label' => 'LBL_DATE_MODIFIED',
        'default' => true,
    ),
    'DESCRIPTION' => array(
        'width' => '40',
        'label' => 'LBL_DESCRIPTION',
        'default' => false,
    ),

);
