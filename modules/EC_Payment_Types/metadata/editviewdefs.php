<?php

$module_name = 'EC_Payment_Types';
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
                    'name' => 'name',
                    'label' => 'LBL_NAME',
                ),
                array(
                    'name' => 'is_report',
                    'label' => 'LBL_IS_REPORT',
                ),
            ),
            array(
                array(
                    'name' => 'aircode',
                    'studio' => 'visible',
                    'label' => 'LBL_AIRCODE',
                ),
                array(
                    'name' => 'is_payment_debt',
                    'label' => 'LBL_IS_PAYMENT_DEBT',
                ),
            ),
            array(
                array(
                    'name' => 'assigned_user_name',
                    'label' => 'LBL_ASSIGNED_TO_NAME',
                ),
                array(
                    'name' => 'is_receipt_debt',
                    'label' => 'LBL_IS_RECEIPT_DEBT',
                ),
            ),
            array(
                array(
                    'name' => 'description',
                    'comment' => 'Full text of the note',
                    'label' => 'LBL_DESCRIPTION',
                ),
                array(
                    'name' => 'is_other_amount',
                    'label' => 'LBL_IS_OTHER_AMOUNT',
                ),
            ),
        ),
    ),
);
