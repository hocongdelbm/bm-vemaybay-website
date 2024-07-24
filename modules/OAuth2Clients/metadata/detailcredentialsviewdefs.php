<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = 'OAuth2Clients';

$viewdefs[$module_name]['DetailView'] = [
    'templateMeta' => [
        'maxColumns' => '1',
        'widths' => [
            ['label' => '30', 'field' => '70'],
        ],
    ],
    'panels' => [
        'default' =>  array(
            array(
                array(
                    'name' => 'name',
                ),
                array(
                    'name' => 'id',
                ),
            ),
            array(
                array(
                    'name' => 'assigned_user_name',
                ),
                array(
                    'name' => 'is_confidential',
                ),
            ),
            array(
                array(
                    'name' => 'allowed_grant_type',
                ),
            ),
            array(
                array(
                    'name' => 'date_entered',
                    'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}',
                ),
                array(
                    'name' => 'date_modified',
                    'label' => 'LBL_DATE_MODIFIED',
                    'customCode' => '{$fields.date_modified.value} ' . '{$APP.LBL_BY} ' . '{$fields.modified_by_name.value}',
                ),
            ),
        ),
    ], 
];
