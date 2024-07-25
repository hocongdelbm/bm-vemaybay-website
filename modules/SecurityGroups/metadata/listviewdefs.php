<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}


$module_name = 'SecurityGroups';
$listViewDefs[$module_name] = array(
    'NAME' => array(
        'width' => '32',
        'label' => 'LBL_NAME',
        'default' => true,
        'link' => true
    ),

    'ASSIGNED_USER_NAME' => array(
        'width' => '9',
        'label' => 'LBL_ASSIGNED_TO_NAME',
        'default' => true
    ),

    'NONINHERITABLE' => array(
        'width' => '9',
        'label' => 'LBL_NONINHERITABLE',
        'default' => true
    ),
    'USE_AUTO_BOOK' => array(
        'type' => 'bool',
        'label' => 'LBL_USE_AUTO_BOOK',
        'width' => '10%',
        'default' => true,
    ),
    'USE_MAIL_CONFIRM' => array(
        'type' => 'bool',
        'label' => 'LBL_USE_MAIL_CONFIRM',
        'width' => '10%',
        'default' => true,
    ),
    'USE_MAIL_ETICKET' => array(
        'type' => 'bool',
        'label' => 'LBL_USE_MAIL_ETICKET',
        'width' => '10%',
        'default' => true,
    ),
    'COM_NAME' => array(
        'type' => 'varchar',
        'label' => 'LBL_COM_NAME',
        'width' => '20%',
        'default' => true,
    ),
    'COM_TAXCODE' => array(
        'type' => 'varchar',
        'label' => 'LBL_COM_TAXCODE',
        'width' => '10%',
        'default' => true,
    ),
    'IS_REPORT' => array(
        'type' => 'bool',
        'label' => 'LBL_IS_REPORT',
        'width' => '10%',
        'default' => true,
    ),
);
