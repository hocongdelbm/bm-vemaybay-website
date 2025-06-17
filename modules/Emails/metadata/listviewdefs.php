<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
$viewdefs['Emails']['ListView'] = array(
    'templateMeta' => array(
        'form' => array(
            'buttons' =>
            array(
                array(
                    'customCode' => '<a class="btn" data-action="emails-show-compose-modal" title="{$MOD.LBL_COMPOSEEMAIL}"><span class="glyphicon glyphicon-envelope"></span></a>'
                ),
                array(
                    'customCode' => '<a class="btn" data-action="emails-configure" title="{$MOD.LBL_EMAILSETTINGS}"><span class="glyphicon glyphicon-cog"></span></a>'
                ),
                array(
                    'customCode' => '<a class="btn" data-action="emails-check-new-email" title="{$MOD.LBL_BUTTON_CHECK_TITLE}"><span class="glyphicon glyphicon-refresh"></span></a>'
                ),
                array(
                    'customCode' => '<a class="btn" data-action="emails-show-folders-modal" title="{$MOD.LBL_SELECT_FOLDER}"><span class="glyphicon glyphicon-folder-open"></span></a>'
                ),
            ),
            'actions' => array(
                array(
                    'customCode' => '<a href="javascript:void(0)" class="parent-dropdown-handler" id="delete_listview_top" onclick="return false;"><label class="selected-actions-label hidden-mobile">{$APP.LBL_BULK_ACTION_BUTTON_LABEL_MOBILE}<span class=\'suitepicon suitepicon-action-caret\'></span></label><label class="selected-actions-label hidden-desktop">{$APP.LBL_BULK_ACTION_BUTTON_LABEL}</label></a>',
                ),
                array(
                    'customCode' => '<a data-action="emails-import-multiple" title="{$MOD.LBL_IMPORT}">{$MOD.LBL_IMPORT}</a>'
                ),
                array(
                    'customCode' => '<a data-action="emails-delete-multiple" title="{$MOD.LBL_BUTTON_DELETE_IMAP}">{$MOD.LBL_BUTTON_DELETE_IMAP}</a>'
                ),
                array(
                    'customCode' => '<a data-action="emails-mark" data-for="unread" title="{$MOD.LBL_MARK_UNREAD}">{$MOD.LBL_MARK_UNREAD}</a>',
                ),
                array(
                    'customCode' => '<a data-action="emails-mark" data-for="read" title="{$MOD.LBL_MARK_READ}">{$MOD.LBL_MARK_READ}</a>',
                ),
                array(
                    'customCode' => '<a data-action="emails-mark" data-for="flagged" title="{$MOD.LBL_MARK_FLAGGED}">{$MOD.LBL_MARK_FLAGGED}</a>',
                ),
                array(
                    'customCode' => '<a data-action="emails-mark" data-for="unflagged" title="{$MOD.LBL_MARK_UNFLAGGED}">{$MOD.LBL_MARK_UNFLAGGED}</a>',
                ),
            ),
            'headerTpl' => 'modules/Emails/include/ListView/ListViewHeader.tpl',
        ),
        'includes' => array(
            array(
                'file' => 'include/javascript/jstree/dist/jstree.js',
            ),
            array(
                'file' => 'modules/Emails/include/ListView/ComposeViewModal.js',
            ),
            array(
                'file' => 'modules/Emails/include/ListView/SettingsView.js',
            ),
            array(
                'file' => 'modules/Emails/include/ListView/CheckNewEmails.js',
            ),
            array(
                'file' => 'modules/Emails/include/ListView/FoldersViewModal.js',
            ),
            array(
                'file' => 'modules/Emails/include/ListView/ListViewHeader.js',
            ),
            array(
                'file' => 'modules/Emails/include/DetailView/ImportView.js',
            ),
            array(
                'file' => 'modules/Emails/include/ListView/ImportEmailAction.js',
            ),
            array(
                'file' => 'modules/Emails/include/ListView/MarkEmails.js',
            ),
            [
                'file' => 'modules/Emails/include/ListView/DeleteEmailAction.js',
            ],
        ),
        'options' => array(
            'hide_edit_link' => true
        )
    )
);

$listViewDefs['Emails'] = array(
    'FROM_ADDR_NAME' => array(
        'width' => '32',
        'label' => 'LBL_LIST_FROM_ADDR',
        'default' => true,
        'sortable' => false,
    ),
    'INDICATOR' => array(
        'width' => '32',
        'label' => 'LBL_INDICATOR',
        'default' => true,
        'sortable' => false,
        'hide_header_label' => true,
    ),
    'SUBJECT' => array(
        // Uses function field
        'width' => '32',
        'label' => 'LBL_LIST_SUBJECT',
        'default' => true,
        'link' => false,
        'customCode' => '',
        'sortable' => false,
    ),
    'HAS_ATTACHMENT' => array(
        'width' => '32',
        'label' => 'LBL_HAS_ATTACHMENT_INDICATOR',
        'default' => false,
        'sortable' => false,
        'hide_header_label' => true,
    ),
    'ASSIGNED_USER_NAME' => array(
        'width' => '9',
        'label' => 'LBL_ASSIGNED_TO_NAME',
        'module' => 'Employees',
        'id' => 'ASSIGNED_USER_ID',
        'default' => false,
        'sortable' => false,
    ),
    'DATE_ENTERED' => array(
        'width' => '32',
        'label' => 'LBL_DATE_ENTERED',
        'default' => false,
        'sortable' => false,
    ),
    'DATE_SENT_RECEIVED' => array(
        'width' => '32',
        'label' => 'LBL_LIST_DATE_SENT_RECEIVED',
        'default' => true,
        'sortable' => false,
        'force_show_sort_direction' => true
    ),
    'TO_ADDRS_NAMES' => array(
        'width' => '32',
        'label' => 'LBL_LIST_TO_ADDR',
        'default' => false,
        'sortable' => false,
    ),
    'CATEGORY_ID' => array(
        'width' => '10%',
        'label' => 'LBL_LIST_CATEGORY',
        'default' => false,
        'sortable' => false,
    ),
);
