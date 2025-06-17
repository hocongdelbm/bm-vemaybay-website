<?php

$module_name = 'Emails';
$viewdefs[$module_name]['DetailView'] = array(
    'templateMeta' => array(
        'form' => array(
            'buttons' => array(
                'EDIT',
                'DUPLICATE',
                'DELETE',
                [
                    'customCode' => '<input type=button class="btn btn-danger btn-DeleteFromImap" onclick="window.location.href=\'index.php?module=Emails&action=DeleteFromImap&folder=INBOX.TestInbox&folder=inbound&inbound_email_record={$bean->inbound_email_record}&uid={$bean->uid}&msgno={$bean->msgNo}&record={$bean->id}&return_module=Emails&return_action=index\';" value="{$MOD.LBL_BUTTON_DELETE_IMAP}">'
                ],
                array(
                    'customCode' => '<input type=button class="btn btn-secondary btn-ReplyTo" onclick="window.location.href=\'index.php?module=Emails&action=ReplyTo&return_module=Emails&return_action=index&folder=INBOX.TestInbox&folder=inbound&inbound_email_record={$bean->inbound_email_record}&uid={$bean->uid}&msgno={$bean->msgno}&record={$bean->id}&return_module=Emails&return_action=index\';" value="{$MOD.LBL_BUTTON_REPLY_TITLE}">'
                ),
                array(
                    'customCode' => '<input type=button class="btn btn-secondary btn-ReplyToAll" onclick="window.location.href=\'index.php?module=Emails&action=ReplyToAll&return_module=Emails&return_action=index&folder=INBOX.TestInbox&folder=inbound&inbound_email_record={$bean->inbound_email_record}&uid={$bean->uid}&msgno={$bean->msgno}&record={$bean->id}&return_module=Emails&return_action=index\';" value="{$MOD.LBL_BUTTON_REPLY_ALL}">'
                ),
                array(
                    'customCode' => '<input type=button class="btn btn-secondary btn-Forward" onclick="window.location.href=\'index.php?module=Emails&action=Forward&return_module=Emails&return_action=index&folder=INBOX.TestInbox&folder=inbound&inbound_email_record={$bean->inbound_email_record}&uid={$bean->uid}&msgno={$bean->msgno}&record={$bean->id}&return_module=Emails&return_action=index\';" value="{$MOD.LBL_BUTTON_FORWARD}">'
                ),
                array(
                    'customCode' => '<input type=button onclick="openQuickCreateModal(\'Bugs\',\'&name={$bean->name}\',\'{$bean->from_addr_name}\');" value="{$MOD.LBL_CREATE} {$APP.LBL_EMAIL_QC_BUGS}">'
                        . '<input type="hidden" id="parentEmailId" name="parentEmailId" value="{$bean->id}">'
                ),
                array(
                    'customCode' => '<input type=button onclick="openQuickCreateModal(\'Cases\',\'&name={$bean->name}\',\'{$bean->from_addr_name}\');" value="{$MOD.LBL_CREATE} {$APP.LBL_EMAIL_QC_CASES}">'
                        . '<input type="hidden" id="parentEmailId" name="parentEmailId" value="{$bean->id}">'
                ),
                array(
                    'customCode' => '<input type=button onclick="openQuickCreateModal(\'Contacts\',\'&last_name={$bean->name}\',\'{$bean->from_addr_name}\');" value="{$MOD.LBL_CREATE} {$APP.LBL_EMAIL_QC_CONTACTS}">'
                        . '<input type="hidden" id="parentEmailId" name="parentEmailId" value="{$bean->id}">'
                ),
                array(
                    'customCode' => '<input type=button onclick="openQuickCreateModal(\'Leads\',\'&last_name={$bean->name}\',\'{$bean->from_addr_name}\');" value="{$MOD.LBL_CREATE} {$APP.LBL_EMAIL_QC_LEADS}">'
                        . '<input type="hidden" id="parentEmailId" name="parentEmailId" value="{$bean->id}">'
                ),
                array(
                    'customCode' => '<input type=button onclick="openQuickCreateModal(\'Opportunities\',\'&name={$bean->name}\',\'{$bean->from_addr_name}\');" value="{$MOD.LBL_CREATE} {$APP.LBL_EMAIL_QC_OPPORTUNITIES}">'
                        . '<input type="hidden" id="parentEmailId" name="parentEmailId" value="{$bean->id}">'
                ),
            )
        ),
        'includes' => array(
            array(
                'file' => 'modules/Emails/include/DetailView/quickCreateModal.js',
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
                'opt_in' => array(
                    'name' => 'opt_in',
                    'label' => 'LBL_OPT_IN',
                ),
            ),
            [
                'date_sent_received' => [
                    'name' => 'date_sent_received',
                    'vname' => 'date_sent_received',
                    'label' => 'LBL_DATE_SENT_RECEIVED',
                ],
            ],
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
            [
                'parent_name'
            ],
            [
                'date_entered' => [
                    'name' => 'date_entered',
                    'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}',
                    'label' => 'LBL_DATE_ENTERED',
                ]
            ],
            [
                'date_modified' => [
                    'name' => 'date_modified',
                    'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}',
                    'label' => 'LBL_DATE_MODIFIED',
                ]
            ],
            [
                'category_id',
            ],
        )
    )
);
