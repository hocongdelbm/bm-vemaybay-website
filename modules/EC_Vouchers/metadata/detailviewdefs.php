<?php
$module_name = 'EC_Vouchers';
$viewdefs[$module_name]['DetailView'] = array(
    'templateMeta' => array(
        'form' => array(
            'buttons' => array(
                // 'EDIT',
                // 'DUPLICATE',
                'DELETE',
                array('customCode' => '{$ACTIVE_BUTTON}'),
            )
        ),
        'maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30')
        ),
        'includes' => array(
            array('file' => 'modules/EC_Vouchers/js/view.detail.js'),
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
            array (
                array(
                    'name' => 'name',
                    'label' => 'LBL_NAME',
                    'customCode' => '{$CODE_FIELD}',
                ),
                array(
                    'name' => 'status',
                    'label' => 'LBL_STATUS',
                    'customCode' => '{$STATUS_FIELD}',
                ),
            ),
            array (
                array(
                    'name' => 'discount',
                    'label' => 'LBL_DISCOUNT',
                    'customCode' => '{$DISCOUNT_FIELD}',
                ),
                array(
                    'name' => 'duration',
                    'label' => 'LBL_DURATION',
                    'customCode' => '{$DURATION_FIELD}',
                ),
            ),
            array (
                array(
                    'name' => 'max_discount',
                    'label' => 'LBL_MAX_DISCOUNT',
                    'customCode' => '{$MAX_DISCOUNT_FIELD}',
                ),
                array(
                    'name' => 'quantity',
                    'label' => 'LBL_QUANTITY',
                ),
            ),
            array (
                array(
                    'name' => 'website',
                    'label' => 'LBL_WEBSITE',
                    'customCode' => '{$WEBSITE_FIELD}',
                ),
                array(
                    'name' => 'contact_name',
                    'label' => 'LBL_CONTACT',
                ),
            ),
            array (
                array(
                    'name' => 'condition_voucher',
                    'label' => 'LBL_CONDITION_VOUCHER',
                    'customCode' => '{$CONDITION_VOUCHER_FIELD}',
                ),
                array(
                    'name' => 'description',
                    'label' => 'LBL_DESCRIPTION',
                ),
            ),
            array(
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
        'LBL_BOOKINGS_PANEL' => array(
            array (
                array (
                    'name' => 'BOOKINGS',
                    'hideLabel' => true,
                    'colspan' => 4,
                    'customCode' => '{$BOOKINGS}',
                )
            ),
            array (
                'description',
            ),
        )
    ),
);
