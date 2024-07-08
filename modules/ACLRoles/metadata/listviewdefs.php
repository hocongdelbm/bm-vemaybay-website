<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$listViewDefs['ACLRoles'] = array(
    'NAME' => array(
        'label' => 'LBL_NAME',
        'width' => '20%',
        'link' => true,
        'default' => true
    ),
    'DESCRIPTION' => array(
        'label' => 'LBL_DESCRIPTION',
        'width' => '80%',
        'default' => true
    ),
);
