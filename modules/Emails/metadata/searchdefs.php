<?php
$module_name = 'Emails';
$searchdefs[$module_name] = array(
    'templateMeta' => array(
        'maxColumns' => '3',
        'maxColumnsBasic' => '4',
        'widths' => array('label' => '10', 'field' => '30'),
    ),
    'layout' => array(
        'basic_search' => array(
            'name',
            array('name' => 'current_user_only', 'label' => 'LBL_CURRENT_USER_FILTER', 'type' => 'bool'),
        ),
        'advanced_search' => array(
           'imap_keywords' => array(
                'name' => 'imap_keywords',
                'label' => 'LBL_IMAP_KEYWORDS',
            ),
            'from_addr_name' => array(
                'name' => 'from_addr_name',
                'label' => 'LBL_FROM',
            ),
            'to_addrs_names' => array(
                'name' => 'to_addrs_names',
                'label' => 'LBL_TO',
            ),
            'name' => array(
                'name' => 'name',
                'label' => 'LBL_SUBJECT',
            ),
            'description' => array(
                'name' => 'description',
                'label' => 'LBL_BODY'
            ),
            'assigned_user_id' => array(
                'name' => 'assigned_user_id',
                'label' => 'LBL_ASSIGNED_TO',
                'type' => 'enum',
                'function' => array('name' => 'get_user_array', 'params' => array(false))
            ),
            // 'category_id' => array(
            //     'name' => 'category_id',
            //     'default' => true,
            //     'width' => '10%',
            // ),
            // 'parent_name' => array(
            //     'name' => 'parent_name',
            //     'default' => true,
            //     'width' => '10%',
            // ),
        ),
    ),
);
