<?php
$viewdefs['Users']['EditView'] = array(
    'templateMeta' => array(
        'maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30')
        ),
        'form' => array(
            'headerTpl' => 'modules/Users/tpls/EditViewHeader.tpl',
            'footerTpl' => 'modules/Users/tpls/EditViewFooter.tpl',
        ),
    ),
    'panels' => array(
        'LBL_USER_INFORMATION' => array(
            array(
                array(
                    'name' => 'user_name',
                    'displayParams' => array('required' => true),
                ),
                'first_name'
            ),
            array(
                array(
                    'name' => 'status',
                    'customCode' => '{if $IS_ADMIN}@@FIELD@@{else}{$STATUS_READONLY}{/if}',
                    'displayParams' => array('required' => true),
                ),
                'last_name'
            ),
            array(
                array(
                    'name' => 'UserType',
                    'customCode' => '{if $IS_ADMIN}{$USER_TYPE_DROPDOWN}{else}{$USER_TYPE_READONLY}{/if}',
                ),
                array(
                    'name' => 'agent_prefix',
                    'label' => 'LBL_AGENT_PREFIX',
                )
            ),
            array(
                array(
                    'name' => 'td_sip',
                    'label' => 'LBL_TD_SIP',
                ),
                array(
                    'name' => 'td_password',
                    'label' => 'LBL_TD_PASSWORD',
                )
            ),
            array(
                array(
                    'name' => 'agent_status',
                    'label' => 'LBL_AGENT_STATUS',
                ),
                array(
                    'name' => 'ip_restriction_enabled',
                    'label' => 'LBL_IP_RESTRICTION_ENABLED',
                )
            ),
            array(
                'photo',
                array(
                    'name' => 'factor_auth',
                    'label' => 'LBL_FACTOR_AUTH'
                )
            ),
        ),
        'LBL_EMPLOYEE_INFORMATION' => array(
            array(
                array(
                    'name' => 'employee_status',
                    'customCode' => '{if $IS_ADMIN}@@FIELD@@{else}{$EMPLOYEE_STATUS_READONLY}{/if}',
                ),
                'show_on_employees'
            ),
            array(
                array(
                    'name' => 'title',
                    'customCode' => '{if $IS_ADMIN}@@FIELD@@{else}{$TITLE_READONLY}{/if}',
                ),
                'phone_work'
            ),
            array(
                array(
                    'name' => 'department',
                    'customCode' => '{if $IS_ADMIN}@@FIELD@@{else}{$DEPT_READONLY}{/if}',
                ),
                'phone_mobile'
            ),
            array(
                array(
                    'name' => 'reports_to_name',
                    'customCode' => '{if $IS_ADMIN}@@FIELD@@{else}{$REPORTS_TO_READONLY}{/if}',
                ),
                'phone_other'
            ),
            array('phone_home', 'phone_fax'),
            array('messenger_type', 'messenger_id'),
            array('address_street', 'address_city'),
            array('address_state', 'address_postalcode'),
            array('address_country', 'description'),
        ),
    ),
);
