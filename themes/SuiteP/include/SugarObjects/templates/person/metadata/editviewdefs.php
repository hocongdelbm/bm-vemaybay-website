<?php


$module_name = '<module_name>';
$viewdefs[$module_name]['EditView'] = array(
    'templateMeta' => array(
        'maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30'),
        ),
    ),
    'panels' => array(
        'lbl_contact_information' => array(

            array(
                array(
                    'name' => 'first_name',
                    'customCode' => '{html_options name="salutation" id="salutation" options=$fields.salutation.options selected=$fields.salutation.value}' .
                        '&nbsp;<input name="first_name"  id="first_name" size="25" maxlength="25" type="text" value="{$fields.first_name.value}">',
                ),
                'phone_work',
            ),

            array(
                'last_name',
                'phone_mobile',
            ),

            array(
                'title',
                'phone_home',
            ),


            array(
                'department',
                'phone_other',
            ),

            array(
                '',
                'phone_fax',
            ),

            array(
                'assigned_user_name',
                'do_not_call',
            ),

            array(
                'description',
            ),

        ),

        'lbl_email_addresses' => array(
            array(
                'email1'
            ),
        ),
        'lbl_address_information' => array(
            array(
                array(
                    'name' => 'primary_address_street',
                    'hideLabel' => true,
                    'type' => 'address',
                    'displayParams' => array('key' => 'primary', 'rows' => 2, 'cols' => 30, 'maxlength' => 150),
                ),

                array(
                    'name' => 'alt_address_street',
                    'hideLabel' => true,
                    'type' => 'address',
                    'displayParams' => array(
                        'key' => 'alt',
                        'copy' => 'primary',
                        'rows' => 2,
                        'cols' => 30,
                        'maxlength' => 150
                    ),
                ),
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
