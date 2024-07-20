<?php

$module_name = 'EC_Request_Flight';
$viewdefs[$module_name]['DetailView'] = array(
    'templateMeta' => array(
        'form' => array(
            'buttons' => array(
                'EDIT',
                'DUPLICATE',
                'DELETE',
            )
        ),
        'maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30')
        ),
    ),

    'panels' =>
    array(
        'default' =>
        array(
            array(
                'name',
            ),
            array(
                array(
                    'name' => 'contact_name',
                    'label' => 'LBL_CONTACT_NAME',
                ),
                array(
                    'name' => 'phone',
                    'label' => 'LBL_PHONE',
                ),
            ),
            array(
                array(
                    'name' => 'email',
                    'label' => 'LBL_EMAIL',
                ),
                array(
                    'name' => 'request_type',
                    'studio' => 'visible',
                    'label' => 'LBL_REQUEST_TYPE',
                ),
            ),
            array(
                array(
                    'name' => 'request_status',
                    'studio' => 'visible',
                    'label' => 'LBL_REQUEST_STATUS',
                ),
                array(
                    'name' => 'booking',
                    'studio' => 'visible',
                    'label' => 'LBL_BOOKING',
                ),
            ),
            array(
                array(
                    'name' => 'request_detail',
                    'studio' => 'visible',
                    'label' => 'LBL_REQUEST_DETAIL',
                ),
            ),
            array(
                array(
                    'name' => 'city',
                    'label' => 'LBL_CITY',
                ),
                array(
                    'name' => 'country',
                    'label' => 'LBL_COUNTRY',
                ),
            ),
            array(
                array(
                    'name' => 'date_entered',
                    'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}',
                    'label' => 'LBL_DATE_ENTERED',
                ),
                array(
                    'name' => 'date_modified',
                    'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}',
                    'label' => 'LBL_DATE_MODIFIED',
                ),
            ),
            array(
                'description',
                'assigned_user_name',
            ),
        ),
    ),
);
