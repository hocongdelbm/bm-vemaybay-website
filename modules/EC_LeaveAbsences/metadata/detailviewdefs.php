<?php
$module_name = 'EC_LeaveAbsences';
$viewdefs[$module_name]['DetailView'] = array(
    'templateMeta' => array(
        'form' => array(
            'buttons' => array(
                'EDIT',
                'DUPLICATE',
                'DELETE',
                array('customCode' => '{$STATUS_BTN}'),
                array('customCode' => '{$APPROVED_BTN}'),
                array('customCode' => '{$CHANGE_STATUS_BTN}'),
            ),
            // 'hideAudit' => 1
        ),
        'maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30')
        ),
    ),

    'panels' => array (
        'lbl_detail_leave_days' => array(
            array (
                array(
                    'name' => 'assigned_user_name',
                    'label' => 'LBL_ASSIGNED_TO_NAME',
                    'customCode' => '{$ASSIGNED_USER_NAME}',
                ),
                'status',
            ),
            array(
                'from_date',
                'absence_type',
            ),
            array(
                'to_date',
                'reason',
            ),
            array(
                array(
                    'name' => 'absence_days',
                    'label' => 'LBL_ABSENCE_DAYS',
                    'customCode' => '{$ABSENCE_DAYS}',
                ),
                array(
                    'name' => 'approved_date',
                    'label' => 'LBL_APPROVED_DATE',
                    'customCode' => '{$CUS_APPROVED_BY}'
                ),
            ),
            array(
                'description',
                array(
                    'name' => 'remark',
                    'label' => 'LBL_REMARK',
                    'customCode' => '{$REMARK}',
                ),
            ),
            array (
                array (
                    'name' => 'date_entered',
                    'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}',
                    'label' => 'LBL_DATE_ENTERED',
                ),
                array (
                    'name' => 'date_modified',
                    'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}',
                    'label' => 'LBL_DATE_MODIFIED',
                ),
            ),
        )
    ),
);
