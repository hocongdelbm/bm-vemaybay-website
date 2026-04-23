<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings;
$popupMeta = array('moduleMain' => 'ACLRole',
    'varName' => 'ROLE',
    'listTitle' => $mod_strings['LBL_ROLE'],
    'orderBy' => 'name',
    'whereClauses' => array('name' => 'acl_roles.name'),
    'searchInputs' => array('name'),
    'searchdefs'   => array('name' => array('name' => 'name', 'label' => 'LBL_NAME',),)
);
