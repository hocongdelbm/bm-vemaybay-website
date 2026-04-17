<?php

$module_name = 'EC_Receipt_Voucher';
$viewdefs[$module_name]['EditView'] = array(
    'templateMeta' => array(
        'maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30')
        ),
        'includes' =>
        array(
            array(
                'file' => 'custom/jqueryui/plugins/formatNumber.js',
            ),
            array(
                'file' => 'custom/jqueryui/plugins/jquery.number.min.js',
            ),
            array(
                'file' => 'custom/jqueryui/plugins/fromPopupReturn.js',
            ),
            array(
                'file' => 'modules/EC_Receipt_Voucher/js/view.edit.js',
            ),
        ),
    ),

    'panels' =>  
    array(
        'default' =>
        array(
            array(
                array(
                    'name' => 'amount',
                    'label' => 'LBL_AMOUNT',
                    'customCode' => '{$AMOUNT}',
                ),
                array(
                    'name' => 'amount_type',
                    'label' => 'LBL_AMOUNT_TYPE',
                    'customCode' => '{$AMOUNT_TYPE}',
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
                    'name' => 'loai_thu',
                    'studio' => 'visible',
                    'label' => 'LBL_LOAI_THU',
                    'customCode' => '{$LOAI_THU}',
                ),
                array(
                    'name' => 'receipt_type',
                    'studio' => 'visible',
                    'label' => 'LBL_RECEIPT_TYPE',
                    'customCode' => '{$RECEIPT_TYPE}',
                ),
            ),
            array(
                array(
                    'name' => 'guest_name',
                    'label' => 'LBL_GUEST_NAME',
                ),
                array(
                    'name' => 'booking_name',
                    'studio' => 'visible',
                    'label' => 'LBL_BOOKING_NAME',
                ),
            ),
            array(
                array(
                    'name' => 'guest_phone',
                    'label' => 'LBL_GUEST_PHONE',
                ),
                array(
                    'name' => 'guest_address',
                    'studio' => 'visible',
                    'label' => 'LBL_GUEST_ADDRESS',
                    'displayParams' =>
                    array(
                        'rows' => 2,
                        'cols' => 32,
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
                        'rows' => 3,
                        'cols' => 32,
                    ),
                ),
                array(
                    'name' => 'rv_notes',
                    'studio' => 'visible',
                    'label' => 'LBL_RV_NOTES',
                    'displayParams' =>
                    array(
                        'rows' => 3,
                        'cols' => 32,
                    ),
                ),
            ),
            array(
                array(
                    'name' => 'go_with',
                    'label' => 'LBL_GO_WITH',
                ),
                array(
                    'name' => 'delivery_man',
                    'studio' => 'visible',
                    'label' => 'LBL_DELIVERY_MAN',
                ),
            ),
            array(
                array(
                    'name' => 'assigned_user_name',
                    'label' => 'LBL_ASSIGNED_TO_NAME',
                ),
                array(
                    'name' => 'employee_name',
                    'label' => 'LBL_EMPLOYEE_NAME',
                    'customCode' => '{$EMPLOYEE_NAME}',
                ),
            ),
        ),
    ),
);
