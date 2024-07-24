<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = 'OAuth2Clients';

$viewdefs[$module_name]['EditView'] = [
    'templateMeta' => [
        'maxColumns' => '1',
        'widths' => [
            ['label' => '30', 'field' => '70'],
        ],
        'includes' => [
            [
                'file' => 'modules/OAuth2Clients/js/ClientCredentialsValidation.js'
            ]
        ],
    ],
    'panels' => [
        'default' =>array(
            array(
                array(
                    'name' => 'name',
                ),
                array(
                    'name' => 'is_confidential',
                ),
            ),
            array(
                array(
                    'name' => 'new_secret',
                    'label' => 'LBL_SECRET_HASHED',
                    'customCode' => '<input type="password" name="new_secret" id="new_secret" placeholder="{$MOD.LBL_LEAVE_BLANK}" size="30">'
                        . '<input type="hidden" name="allowed_grant_type" id="allowed_grant_type" value="client_credentials">'
                        . '<br /><span>{$MOD.LBL_REMEMBER_SECRET}</span>',
                ),
                array(
                    'name' => 'assigned_user_name',
                ),
            ),
        ),
    ],
];
