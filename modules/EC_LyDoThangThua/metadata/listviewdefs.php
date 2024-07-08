<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = 'EC_LyDoThangThua';
$listViewDefs[$module_name] = array(
    'NAME' => array(
        'width' => '40%',
        'label' => 'LBL_NAME',
        'default' => true,
        'link' => true,
    ),
    'LOAILYDO' => array(
        'type' => 'enum',
        'studio' => 'visible',
        'label' => 'LBL_LOAILYDO',
        'width' => '10%',
        'default' => true,
    ),
    'DESCRIPTION' => array(
        'type' => 'text',
        'label' => 'LBL_DESCRIPTION',
        'width' => '30%',
        'default' => true,
    ),
    'DATE_ENTERED' => array(
        'type' => 'datetime',
        'label' => 'LBL_DATE_ENTERED',
        'width' => '13%',
        'default' => true,
    ),
);
