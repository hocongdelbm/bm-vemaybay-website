<?php


$module_name = '<module_name>';
$viewdefs[$module_name]['QuickCreate'] = array(
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
                    'customCode' => '{html_options name="salutation" options=$fields.salutation.options selected=$fields.salutation.value}&nbsp;<input name="first_name" size="25" maxlength="25" type="text" value="{$fields.first_name.value}">',
                ),
                'assigned_user_name',
            ),

            array(
                array('name' => 'last_name', 'displayParams' => array('required' => true)),
            ),

            array(
                'title',
                'phone_work',
            ),

            array(
                'department',
                'phone_mobile',
            ),

            array(
                'phone_fax',
                '',
            ),
        ),
        'lbl_email_addresses' => array(
            array('email1')
        ),

    )

);
