<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = 'EC_Bank_Account';
$listViewDefs[$module_name] = array(
    'ACCOUNT_NUMBER' => array(
        'type' => 'varchar',
        'label' => 'LBL_ACCOUNT_NUMBER',
        'width' => '10%',
        'default' => true,
        'link' => true,
    ),
    'NAME' => array(
        'width' => '20%',
        'label' => 'LBL_NAME',
        'default' => true,
        'link' => true,
    ),
    'BANK' => array(
        'type' => 'relate',
        'studio' => 'visible',
        'label' => 'LBL_BANK',
        'width' => '20%',
        'default' => true,
    ),
    'ACCOUNT_HOLDER' => array(
        'type' => 'varchar',
        'label' => 'LBL_ACCOUNT_HOLDER',
        'width' => '20%',
        'default' => true,
    ),
    'BRANCH' => array(
        'type' => 'text',
        'studio' => 'visible',
        'label' => 'LBL_BRANCH',
        'width' => '30%',
        'default' => true,
    ),
    'IS_DISPLAY' => array(
        'type' => 'bool',
        'default' => true,
        'label' => 'LBL_IS_DISPLAY',
        'width' => '8%',
    ),
    'IS_SMS' => array(
        'type' => 'bool',
        'default' => true,
        'label' => 'LBL_IS_SMS',
        'width' => '8%',
    ),
);
