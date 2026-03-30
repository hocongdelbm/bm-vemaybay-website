<?php
$module_name = 'EC_Bank_Account';
$viewdefs[$module_name]['EditView'] = array(
    'templateMeta' => array(
        'maxColumns' => '2',
        'form' => array(
            'buttons' => array(
                array(
                    'customCode' => '{$CUSTOM_SAVE}',
                ),
                'CANCEL',
            ),
        ),
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30')
        ),
        'includes' => array(
            array(
                'file' => 'modules/EC_Bank_Account/js/EC_Bank_Account.js',
            ),
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
                ),
                array(
                    'name' => 'sort',
                    'label' => 'LBL_SORT',
                ),
            ),

            array(
                array(
                    'name' => 'description',
                    'comment' => 'Full text of the note',
                    'label' => 'LBL_DESCRIPTION',
                    'displayParams' => array(
                        'rows' => 2,
                        'cols' => 45,
                    ),
                ),
                array(
                    'name' => 'branch',
                    'studio' => 'visible',
                    'label' => 'LBL_BRANCH',
                    'displayParams' =>
                    array(
                        'rows' => 2,
                        'cols' => 45,
                    ),
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
                array(
                    'name' => 'is_roll',
                    'label' => 'LBL_IS_ROLL',
                ),
            ),
        ),
    ),
);
