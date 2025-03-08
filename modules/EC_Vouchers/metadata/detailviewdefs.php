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
<<<<<<< HEAD
            array(
                array(
                    'name' => 'quantity',
                    'label' => 'LBL_QUANTITY',
=======
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
>>>>>>> 8201700091c3488d9a9fb900f7efbbfebbcecfff
                ),
                array(
                    'name' => 'duration',
                    'label' => 'LBL_DURATION',
                    'customCode' => '{$DURATION_FIELD}',
                ),
            ),
            array (
                array(
<<<<<<< HEAD
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
=======
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
>>>>>>> 8201700091c3488d9a9fb900f7efbbfebbcecfff
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
<<<<<<< HEAD
        'lbl_panel_description' => array(
=======
        'LBL_BOOKINGS_PANEL' => array(
>>>>>>> 8201700091c3488d9a9fb900f7efbbfebbcecfff
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
