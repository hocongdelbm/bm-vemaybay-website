<?php


$module_name = '<module_name>';
$viewdefs[$module_name]['DetailView'] = array(
    'templateMeta' => array(
        'form' => array(
            'buttons' => array(
                'EDIT',
                'DUPLICATE',
                'DELETE',
                'FIND_DUPLICATES',
            ),
        ),
        'maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30')
        ),
    ),
    'panels' => array(
        'lbl_contact_information' => array(
            array(
                array(
                    'name' => 'full_name',
                    'label' => 'LBL_NAME',
                ),
                array(
                    'name' => 'phone_work',
                ),
            ),

            array(
                'title',
                array(
                    'name' => 'phone_mobile',
                ),
            ),

            array(
                'department',

                array(
                    'name' => 'phone_home',
                    'label' => 'LBL_HOME_PHONE',
                ),
            ),

            array(
                null,
                array(
                    'name' => 'phone_other',
                    'label' => 'LBL_OTHER_PHONE',
                ),
            ),
            array(
                array(
                    'name' => 'date_entered',
                    'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}',
                    'label' => 'LBL_DATE_ENTERED',
                ),
                array(
                    'name' => 'phone_fax',
                    'label' => 'LBL_FAX_PHONE',
                ),
            ),

            array(
                array(
                    'name' => 'date_modified',
                    'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}',
                    'label' => 'LBL_DATE_MODIFIED',
                ),
                'do_not_call',
            ),
            array('assigned_user_name', ''),



            array(
                'email1'
            ),

            array(
                array(
                    'name' => 'primary_address_street',
                    'label' => 'LBL_PRIMARY_ADDRESS',
                    'type' => 'address',
                    'displayParams' => array('key' => 'primary'),
                ),
                array(
                    'name' => 'alt_address_street',
                    'label' => 'LBL_ALT_ADDRESS',
                    'type' => 'address',
                    'displayParams' => array('key' => 'alt'),
                ),
            ),

            array(
                'description',
            ),
        ),

        'lbl_consent' => array(
            array(
                array(
                    'name' => 'lawful_basis',
                    'label' => 'LBL_LAWFUL_BASIS',
                ),
                array(
                    'name' => 'lawful_basis_source',
                    'label' => 'LBL_LAWFUL_BASIS_SOURCE',
                ),
            ),
            array(
                array(
                    'name' => 'date_reviewed',
                    'label' => 'LBL_DATE_REVIEWED',
                ),
                null,
            ),
        ),
    )
);
