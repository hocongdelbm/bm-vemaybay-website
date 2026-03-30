<?php

$viewdefs['Users']['DetailView'] = array(
    'templateMeta' =>
    array(
        'form' =>
        array(
            'buttons' => array(),
        ),
        'maxColumns' => '2',
        'widths' =>
        array(
            0 =>
            array(
                'label' => '10',
                'field' => '30',
            ),
            1 =>
            array(
                'label' => '10',
                'field' => '30',
            ),
        ),
        'useTabs' => true,
        'tabDefs' =>
        array(
            'LBL_USER_INFORMATION' =>
            array(
                'newTab' => true,
                'panelDefault' => 'expanded',
            ),
            'LBL_EMPLOYEE_INFORMATION' =>
            array(
                'newTab' => false,
                'panelDefault' => 'collapsed',
            ),
        ),
    ),
    'panels' =>
    array(
        'LBL_USER_INFORMATION' =>
        array(
            array(
                'full_name',
                'user_name',
            ),
            array(
                'status',
                array(
                    'name' => 'UserType',
                    'customCode' => '{$USER_TYPE_READONLY}',
                ),
            ),
            array(
                'photo',
                'agent_prefix'
            ),
        ),
        'LBL_EMPLOYEE_INFORMATION' =>
        array(
            array(
                'employee_status',
                'show_on_employees',
            ),
            array(
                'title',
                'phone_work',
            ),
            array(
                'department',
                'phone_mobile',
            ),
            array(
                'reports_to_name',
                'phone_other',
            ),
            array(
                'phone_home',
                'phone_fax',
            ),
            array(
                'messenger_type',
                'messenger_id',
            ),
            array(
                'address_street',
                'address_city',
            ),
            array(
                'address_state',
                'address_postalcode',
            ),
            array(
                'address_country',
                'description',
            ),
        ),
    ),
);
