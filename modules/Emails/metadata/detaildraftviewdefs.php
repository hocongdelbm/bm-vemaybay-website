<?php

$module_name = 'Emails';
$viewdefs[$module_name]['DetailView'] = array(
    'templateMeta' => array(
        'form' => array(
            'buttons' => array(
                0 => array(
                    'customCode' =>  '<button class="button btn-edit-drafts" title="">{$MOD.LBL_BUTTON_EDIT_EDIT_DRAFT}</button>'
                ),
                'DELETE',
                array(
                    'customCode' =>  '<button class="button btn-edit-drafts" title="">{$MOD.LBL_SEND_BUTTON_LABEL}</button>'
                ),
            )
        ),
        'includes' => array(
            array(
                'file' => 'modules/Emails/include/DetailView/edit-draft.js',
            ),
        ),
        'maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30')
        ),
    ),

    'panels' => array(

        'LBL_EMAIL_INFORMATION' => array(
            array(
                'from_addr_name' => array(
                    'name' => 'from_addr_name',
                    'label' => 'LBL_FROM',
                ),
            ),
            array(
                'to_addrs_names' => array(
                    'name' => 'to_addrs_names',
                    'label' => 'LBL_TO',
                ),
            ),
            array(
                'cc_addrs_names' => array(
                    'name' => 'cc_addrs_names',
                    'label' => 'LBL_CC',
                ),
            ),
            array(
                'bcc_addrs_names' => array(
                    'name' => 'bcc_addrs_names',
                    'label' => 'LBL_BCC',
                ),
            ),
            array(
                'name' => array(
                    'name' => 'name',
                    'label' => 'LBL_SUBJECT',
                ),
            ),
            array(
                'description' => array(
                    'name' => 'description_html',
                    'label' => 'LBL_BODY'
                ),
            ),
            array(
                'parent_name'
            ),
            array(
                'date_entered' => array(
                    'name' => 'date_entered',
                    'label' => 'LBL_DATE_ENTERED',
                )
            ),
            array(
                'category_id',
            ),
        )
    )
);
