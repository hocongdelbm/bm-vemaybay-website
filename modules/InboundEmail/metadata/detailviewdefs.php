<?php

$viewdefs['InboundEmail'] = [
    'DetailView' => [
        'templateMeta' => [
            'form' => [
                'buttons' => [
                    'EDIT',
                    'DELETE',
                    [
                        'customCode' => '
                            {if $fields.type.value === "personal" && $fields.created_by.value === $current_user_id}
                            <input title="{$MOD.LBL_SET_AS_DEFAULT_BUTTON}"
                                   type="button"
                                   class="btn btn-secondary btn-default"
                                   id="set-as-default-inbound"
                                   onClick="document.location.href=\'index.php?module=InboundEmail&action=SetDefault&record={$fields.id.value}&return_module=InboundEmail&return_action=DetailView&return_id={$fields.id.value}\';"
                                   name="button" value="{$MOD.LBL_SET_AS_DEFAULT_BUTTON}" />
                           {/if}
                        '
                    ]
                ],
            ],
            'maxColumns' => '2',
            'widths' => [
                [
                    'label' => '10',
                    'field' => '30',
                ],
                [
                    'label' => '10',
                    'field' => '30',
                ],
            ],
            'useTabs' => false,
            'tabDefs' => [
                'DEFAULT' => [
                    'newTab' => false,
                    'panelDefault' => 'expanded',
                ],
                'LBL_CONNECTION_CONFIGURATION' => [
                    'newTab' => false,
                    'panelDefault' => 'expanded',
                ],
                'LBL_OUTBOUND_CONFIGURATION' => [
                    'newTab' => false,
                    'panelDefault' => 'expanded',
                ],
                'LBL_AUTO_REPLY_CONFIGURATION' => [
                    'newTab' => false,
                    'panelDefault' => 'expanded',
                ],
                'LBL_GROUP_CONFIGURATION' => [
                    'newTab' => false,
                    'panelDefault' => 'expanded',
                ],
                'LBL_CASE_CONFIGURATION' => [
                    'newTab' => false,
                    'panelDefault' => 'expanded',
                ],
            ],
            'preForm' => '
                {sugar_getscript file="modules/InboundEmail/InboundEmail.js"}
                <script type="text/javascript">
                    {literal}var userService = function() { return { isAdmin: function() { return {/literal}{if $is_admin}true{else}false{/if}{literal};}}}();{/literal}
                    {suite_combinescripts
                        files="modules/InboundEmail/js/fields.js,
                               modules/InboundEmail/js/case_create_toggle.js,
                               modules/InboundEmail/js/distribution_toggle.js,
                               modules/InboundEmail/js/mail_folders.js,
                               modules/InboundEmail/js/owner_toggle.js,
                               modules/InboundEmail/js/fields_toggle.js,
                               modules/InboundEmail/js/auth_type_fields_toggle.js,
                               modules/InboundEmail/js/panel_toggle.js"}
                </script>
            '
        ],
        'panels' => [
            'default' => [
                [
                    'name',
                    'is_default'
                ],
                [
                    'type',
                    'status'
                ],
                [
                    'owner_name',
                ],
            ],
            'lbl_connection_configuration' => [
                [
                    'auth_type',
                    'external_oauth_connection_name',
                ],
                [
                    'server_url',
                    'email_user'
                ],
                [
                    'protocol',
                    ''
                ],
                [
                    'port',
                    'mailbox'
                ],
                [
                    'is_ssl',
                    'trashFolder',
                ],
                [
                    'connection_string',
                    'sentFolder'
                ],

            ],
            'lbl_outbound_configuration' => [
                [
                    'outbound_email_name',
                    'account_signature_id'
                ],
                [
                    'allow_outbound_group_usage',
                ],
                [
                    'from_name',
                    'reply_to_name',
                ],
                [
                    'from_addr',
                    'reply_to_addr'
                ],
            ],
            'lbl_auto_reply_configuration' => [
                [
                    'filter_domain',
                    'autoreply_email_template_name'
                ],
                [
                    'email_num_autoreplies_24_hours',
                    ''
                ],
            ],
            'lbl_group_configuration' => [
                [
                    'is_auto_import',
                    'move_messages_to_trash_after_import',
                ],
            ],
            'lbl_case_configuration' => [
                [
                    'is_create_case',
                ],
                [
                    'create_case_email_template_name',
                    'distrib_method',
                ],
                [
                    '',
                    'distribution_options'
                ],
                [
                    '',
                    'distribution_user_name'
                ]
            ]
        ],
    ],
];
