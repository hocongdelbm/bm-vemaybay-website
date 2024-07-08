<?php

$module_name = 'SecurityGroups';
$viewdefs[$module_name]['DetailView'] =
    array(
        'templateMeta' =>
        array(
            'form' =>
            array(
                'buttons' =>
                array(
                    'EDIT', 'DUPLICATE', 'DELETE',
                )
            ),
            'maxColumns' => '2',
            'widths' => array(
                0 =>
                array(
                    'label' => '10',
                    'field' => '30'
                ),
                1 =>
                array(
                    'label' => '10',
                    'field' => '30'
                )
            ),
        ),

        'panels' => array(
            'default' => array(
                array(
                    'name',
                    'assigned_user_name',
                ),

                array(
                    'noninheritable',
                    'description',
                ),

                array(
                    array(
                        'name' => 'date_entered',
                        'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}',
                        'label' => 'LBL_DATE_ENTERED',
                    ),
                    array(
                        'name' => 'date_modified',
                        'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}',
                        'label' => 'LBL_DATE_MODIFIED',
                    ),
                ),

                array(
                    array(
                        'name' => 'use_auto_book',
                        'label' => 'LBL_USE_AUTO_BOOK',
                    ),
                    array(
                        'name' => 'use_mail_confirm',
                        'label' => 'LBL_USE_MAIL_CONFIRM',
                    ),
                ),

                array(
                    array(
                        'name' => 'use_mail_eticket',
                        'label' => 'LBL_USE_MAIL_ETICKET',
                    ),
                    array(
                        'name' => 'is_report',
                        'label' => 'LBL_IS_REPORT',
                    ),
                ),

                array(
                    array(
                        'name' => 'location',
                        'studio' => 'visible',
                        'label' => 'LBL_LOCATION',
                    ),
                ),
            ),

            'lbl_panel1' => array(
                array(
                    array(
                        'name' => 'com_name',
                        'label' => 'LBL_COM_NAME',
                    ),
                    array(
                        'name' => 'com_taxcode',
                        'label' => 'LBL_COM_TAXCODE',
                    ),
                ),

                array(
                    array(
                        'name' => 'delivery_fee',
                        'label' => 'LBL_DELIVERY_FEE',
                    ),
                    array(
                        'name' => 'payment_guide_link',
                        'label' => 'LBL_PAYMENT_GUIDE_LINK',
                    ),
                ),

                array(
                    array(
                        'name' => 'code_book',
                        'studio' => 'visible',
                        'label' => 'LBL_CODE_BOOK',
                    ),
                    array(
                        'name' => 'promo_link',
                        'studio' => 'visible',
                        'label' => 'LBL_PROMO_LINK',
                    ),
                ),

                array(
                    array(
                        'name' => 'color',
                        'label' => 'LBL_COLOR',
                    ),
                ),
            ),

            'lbl_panel6' => array(
                array(
                    array(
                        'name' => 'com_email',
                        'label' => 'LBL_COM_EMAIL',
                    ),
                    array(
                        'name' => 'com_email2',
                        'label' => 'LBL_COM_EMAIL2',
                    ),
                ),

                array(
                    array(
                        'name' => 'com_email3',
                        'label' => 'LBL_COM_EMAIL3',
                    ),
                    array(
                        'name' => 'local_email',
                        'studio' => 'visible',
                        'label' => 'LBL_LOCAL_EMAIL',
                    ),
                ),
            ),

            'lbl_panel3' => array(
                array(
                    array(
                        'name' => 'com_address',
                        'label' => 'LBL_COM_ADDRESS',
                    ),
                    array(
                        'name' => 'com_address2',
                        'label' => 'LBL_COM_ADDRESS2',
                    ),
                ),

                array(
                    array(
                        'name' => 'com_address3',
                        'label' => 'LBL_COM_ADDRESS3',
                    ),
                    array(
                        'name' => 'com_address4',
                        'label' => 'LBL_COM_ADDRESS4',
                    ),
                ),
            ),

            'lbl_panel4' => array(
                array(
                    array(
                        'name' => 'com_phone',
                        'label' => 'LBL_COM_PHONE',
                    ),
                    array(
                        'name' => 'com_phone2',
                        'label' => 'LBL_COM_PHONE2',
                    ),
                ),

                array(
                    array(
                        'name' => 'com_phone3',
                        'label' => 'LBL_COM_PHONE3',
                    ),
                    array(
                        'name' => 'com_hotline1',
                        'label' => 'LBL_COM_HOTLINE1',
                    ),
                ),

                array(
                    array(
                        'name' => 'com_hotline2',
                        'label' => 'LBL_COM_HOTLINE2',
                    ),
                    array(
                        'name' => 'com_hotline3',
                        'label' => 'LBL_COM_HOTLINE3',
                    ),
                ),
            ),

            'lbl_panel5' => array(
                array(
                    array(
                        'name' => 'com_website',
                        'label' => 'LBL_COM_WEBSITE',
                    ),
                    array(
                        'name' => 'com_website2',
                        'label' => 'LBL_COM_WEBSITE2',
                    ),
                ),

                array(
                    array(
                        'name' => 'com_website3',
                        'label' => 'LBL_COM_WEBSITE3',
                    ),
                ),
            ),

            'lbl_panel2' => array(
                array(
                    array(
                        'name' => 'notify_fromname',
                        'label' => 'LBL_NOTIFY_FROMNAME',
                    ),
                    array(
                        'name' => 'notify_fromaddress',
                        'label' => 'LBL_NOTIFY_FROMADDRESS',
                    ),
                ),

                array(
                    array(
                        'name' => 'mail_smtpserver',
                        'label' => 'LBL_MAIL_SMTPSERVER',
                    ),
                    array(
                        'name' => 'mail_smtpport',
                        'label' => 'LBL_MAIL_SMTPPORT',
                    ),
                ),

                array(
                    array(
                        'name' => 'mail_smtpssl',
                        'studio' => 'visible',
                        'label' => 'LBL_SMTPSSL',
                    ),
                    array(
                        'name' => 'company_logo',
                        'label' => 'LBL_COMPANY_LOGO',
                    ),
                ),

                array(
                    array(
                        'name' => 'mail_smtpuser',
                        'label' => 'LBL_MAIL_SMTPUSER',
                    ),
                    array(
                        'name' => 'mail_smtppass',
                        'label' => 'LBL_MAIL_SMTPPASS',
                    ),
                ),

                array(
                    array(
                        'name' => 'com_email_bcc',
                        'label' => 'LBL_COM_EMAIL_BCC',
                    ),
                    array()
                ),
            ),
        ),
    );
