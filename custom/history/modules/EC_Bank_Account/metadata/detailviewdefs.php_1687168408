<?php
$module_name = 'EC_Bank_Account';
$viewdefs[$module_name]['DetailView'] = array(
    'templateMeta' => array(
        'form' => array(
            'buttons' => array(
                'EDIT',
                'DUPLICATE',
                'DELETE',
            )
        ),
        'maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30')
        ),
    ),

    'panels' => array(
        'default' => array(
            array(
                array(
                    'name' => 'account_number',
                    'label' => 'LBL_ACCOUNT_NUMBER',
                ),
                array(
                    'name' => 'account_holder',
                    'label' => 'LBL_ACCOUNT_HOLDER',
                ),
            ),

            array(
                array(
                    'name' => 'name',
                    'label' => 'LBL_NAME',
                ),
                array(
                    'name' => 'bank',
                    'label' => 'LBL_BANK',
                ),
            ),

            array(
                array(
                    'name' => 'account',
                    'label' => 'LBL_ACCOUNT',
                ),
                array(
                    'name' => 'account_holder',
                    'studio' => 'visible',
                    'label' => 'LBL_ACCOUNT_HOLDER',
                ),
            ),

            array(
                array(
                    'name' => 'description',
                    'comment' => 'Full text of the note',
                    'label' => 'LBL_DESCRIPTION',
                ),
                array(
                    'name' => 'sort',
                    'label' => 'LBL_SORT',
                ),
            ),

            array(
                array(
                    'name' => 'is_display',
                    'label' => 'LBL_IS_DISPLAY',
                ),
                array(
                    'name' => 'unfollow',
                    'label' => 'LBL_UNFOLLOW',
                ),
            ),

            array(
                array(
                    'name' => 'is_sms',
                    'label' => 'LBL_IS_SMS',
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
        ),
    )
);
