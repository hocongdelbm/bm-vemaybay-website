<?php

$viewdefs['Emails']['ComposeView'] = array(
    'templateMeta' => array(
        'maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30')
        ),
        'form' => array(
            'headerTpl' => 'modules/Emails/include/ComposeView/ComposeViewBlank.tpl',
            'footerTpl' => 'modules/Emails/include/ComposeView/ComposeViewToolbar.tpl',
            'buttons' => array(
                array('customCode' => '<button class="btn btn-send-email" title="{$MOD.LBL_SEND_BUTTON_TITLE}"><span class="glyphicon glyphicon-send"></span></button>'),
                array('customCode' => '<button class="vertical-separator"></button>'),
                array('customCode' => '<button class="btn btn-attach-file" title="{$MOD.LBL_ATTACH_FILES}"><span class="glyphicon glyphicon-paperclip"></span></button>'),
                array('customCode' => '<button class="btn btn-attach-document" title="{$MOD.LBL_ATTACH_DOCUMENTS}"><span class="glyphicon suitepicon suitepicon-module-documents"></span></button>'),
                array('customCode' => '<button class="vertical-separator"></button>'),
                array('customCode' => '<button class="btn btn-save-draft" title="{$MOD.LBL_SAVE_AS_DRAFT_BUTTON_TITLE}"><span class="glyphicon glyphicon-floppy-save"></span></button>'),
                array('customCode' => '<button class="btn btn-disregard-draft" title="{$MOD.LBL_DISREGARD_DRAFT_BUTTON_TITLE}"><span class="glyphicon glyphicon-trash"></span></button>'),
            )
        ),
        'includes' => array(
            array(
                'file' => 'modules/Emails/include/ComposeView/EmailsComposeView.js',
            ),
            array(
                'file' => 'vendor/tinymce/tinymce/tinymce.min.js'
            ),
            array(
                'file' => 'include/javascript/qtip/jquery.qtip.min.js'
            )
        ),
    ),
    'panels' => array(
        'LBL_COMPOSE_MODULE_NAME' => array(
            array(
                array(
                    'name' => 'emails_email_templates_name',
                    'label' => 'LBL_EMAIL_TEMPLATE',
                    'displayParams' => array(
                        'call_back_function' => '$.fn.EmailsComposeView.onTemplateSelect',
                    ),
                ),
                array(
                    'name' =>  'parent_name',
                    'label' => 'LBL_EMAIL_RELATE',
                    'displayParams' => array(
                        'call_back_function' => '$.fn.EmailsComposeView.onParentSelect',
                        'field_to_name_array' => array(
                            'id' => 'parent_id',
                            'name' => 'parent_name',
                            'email1' => 'email1',
                        )
                    ),
                )
            ),
            array(
                array(
                    'name' => 'from_addr_name',
                    'label' => 'LBL_LIST_FROM_ADDR',
                )
            ),
            array(
                array(
                    'name' => 'to_addrs_names',
                    'label' => 'LBL_TO',
                    'expanded' => 'true',
                )
            ),
            array(
                array(
                    'name' => 'cc_addrs_names',
                    'label' => 'LBL_CC',
                    'expanded' => 'true'
                )
            ),
            array(
                array(
                    'name' => 'bcc_addrs_names',
                    'label' => 'LBL_BCC',
                    'expanded' => 'true'
                )
            ),
            array(
                array(
                    'name' => 'name',
                    'label' => 'LBL_SUBJECT',
                ),
            ),
            array(
                array(
                    'name' => 'description',
                    'label' => 'LBL_BODY',
                )
            ),
            array(
                array(
                    'name' => 'description_html',
                    'label' => 'LBL_BODY',
                )
            ),
            array(
                array(
                    'name' => 'is_only_plain_text',
                    'label' => 'LBL_SEND_IN_PLAIN_TEXT'
                )
            )
        )
    )

);
