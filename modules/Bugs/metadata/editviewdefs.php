<?php

$viewdefs['Bugs']['EditView'] = array(
    'templateMeta' => array(
        'form' => array(
            'hidden' => array(
                '<input type="hidden" name="account_id" value="{$smarty.request.account_id}">',
                '<input type="hidden" name="contact_id" value="{$smarty.request.contact_id}">'
            )
        ),
        'maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30')
        ),
        'includes' => array(
            array(
                'file' => 'modules/Bugs/js/view.edit.js',
            ),
        ),
    ),


    'panels' => array(
        'lbl_bug_information' =>
        array(

            array(
                array(
                    'name' => 'bug_number',
                    'type' => 'readonly',
                ),
            ),

            array(
                array('name' => 'name', 'displayParams' => array('size' => 60, 'required' => true)),
                array(
                    'name' => 'assigned_user_name',
                    'label' => 'LBL_ASSIGNED_TO_NAME',
                ),
            ),

            array(
                'priority',
                'type',
            ),

            array(
                'source',
                'status',

            ),

            array(
                'product_category',
                'resolution',
            ),


            // array(
            //     'found_in_release',
            //     'fixed_in_release'
            // ),

            array(
                array(
                    'name' => 'description',
                    'nl2br' => true,
                ),
            ),

            array(
                array(
                    'name' => 'photo',
                ),
                array(
                    'name' => 'photo_sub',
                ),
            ),


            array(
                array(
                    'name' => 'work_log',
                    'nl2br' => true,
                ),
            ),

        ),
    ),
);
