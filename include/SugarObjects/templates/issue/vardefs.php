<?php


$vardefs = array(
    'fields' => array(
        $_object_name . '_number' => array(
            'name' => $_object_name . '_number',
            'vname' => 'LBL_NUMBER',
            'type' => 'int',
            'readonly' => true,
            'len' => 11,
            'required' => true,
            'auto_increment' => true,
            'unified_search' => true,
            'full_text_search' => array('boost' => 3),
            'comment' => 'Visual unique identifier',
            'duplicate_merge' => 'disabled',
            'disable_num_format' => true,
            'studio' => array('quickcreate' => false),
            'inline_edit' => false,
        ),

        'name' => array(
            'name' => 'name',
            'vname' => 'LBL_SUBJECT',
            'type' => 'name',
            'link' => true,
            'dbType' => 'varchar',
            'len' => 255,
            'audited' => true,
            'unified_search' => true,
            'full_text_search' => array('boost' => 3),
            'comment' => 'The short description of the bug',
            'merge_filter' => 'selected',
            'required' => true,
            'importable' => 'required',

        ),
        'type' => array(
            'name' => 'type',
            'vname' => 'LBL_TYPE',
            'type' => 'enum',
            'options' => strtolower($object_name) . '_type_dom',
            'len' => 255,
            'comment' => 'The type of issue (ex: issue, feature)',
            'merge_filter' => 'enabled',
        ),

        'status' => array(
            'name' => 'status',
            'vname' => 'LBL_STATUS',
            'type' => 'enum',
            'options' => strtolower($object_name) . '_status_dom',
            'len' => 100,
            'audited' => true,
            'comment' => 'The status of the issue',
            'merge_filter' => 'enabled',

        ),

        'priority' => array(
            'name' => 'priority',
            'vname' => 'LBL_PRIORITY',
            'type' => 'enum',
            'options' => strtolower($object_name) . '_priority_dom',
            'len' => 100,
            'audited' => true,
            'comment' => 'An indication of the priorty of the issue',
            'merge_filter' => 'enabled',

        ),

        'resolution' => array(
            'name' => 'resolution',
            'vname' => 'LBL_RESOLUTION',
            'type' => 'enum',
            'options' => strtolower($object_name) . '_resolution_dom',
            'len' => 255,
            'audited' => true,
            'comment' => 'An indication of how the issue was resolved',
            'merge_filter' => 'enabled',

        ),

        //not in cases.
        'work_log' => array(
            'name' => 'work_log',
            'vname' => 'LBL_WORK_LOG',
            'type' => 'text',
            'comment' => 'Free-form text used to denote activities of interest'
        ),

    ),
    'indices' => array(
        'number' => array(
            'name' => strtolower($module) . 'numk',
            'type' => 'unique',
            'fields' => array($_object_name . '_number')
        )
    ),

);
