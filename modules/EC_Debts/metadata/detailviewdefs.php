<?php
$module_name = 'EC_Debts';
$viewdefs[$module_name]['DetailView'] = array(
    'templateMeta' => array(
        'form' => array(
            'buttons' => array(
                'EDIT',
                'DUPLICATE',
                'DELETE',
                array('customCode' => '{$CREATE_VOUCHER}'),

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
                array(
                    'name' => 'name',
                    'label' => 'LBL_NAME',
                ),
                array(
                    'name' => 'debt_status',
                    'studio' => 'visible',
                    'label' => 'LBL_DEBT_STATUS',
                ),
            ),
            array(
                array(
                    'name' => 'debt_type',
                    'studio' => 'visible',
                    'label' => 'LBL_DEBT_TYPE',
                ),
            ),
            array(
                array(
                    'name' => 'debt_amount',
                    'label' => 'LBL_DEBT_AMOUNT',
                ),
                array(
                    'name' => 'remain_amount',
                    'label' => 'LBL_REMAIN_AMOUNT',
                ),
            ),
            array(
                array(
                    'name' => 'paid_amount',
                    'label' => 'LBL_PAID_AMOUNT',
                ),
                array(
                    'name' => 'booking',
                    'studio' => 'visible',
                    'label' => 'LBL_BOOKING',
                ),
            ),
            array(
                array(
                    'name' => 'supplier',
                    'studio' => 'visible',
                    'label' => 'LBL_SUPPLIER',
                ),
                array(
                    'name' => 'date_limit',
                    'label' => 'LBL_DATE_LIMIT',
                ),
            ),
            array(
                array (
                    'name' => 'ngayhachtoan',
                    'label' => 'LBL_NGAYHACHTOAN',
                  ),
                  array()
            ),
            array(
                array(
                    'name' => 'contact_name',
                    'label' => 'LBL_CONTACT_NAME',
                ),
                array(
                    'name' => 'phone_office',
                    'label' => 'LBL_PHONE_OFFICE',
                ),
            ),
            array(
                array(
                    'name' => 'phone_fax',
                    'label' => 'LBL_PHONE_FAX',
                ),
                array(
                    'name' => 'phone_mobile',
                    'label' => 'LBL_PHONE_MOBILE',
                ),
            ),
            array(
                array(
                    'name' => 'email',
                    'label' => 'LBL_EMAIL',
                ),
                array(
                    'name' => 'address',
                    'studio' => 'visible',
                    'label' => 'LBL_ADDRESS',
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
                    'name' => 'description',
                    'comment' => 'Full text of the note',
                    'label' => 'LBL_DESCRIPTION',
                ),
                array(
                    'name' => 'assigned_user_name',
                    'label' => 'LBL_ASSIGNED_TO_NAME',
                ),
            ),
        ),
    ),
);
