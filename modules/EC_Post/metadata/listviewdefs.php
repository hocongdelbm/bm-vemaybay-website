<?php


if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$listViewDefs['EC_Post'] = array(
    'ID' => array(
        'width' => '8',
        'label' => 'LBL_ID',
        'default' => true,
    ),
    'POST_TITLE' => array(
        'width' => '32',
        'label' => 'LBL_POST_TITLE',
        'link' => true,
        'default' => true,
    ),
    'POST_STATUS' => array(
        'width' => '10',
        'label' => 'LBL_POST_STATUS',
        'default' => true,
    ),
    'PUBLISHED_AT' => array(
        'width' => '15',
        'label' => 'LBL_PUBLISHED_AT',
        'default' => true,
    ),
    'POST_TYPE' => array(
        'width' => '10',
        'label' => 'LBL_POST_TYPE',
        'default' => true,
    ),
);