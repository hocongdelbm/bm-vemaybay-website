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
            array(
                array(
                    'name' => 'campaign_name',
                    'label' => 'LBL_CAMPAIGN_NAME',
                ),
            ),
            array (
                'name',
                'status',
            ),
            array (
                'reduce_amount',
                array(
                    'name' => 'duration',
                    'label' => 'LBL_DURATION',
                    'customCode' => '{$CUS_DURATION}',
                ),
            ),
            array (
                'account_name',
                'account_phone',
            ),
            array (
                'account_address',
                'account_email',
            ),
            array (
                array(
                    'name' => 'booking',
                    'label' => 'LBL_BOOKING',
                    'customCode' => '{$CUS_BOOKING}',
                ),
                array(
                    'name' => 'journey',
                    'label' => 'LBL_JOURNEY',
                    'customCode' => '{$CUS_JOURNEY}',
                ),
            ),
            array (
                'active_date',
                'applied_date'
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
                'description',
            ),
            array (
                array (
                    'name' => 'condition_voucher',
                    'label' => 'LBL_CONDITION_VOUCHER',
                    'customCode' => '{$CUS_CONDITION_VOUCHER}',
                )
            ),
        )
    ),
);
