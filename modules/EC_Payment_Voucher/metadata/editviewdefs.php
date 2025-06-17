<?php

$module_name = 'EC_Payment_Voucher';
$viewdefs[$module_name]['EditView'] =
    array(
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
                    'file' => 'modules/EC_Payment_Voucher/js/EC_Payment_Voucher.js',
                ),
            ),
        ),

        'panels' => array(
            'default' =>
            array(
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
                        'name' => 'hinhthucchi',
                        'studio' => 'visible',
                        'label' => 'LBL_HINHTHUCCHI',
                        'customCode' => '{$HINHTHUCCHI}',
                    ),
                    array(
                        'name' => 'payment_type',
                        'studio' => 'visible',
                        'label' => 'LBL_PAYMENT_TYPE',
                    ),
                ),
                array(
                    array(
                        'name' => 'receipent_name',
                        'label' => 'LBL_RECEIPENT_NAME',
                    ),
                    array(
                        'name' => 'supplier',
                        'studio' => 'visible',
                        'label' => 'LBL_SUPPLIER',
                        'customCode' => '{$CUS_SUPPLIER}',
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
                        'name' => 'pv_notes',
                        'studio' => 'visible',
                        'label' => 'LBL_PV_NOTES',
                        'displayParams' =>
                        array(
                            'rows' => 2,
                            'cols' => 45,
                        ),
                    ),
                ),
                array(
                    array(
                        'name' => 'account_name',
                        'label' => 'LBL_ACCOUNT_NAME',
                        'customCode' => '{$CUS_ACCOUNT}',
                    ),
                    array(
                        'name' => 'employee_name',
                        'label' => 'LBL_EMPLOYEE_NAME',
                        'customCode' => '{$EMPLOYEE_NAME}',
                    ),
                ),
                array(
                    array(
                        'name' => 'hoanve',
                        'studio' => 'visible',
                        'label' => 'LBL_HOANVE',
                    ),
                    array(
                        'name' => 'booking',
                        'studio' => 'visible',
                        'label' => 'LBL_BOOKING',
                    ),
                ),
                array(
                    array(
                        'name' => 'phieuthu',
                        'studio' => 'visible',
                        'label' => 'LBL_PHIEUTHU',
                    ),
                    array(
                        'name' => 'debt_name',
                        'studio' => 'visible',
                        'label' => 'LBL_DEBT_NAME',
                    ),
                ),
                array(
                    array(
                        'name' => 'assigned_user_name',
                        'label' => 'LBL_ASSIGNED_TO_NAME',
                    ),
                    array(
                        'name' => 'is_margin',
                        'label' => 'LBL_IS_MARGIN',
                    ),
                ),
                array(
                    array(
                        'name' => 'receipent_address',
                        'studio' => 'visible',
                        'label' => 'LBL_RECEIPENT_ADDRESS',
                        'displayParams' =>
                        array(
                            'rows' => 2,
                            'cols' => 45,
                        ),
                    ),
                    array()
                )
            ),
        ),
    );
