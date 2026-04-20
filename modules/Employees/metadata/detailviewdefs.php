<?php
$viewdefs['Employees']['DetailView'] = array(
    'templateMeta' => array(
        'form' => array(
            'buttons' => array(
                array('customCode' => '{if $DISPLAY_EDIT}<input title="{$APP.LBL_EDIT_BUTTON_TITLE}" accessKey="{$APP.LBL_EDIT_BUTTON_KEY}" class="btn btn-warning" onclick="this.form.return_module.value=\'{$module}\'; this.form.return_action.value=\'DetailView\'; this.form.return_id.value=\'{$id}\'; this.form.action.value=\'EditView\'" type="submit" name="Edit" id="edit_button" value="{$APP.LBL_EDIT_BUTTON_LABEL}">{/if}'),
                array('customCode' => '{if $DISPLAY_DUPLICATE}<input title="{$APP.LBL_DUPLICATE_BUTTON_TITLE}" accessKey="{$APP.LBL_DUPLICATE_BUTTON_KEY}" class="btn btn-primary" onclick="this.form.return_module.value=\'{$module}\'     ; this.form.return_action.value=\'DetailView\'; this.form.return_id.value=\'{$id}\'; this.form.isDuplicate.value=true; this.form.action.value=\'EditView\'" type="submit" name="Duplicate" value="{$APP.LBL_DUPLICATE_BUTTON_LABEL}" id="duplicate_button">{/if}'),
            ),
        ),
        'maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30')
        ),
        'includes' => array(
            0 =>
            array(
                'file' => 'custom/jqueryui/plugins/jquery.number.min.js',
            ),
        ),
    ),
    'panels' => array(
        'LBL_PANEL1' => array(
            array(
                array(
                    'name' => 'first_name',
                    'customCode' => '{$fields.last_name.value} {$fields.first_name.value}',
                    'label' => 'LBL_FULLNAME',
                ),
                'employee_status',
            ),
            array(
                array(
                    'name' => 'address_country',
                    'customCode' => '{$fields.address_street.value} {$fields.address_city.value} {$fields.address_state.value} {$fields.address_postalcode.value} {$fields.address_country.value}',
                    'label' => 'LBL_ADDRESS',
                ),
                array(
                    'name' => 'phone_home',
                    'label' => 'LBL_HOME_PHONE',
                ),
            ),
            array(
                array(
                    'name' => 'phone_mobile',
                    'label' => 'LBL_MOBILE_PHONE',
                ),
                array(
                    'name' => 'phone_work',
                    'label' => 'LBL_OFFICE_PHONE',
                ),
            ),
            array(
                array(
                    'name' => 'title',
                    'label' => 'LBL_TITLE',
                ),
                array(
                    'name' => 'other_phone',
                    'label' => 'LBL_OTHER_PHONE',
                ),
            ),
            array(
                array(
                    'name' => 'department',
                    'label' => 'LBL_DEPARTMENT',
                ),
                array(
                    'name' => 'employee_type',
                    'label' => 'LBL_EMPLOYEE_TYPE',
                ),
            ),
            array(
                array(
                    'name' => 'leader_name',
                    'label' => 'LBL_LEADER_NAME',
                    'customCode' => '{$LEADER_NAME}',
                ),
                array(
                    'name' => 'email1',
                    'label' => 'LBL_EMAIL',
                ),
            ),
            array(
                array(
                    'name' => 'description',
                    'label' => 'LBL_NOTES',
                ),
            ),
        ),
        'LBL_PANEL2' => array(
            array(
                'basic_salary',
                array(
                    'name' => 'gas_allowance',
                    'label' => 'LBL_GAS_ALLOWANCE',
                    'customCode' => '<span class="allow-number-only">{$fields.gas_allowance.value}</span>'
                ),
            ),
            array(
                array(
                    'name' => 'efficient_wage',
                    'label' => 'LBL_EFFICIENT_WAGE',
                    'customCode' => '<span class="allow-number-only">{$fields.efficient_wage.value}</span>'
                ),
                array(
                    'name' => 'lunch_allowance',
                    'label' => 'LBL_LUNCH_ALLOWANCE',
                    'customCode' => '<span class="allow-number-only">{$fields.lunch_allowance.value}</span>'
                ),
            ),
            array(
                'start_working_date',
                array(
                    'name' => 'tele_allowance',
                    'label' => 'LBL_TELE_ALLOWANCE',
                    'customCode' => '<span class="allow-number-only">{$fields.tele_allowance.value}</span>'
                ),
            ),
            array(
                'leaveday',
                array(
                    'name' => 'responsible_allowance',
                    'label' => 'LBL_RESPONSIBLE_ALLOWANCE',
                    'customCode' => '<span class="allow-number-only">{$fields.responsible_allowance.value}</span>'
                ),
            ),
            array(
                array(
                    'name' => 'target_month',
                    'label' => 'LBL_TARGET_MONTH',
                    'customCode' => '<span class="allow-number-only">{$fields.target_month.value}</span>'
                ),
                array(
                    'name' => 'seniority_allowance',
                    'label' => 'LBL_SENIORITY_ALLOWANCE',
                    'customCode' => '<span class="allow-number-only">{$fields.seniority_allowance.value}</span>'
                ),
            ),
            array(
                array(
                    'name' => 'target_quarter',
                    'label' => 'LBL_TARGET_QUARTER',
                    'customCode' => '<span class="allow-number-only">{$fields.target_quarter.value}</span>'
                ),
                array(
                    'name' => 'other_allowance1',
                    'label' => 'LBL_OTHER_ALLOWANCE1',
                    'customCode' => '<span class="allow-number-only">{$fields.other_allowance1.value}</span>'
                ),
            ),
            array(
                array(
                    'name' => 'target_year',
                    'label' => 'LBL_TARGET_YEAR',
                    'customCode' => '<span class="allow-number-only">{$fields.target_year.value}</span>'
                ),
                array(
                    'name' => 'other_allowance2',
                    'label' => 'LBL_OTHER_ALLOWANCE2',
                    'customCode' => '<span class="allow-number-only">{$fields.other_allowance2.value}</span>'
                ),
            ),
        ),
        'LBL_WORK_HISTORY' => array(
            0 => array(
                'work_history',
                0 => array(
                    'name' => 'work_history',
                    'label' => 'LBL_WORK_HISTORY',
                    'customCode' => '{$WORK_HISTORY}'
                ),
            ),
        ),
    ),
);
