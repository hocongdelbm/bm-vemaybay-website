<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = 'OAuth2Tokens';

$viewdefs[$module_name]['DetailView'] = [
    'templateMeta' => [
        'maxColumns' => '1',
        'widths' => [
            ['label' => '30', 'field' => '70'],
        ],
        'form' => [
            'buttons' => array(
            )
        ]
    ],
    'panels' => [
        'default' => array(
            array(
                array(
                    'name' => 'id',
                ),
                array(
                    'name' => 'oauth2client_name',
                ),
            ),
            array(
                array(
                    'name' => 'assigned_user_name',
                ),
                array(
                    'name' => 'token_is_revoked',
                ),
            ),
            array(
                array(
                    'name' => 'token_type',
                ),
                array(
                    'name' => 'access_token_expires',
                ),
            ),
            array(
                array(
                    'name' => 'refresh_token_expires',
                ),
                array(
                    'name' => 'state',
                ),
            ),
            array(
                array(
                    'name' => 'date_entered',
                    'label' => 'LBL_DATE_ENTERED',
                    'customCode' => '{$fields.date_entered.value}',
                ),
                array(
                    'name' => 'date_modified',
                    'label' => 'LBL_DATE_MODIFIED',
                    'customCode' => '{$fields.date_modified.value}',
                ),
            ),
        )
    ],
];
