<?php

/*
 * Created on May 29, 2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
$searchdefs['Bugs'] = array(
    'templateMeta' => array(
        'maxColumns' => '3',
        'maxColumnsBasic' => '4',
        'widths' => array('label' => '10', 'field' => '30'),
    ),
    'layout' => array(
        'basic_search' => array(
            'name',
            // array('name' => 'current_user_only', 'label' => 'LBL_CURRENT_USER_FILTER', 'type' => 'bool'),
            // array('name' => 'open_only', 'label' => 'LBL_OPEN_ITEMS', 'type' => 'bool', 'default' => false, 'width' => '10%'),
        ),
        'advanced_search' => array(
            'bug_number',
            'name',
            'resolution',
            'type',
            'status',
            // array('name' => 'assigned_user_id', 'type' => 'enum', 'label' => 'LBL_ASSIGNED_TO', 'function' => array('name' => 'get_user_array', 'params' => array(false))),
            'priority',

        ),
    ),
);
