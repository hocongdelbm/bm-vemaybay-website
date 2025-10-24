<?php

$module_name = 'EC_Receipt_Voucher';
$viewdefs[$module_name]['DetailView'] = array(
    'templateMeta' => array(
        'form' => array(
            'buttons' => array(
                'EDIT',
                'DUPLICATE',
                'DELETE',
                array(
                    'customCode' => '{$PRINT_RV}',
                ),
                array(
                    'customCode' => '{$CHANGE_STATUS}',
                ),
                array(
                    'customCode' => '{$ADMIN_CHANGE_STATUS}',
                ),
                array(
                    'customCode' => '{$DEBT}', // nút công nợ
                ),
            )
        ),
        'maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30')
        ),
        'includes' =>
        array(
            array(
                'file' => 'modules/EC_Receipt_Voucher/js/EC_Receipt_Voucher_DV.js',
            ),
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
                    'name' => 'amount',
                    'label' => 'LBL_AMOUNT',
                ),
            ),
            array(
                array(
                    'name' => 'amount_type',
                    'studio' => 'visible',
                    'label' => 'LBL_AMOUNT_TYPE',
                ),
                array(
                    'name' => 'exchange_rate',
                    'label' => 'LBL_EXCHANGE_RATE',
                ),
            ),
            array(
                array(
                    'name' => 'loai_thu',
                    'studio' => 'visible',
                    'label' => 'LBL_LOAI_THU',
                    'customCode' => '{$LOAI_THU}',
                ),
                array(
                    'name' => 'rv_status',
                    'studio' => 'visible',
                    'label' => 'LBL_RV_STATUS',
                    'customCode' => '{$RV_STATUS}',
                ),
            ),
            array(
                array(
                    'name' => 'ngaychungtu',
                    'label' => 'LBL_NGAYCHUNGTU',
                ),
                array(
                    'name' => 'ngayhachtoan',
                    'label' => 'LBL_NGAYHACHTOAN',
                ),
            ),
            array(
                array(
                    'name' => 'receipt_type',
                    'studio' => 'visible',
                    'label' => 'LBL_RECEIPT_TYPE',
                ),
                array(
                    'name' => 'tknganhang',
                    'studio' => 'visible',
                    'label' => 'LBL_TKNGANHANG',
                ),
            ),
            array(
                array(
                    'name' => 'guest_name',
                    'label' => 'LBL_GUEST_NAME',
                ),
                array(
                    'name' => 'guest_phone',
                    'label' => 'LBL_GUEST_PHONE',
                ),
            ),
            array(
                array(
                    'name' => 'com_location',
                    'studio' => 'visible',
                    'label' => 'LBL_COM_LOCATION',
                ),
                array(
                    'name' => 'booking_name',
                    'studio' => 'visible',
                    'label' => 'LBL_BOOKING_NAME',
                ),
            ),
            array(
                array(
                    'name' => 'description',
                    'comment' => 'Full text of the note',
                    'label' => 'LBL_DESCRIPTION',
                ),
                array(
                    'name' => 'rv_notes',
                    'studio' => 'visible',
                    'label' => 'LBL_RV_NOTES',
                ),
            ),
            array(
                array(
                    'name' => 'guest_address',
                    'studio' => 'visible',
                    'label' => 'LBL_GUEST_ADDRESS',
                ),
                array(
                    'name' => 'go_with',
                    'label' => 'LBL_GO_WITH',
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
                array(
                    'name' => 'assigned_user_name',
                    'label' => 'LBL_ASSIGNED_TO_NAME',
                ),
                array(),
            ),
        ),
    ),
);
