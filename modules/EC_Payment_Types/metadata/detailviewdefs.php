<?php

$module_name = 'EC_Payment_Types';
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
                'assigned_user_name',
                array(
                    'name' => 'is_receipt_debt',
                    'label' => 'LBL_IS_RECEIPT_DEBT',
                ),
            ),
            array(
                'description',
                array(
                    'name' => 'is_other_amount',
                    'label' => 'LBL_IS_OTHER_AMOUNT',
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
        ),
    ),
);
