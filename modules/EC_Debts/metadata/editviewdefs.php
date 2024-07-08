<?php

$module_name = 'EC_Debts';
$viewdefs[$module_name]['EditView'] = array(
    'templateMeta' => array(
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
                array(
                    'name' => 'debt_amount',
                    'label' => 'LBL_DEBT_AMOUNT',
                ),
                array(
                    'name' => 'date_limit',
                    'label' => 'LBL_DATE_LIMIT',
                ),
            ),
            array(
                array(
                    'name' => 'debt_type',
                    'studio' => 'visible',
                    'label' => 'LBL_DEBT_TYPE',
                ),
                array(
                    'name' => 'ngayhachtoan',
                    'studio' => 'visible',
                    'label' => 'LBL_NGAYHACHTOAN',
                ),
            ),
            array(
                array(
                    'name' => 'booking',
                    'studio' => 'visible',
                    'label' => 'LBL_BOOKING',
                ),
                array(
                    'name' => 'supplier',
                    'studio' => 'visible',
                    'label' => 'LBL_SUPPLIER',
                ),
            ),
            array(
                array(
                    'name' => 'contact_name',
                    'label' => 'LBL_CONTACT_NAME',
                ),
                array(
                    'name' => 'phone_office',
                    'label' => 'LBL_PHONE_OFFICE',
                ),
            ),
            array(
                array(
                    'name' => 'phone_fax',
                    'label' => 'LBL_PHONE_FAX',
                ),
                array(
                    'name' => 'phone_mobile',
                    'label' => 'LBL_PHONE_MOBILE',
                ),
            ),
            array(
                array(
                    'name' => 'email',
                    'label' => 'LBL_EMAIL',
                ),
                array(
                    'name' => 'address',
                    'studio' => 'visible',
                    'label' => 'LBL_ADDRESS',
                    'displayParams' =>
                    array(
                        'rows' => 2,
                        'cols' => 45,
                    ),
                ),
            ),
            array(
                array(
                    'name' => 'description',
                    'comment' => 'Full text of the note',
                    'label' => 'LBL_DESCRIPTION',
                    'displayParams' =>
                    array(
                        'rows' => 2,
                        'cols' => 45,
                    ),
                ),
                array(
                    'name' => 'assigned_user_name',
                    'label' => 'LBL_ASSIGNED_TO_NAME',
                ),
            ),
        ),
    ),
);
