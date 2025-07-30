<?php

$module_name = 'EC_Payment_Voucher';
$viewdefs[$module_name]['QuickCreate'] = array(
    'templateMeta' => array(
        'maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30')
        ),
    ),

    'panels' => array(
        'default' =>
        array(
            array(
                array(
                    'name' => 'receipent_name',
                    'label' => 'LBL_RECEIPENT_NAME',
                ),
                array(
                    'name' => 'receipent_phone',
                    'label' => 'LBL_RECEIPENT_PHONE',
                ),
            ),
            array(
                array(
                    'name' => 'receipent_address',
                    'studio' => 'visible',
                    'label' => 'LBL_RECEIPENT_ADDRESS',
                ),
            ),
            array(
                array(
                    'name' => 'amount',
                    'label' => 'LBL_AMOUNT',
                ),
                array(
                    'name' => 'amount_type',
                    'studio' => 'visible',
                    'label' => 'LBL_AMOUNT_TYPE',
                ),
            ),
            array(
                array(
                    'name' => 'description',
                    'comment' => 'Full text of the note',
                    'label' => 'LBL_DESCRIPTION',
                ),
                array(
                    'name' => 'payment_type',
                    'studio' => 'visible',
                    'label' => 'LBL_PAYMENT_TYPE',
                ),
            ),
            array(
                array(
                    'name' => 'pv_notes',
                    'studio' => 'visible',
                    'label' => 'LBL_PV_NOTES',
                ),
            ),
            array(
                array(
                    'name' => 'assigned_user_name',
                    'label' => 'LBL_ASSIGNED_TO_NAME',
                ),
            ),
        ),
    ),
);
