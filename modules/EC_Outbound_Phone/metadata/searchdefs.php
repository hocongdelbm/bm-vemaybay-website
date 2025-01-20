<?php
$module_name = 'EC_Outbound_Phone';
$searchdefs[$module_name] = array(
    'templateMeta' => array(
        'maxColumns' => '3',
        'maxColumnsBasic' => '4',
        'widths' => array('label' => '10', 'field' => '30'),
    ),
    'layout' => array(
        'basic_search' => array(
            'name',
            array(
                'name' => 'brand_name',
                'label' => 'LBL_BRAND_NAME',
            ),
            array('name' => 'current_user_only', 'label' => 'LBL_CURRENT_USER_FILTER', 'type' => 'bool'),
        ),
        'advanced_search' => array(
            array(
                'name' => 'name',
                'label' => 'LBL_NAME',
            ),
            array(
                'name' => 'brand_name',
                'label' => 'LBL_BRAND_NAME',
            ),
            array(
                'name' => 'proxy',
                'label' => 'LBL_PROXY',
            ),
 
            array(
                'name' => 'website',
                'label' => 'LBL_WEBSITE',
            ),
            array(
                'name' => 'label',
                'label' => 'LBL_LABEL',
            ),
            array(
                'name' => 'only_inbound',
                'label' => 'LBL_ONLY_INBOUND',
            ),
            array(
                'name' => 'network_provider',
                'label' => 'LBL_NETWORK_PROVIDER',
            ),
            array(
                'name' => 'status',
                'label' => 'LBL_STATUS',
            ),
            array('name' => 'current_user_only', 'label' => 'LBL_CURRENT_USER_FILTER', 'type' => 'bool'),
            // array(
            //     'name' => 'assigned_user_id',
            //     'label' => 'LBL_ASSIGNED_TO',
            //     'type' => 'enum',
            //     'function' => array('name' => 'get_user_array', 'params' => array(false))
            // ),
        ),
    ),
);
