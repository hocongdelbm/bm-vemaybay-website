<?php
$viewdefs['Employees']['QuickCreate'] = array(
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
        'LBL_EMPLOYEE_INFORMATION' => array(
            array(
                array(
                    'name' => 'employee_status',
                    'customCode' => '{if $EDIT_REPORTS_TO || $IS_ADMIN}@@FIELD@@{else}{$EMPLOYEE_STATUS_READONLY}{/if}',
                ),
                array(
                    'name' => 'title',
                    'customCode' => '{if  $EDIT_REPORTS_TO || $IS_ADMIN}@@FIELD@@{else}{$TITLE_READONLY}{/if}',
                ),
            ),
            array(
                'first_name',
                array(
                    'name' => 'last_name',
                    'displayParams' => array('required' => true),
                ),
            ),
            array(
                array(
                    'name' => 'department',
                    'customCode' => '{if  $EDIT_REPORTS_TO || $IS_ADMIN}@@FIELD@@{else}{$DEPT_READONLY}{/if}',
                ),
                'phone_work'
            ),
            array(
                array(
                    'name' => 'reports_to_name',
                    'customCode' => '{if  $EDIT_REPORTS_TO || $IS_ADMIN}@@FIELD@@{else}{$REPORTS_TO_READONLY}{/if}',
                ),
                array(
                    'name' => 'email1',
                    'displayParams' => array('required' => false),
                ),
            ),
        ),
    ),
);
