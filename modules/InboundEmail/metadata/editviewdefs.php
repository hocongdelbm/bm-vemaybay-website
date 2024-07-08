    <?php

    $viewdefs['InboundEmail'] = [
        'EditView' => [
            'templateMeta' => [
                'form' => [
                    'hidden' => [
                        '<input type="hidden" name="searchField" value="">',
                        '<input type="hidden" id="origin_id" name="origin_id" value="{$smarty.request.origin_id}">',
                    ],
                    'buttons' => [
                        'SAVE',
                        'CANCEL',
                        [
                            'customCode' => '
                            <input title="{$MOD.LBL_TEST_BUTTON_TITLE}"
                                   type="button"
                                   class="btn btn-info"
                                   id="emailTestSettings"
                                   onClick="testInboundConfiguration()"
                                   name="button" value="{$MOD.LBL_TEST_CONNECTION_SETTINGS}" />
                        ',
                        ]
                    ]
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
                'javascript' => '
                {sugar_getscript file="modules/InboundEmail/InboundEmail.js"}
                <script type="text/javascript">
                    {literal}var userService = function() { return { isAdmin: function() { return {/literal}{if $is_admin}true{else}false{/if}{literal};}}}();{/literal}
                    {suite_combinescripts
                        files="modules/InboundEmail/js/fields.js,
                               modules/InboundEmail/js/case_create_toggle.js,
                               modules/InboundEmail/js/distribution_toggle.js,
                               modules/InboundEmail/js/mail_folders.js,
                               modules/InboundEmail/js/ssl_port_set.js,
                               modules/InboundEmail/js/fields_toggle.js,
                               modules/InboundEmail/js/auth_type_fields_toggle.js,
                               modules/InboundEmail/js/owner_toggle.js,
                               modules/InboundEmail/js/test_configuration.js,
                               modules/InboundEmail/js/panel_toggle.js"}
                </script>
            '
            ],
            'panels' => [
                'default' => [
                    [
                        'type',
                        'owner_name',

                    ],
                    [
                        'name',
                        'status'
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
                        'email_password'
                    ],
                    [
                        'port',
                        [
                            'name' => 'mailbox',
                            'vname' => 'LBL_MAILBOX',
                            'customCode' => '<div class="d-flex align-items-center gap-2"><input id="mailbox" name="mailbox" tabindex="90" size="30" maxlength="500" type="text" value="{$fields.mailbox.value}"/> <input type="button" id="subscribeFolderButton" class="btn btn-primary" onclick="openMailboxPopup()" value="{$MOD.LBL_SELECT}"/></div>',
                        ]
                    ],
                    [
                        'is_ssl',
                        [
                            'name' => 'trashFolder',
                            'customCode' => '<div class="d-flex align-items-center gap-2"><input name="trashFolder" id="trashFolder" tabindex="92" value="{$fields.trashFolder.value}" size=\'30\' maxlength=\'100\' type="text"/> <input type="button" id="trashFolderButton" class="btn btn-primary" onclick="openTrashMailboxPopup()" value="{$MOD.LBL_SELECT}"/></div>',
                        ]
                    ],
                    [
                        'connection_string',
                        [
                            'name' => 'sentFolder',

                            'customCode' => '<div class="d-flex align-items-center gap-2"><input id="sentFolder" name="sentFolder" tabindex="95" size="30" maxlength="100" type="text" value="{$fields.sentFolder.value}"/> <input type="button" id="sentFolderButton" class="btn btn-primary" onclick="openSentMailboxPopup()" value="{$MOD.LBL_SELECT}"/></div>',
                        ]
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
                        'move_messages_to_trash_after_import'
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
                ],
            ],
        ],
    ];
