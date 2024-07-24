<?php

$module_name = 'EC_Request_Flight';
$searchdefs[$module_name] = array(
    'templateMeta' => array(
        'maxColumns' => '3',
        'maxColumnsBasic' => '4',
        'widths' => array('label' => '10', 'field' => '30'),
    ),
    'layout' => array(
        'basic_search' =>
        array(
            'name' =>
            array(
                'name' => 'name',
                'default' => true,
                'width' => '10%',
            ),
            'contact_name' =>
            array(
                'type' => 'varchar',
                'label' => 'LBL_CONTACT_NAME',
                'width' => '10%',
                'default' => true,
                'name' => 'contact_name',
            ),
            'phone' =>
            array(
                'type' => 'phone',
                'label' => 'LBL_PHONE',
                'width' => '10%',
                'default' => true,
                'name' => 'phone',
            ),
            'email' => 
            array (
                'type' => 'varchar',
                'label' => 'LBL_EMAIL',
                'width' => '10%',
                'default' => true,
                'name' => 'email',
            ),
        ),
        'advanced_search' =>
        array(
            'name' =>
            array(
                'name' => 'name',
                'default' => true,
                'width' => '10%',
            ),
            'contact_name' =>
            array(
                'type' => 'varchar',
                'label' => 'LBL_CONTACT_NAME',
                'width' => '10%',
                'default' => true,
                'name' => 'contact_name',
            ),
            'phone' =>
            array(
                'type' => 'phone',
                'label' => 'LBL_PHONE',
                'width' => '10%',
                'default' => true,
                'name' => 'phone',
            ),
            'email' =>
            array(
                'type' => 'varchar',
                'label' => 'LBL_EMAIL',
                'width' => '10%',
                'default' => true,
                'name' => 'email',
            ),
            'booking' =>
            array(
                'type' => 'relate',
                'studio' => 'visible',
                'label' => 'LBL_BOOKING',
                'width' => '10%',
                'default' => true,
                'name' => 'booking',
            ),
            'city' =>
            array(
                'type' => 'varchar',
                'label' => 'LBL_CITY',
                'width' => '10%',
                'default' => true,
                'name' => 'city',
            ),
            'country' =>
            array(
                'type' => 'varchar',
                'label' => 'LBL_COUNTRY',
                'width' => '10%',
                'default' => true,
                'name' => 'country',
            ),
            'request_type' =>
            array(
                'type' => 'enum',
                'studio' => 'visible',
                'label' => 'LBL_REQUEST_TYPE',
                'width' => '10%',
                'default' => true,
                'name' => 'request_type',
            ),
            'request_status' =>
            array(
                'type' => 'enum',
                'studio' => 'visible',
                'label' => 'LBL_REQUEST_STATUS',
                'width' => '10%',
                'default' => true,
                'name' => 'request_status',
            ),
        ),
    ),
);
