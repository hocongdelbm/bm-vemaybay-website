<?php
$module_name = 'EC_Vouchers';
$viewdefs[$module_name]['DetailView'] = array(
    'templateMeta' => array(
        'form' => array(
            'buttons' => array(
                'EDIT',
                // 'DUPLICATE',
                'DELETE',
                array(
                    'customCode' => '{$CHANGE_STATUS}',
                ),
            )
        ),
        'maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30')
        ),
        'includes' => array(
            array(
                'file' => 'modules/EC_Vouchers/js/view.detail.js',
            ),
        ),
    ),

    'panels' => array (
        'default' => array(
            array (
                array(
                    'name' => 'name',
                    'label' => 'LBL_NAME',
                ),
                array(
                    'name' => 'campaign_name',
                    'label' => 'LBL_EVENT',
                ),
            ),
            array(
                array(
                    'name' => 'quantity',
                    'label' => 'LBL_QUANTITY',
                ),
                array(
                    'name' => 'duration',
                    'label' => 'LBL_DURATION',
                    'customCode' => '{$CUS_DURATION}',
                ),
            ),
            array (
                array(
                    'name' => 'amount',
                    'label' => 'LBL_AMOUNT',
                    'customCode' => '{$CUS_AMOUNT}',
                ),
                array(
                    'name' => 'max_discount',
                    'label' => 'LBL_MAX_DISCOUNT',
                ),
            ),
            array (
                array(
                    'name' => 'status',
                    'label' => 'LBL_STATUS',
                    'customCode' => '{$CUS_STATUS}',
                ),
                'applied_date',
            ),
            array (
                array(
                    'name' => 'account',
                    'label' => 'LBL_ACCOUNT',
                    'customCode' => '{$CUS_ACCOUNT}',
                ),
                array(
                    'name' => 'booking',
                    'label' => 'LBL_BOOKING',
                    'customCode' => '{$CUS_BOOKING}',
                ),
            ),
            array (
                  array (
                    'name' => 'date_entered',
                    'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}',
                    'label' => 'LBL_DATE_ENTERED',
                ),
                array (
                    'name' => 'date_modified',
                    'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}',
                    'label' => 'LBL_DATE_MODIFIED',
                ),
            ),
        ),
        'lbl_panel_description' => array(
            array (
                array (
                    'name' => 'condition_voucher',
                    'label' => 'LBL_CONDITION_VOUCHER',
                    'customCode' => '{$CUS_CONDITION_VOUCHER}',
                )
            ),
            array (
                'description',
            ),
        )
    ),
);
