<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = 'OAuth2Clients';

$viewdefs[$module_name]['EditView'] = array(
    'templateMeta' => array(
        'maxColumns' => '1',
        'widths' => array(
            array('label' => '30', 'field' => '70'),
        )
    ),

    'panels' => array(
        'default' => array(
            array(
                array(
                    'name'
                ),
                array(
                    'redirect_url'
                ),
            ),
            array(
                array(
                    'is_confidential'
                ),
                array(
                    'allowed_grant_type'
                ),
            ),
            array(
                array(
                    'name' => 'duration_amount',
                ),
                array(
                    'name' => 'duration_unit',
                ),
            ),
            array(
                array(
                    'name' => 'new_secret',
                                'label' => 'LBL_SECRET_HASHED',
                                'customCode' => '<input type="password" name="new_secret" id="new_secret" placeholder="{$MOD.LBL_LEAVE_BLANK}" size="30">'
                                    . '<br /><span>{$MOD.LBL_REMEMBER_SECRET}</span>',
                ),
                array(),
            ),
        ),
    ),
);
