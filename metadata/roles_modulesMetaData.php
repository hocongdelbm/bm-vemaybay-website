<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$dictionary['roles_modules'] = array(

    'table' => 'roles_modules',

    'fields' => array(
        array(
            'name' => 'id',
            'type' => 'varchar',
            'len' => '36',
        ),
        array(
            'name' => 'role_id',
            'type' => 'varchar',
            'len' => '36',
        ),
        array(
            'name' => 'module_id',
            'type' => 'varchar',
            'len' => '36',
        ),
        array(
            'name' => 'allow',
            'type' => 'bool',
            'len' => '1',
            'default' => '0',
        ),
        array('name' => 'date_modified', 'type' => 'datetime'),
        array(
            'name' => 'deleted',
            'type' => 'bool',
            'len' => '1',
            'default' => '0'
        ),
    ),

    'indices' => array(
        array(
            'name' => 'roles_modulespk',
            'type' => 'primary',
            'fields' => array('id')
        ),
        array(
            'name' => 'idx_role_id',
            'type' => 'index',
            'fields' => array('role_id')
        ),
        array(
            'name' => 'idx_module_id',
            'type' => 'index',
            'fields' => array('module_id')
        ),
    ),
);
