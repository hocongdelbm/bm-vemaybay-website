<?php

if (!defined('sugarEntry') || !sugarEntry) {
  die('Not A Valid Entry Point');
}

$viewdefs['Employees'] =
  array(
    'EditView' =>
    array(
      'templateMeta' =>
      array(
        'maxColumns' => '2',
        'widths' =>
        array(
          array(
            'label' => '10',
            'field' => '30',
          ),
          array(
            'label' => '10',
            'field' => '30',
          ),
        ),
        'includes' => array(
          array(
            'file' => 'custom/jqueryui/plugins/jquery.number.min.js',
          ),
          array(
            'file' => 'modules/Employees/js/Employees.js',
          ),
        ),
        'useTabs' => true,
        'tabDefs' =>
        array(
          'DEFAULT' =>
          array(
            'newTab' => true,
            'panelDefault' => 'expanded',
          ),
        ),
      ),
      'panels' =>
      array(
        'default' => array(
          array(
            array(
              'name' => 'last_name',
              'displayParams' => array('required' => true)
            ),
            'first_name',
          ),
          array(
            'employee_status',
            'employee_type',
          ),
          array(
            array(
              'name' => 'department',
              'customCode' => '{if $EDIT_REPORTS_TO}<input class="box-input" type="text" name="{$fields.department.name}" id="{$fields.department.name}" size="30" maxlength="50" value="{$fields.department.value}" title="" tabindex="t" >' .
                '{else}{$fields.department.value}<input type="hidden" name="{$fields.department.name}" id="{$fields.department.name}" value="{$fields.department.value}">{/if}'
            ),
            array(
              'name' => 'title',
              'customCode' => '{if $IS_MANAGER}<input class="box-input" type="text" name="{$fields.title.name}" id="{$fields.title.name}" size="30" maxlength="50" value="{$fields.title.value}" title="" tabindex="t">' .
                '{else}{$fields.title.value}<input type="hidden" name="{$fields.title.name}" id="{$fields.title.name}" value="{$fields.title.value}">{/if}'
            ),
          ),
          array(
            'phone_mobile',
            array(
              'name' => 'phone_work',
              'label' => 'LBL_OFFICE_PHONE'
            ),
          ),
          array(
            'phone_home',
            'phone_other',
          ),
          array(
            array(
              'name' => 'description',
              'label' => 'LBL_NOTES',
              'displayParams' => array(
                'rows' => 4,
                'cols' => 31
              ),
            ),
            array(
              'name' => 'address_street',
              'type' => 'text',
              'label' => 'LBL_PRIMARY_ADDRESS',
              'displayParams' => array(
                'rows' => 4,
                'cols' => 31
              )
            ),
          ),
          array(
            array(
              'name' => 'address_state',
              'label' => 'LBL_STATE'
            ),
            array(
              'name' => 'address_city',
              'label' => 'LBL_CITY'
            ),
          ),
          array(
            array(
              'name' => 'address_country',
              'label' => 'LBL_COUNTRY'
            ),
            array(
              'name' => 'leader_name',
              'label' => 'LBL_LEADER_NAME'
            ),
          ),
          array(
            array(
              'name' => 'email1',
              'label' => 'LBL_EMAIL',
            ),
          ),
        ),
        'LBL_PANEL2' => array(
          array(
            array(
              'name' => 'basic_salary',
              'label' => 'LBL_BASIC_SALARY',
              'customCode' => '{if $IS_MANAGER}
                              <input type="text" class="box-input" name="basic_salary" id="basic_salary" size="30" value="{$fields.basic_salary.value}" title="Lương cơ bản" tabindex="118" class="allow-number-only">
                            {else} 
                              <span class="allow-number-only">{$fields.basic_salary.value}</span>
                            {/if}',
            ),
            array(
              'name' => 'gas_allowance',
              'label' => 'LBL_GAS_ALLOWANCE',
              'customCode' => '{if $IS_MANAGER}
                              <input type="text" class="box-input" name="gas_allowance" id="gas_allowance" size="30" value="{$fields.gas_allowance.value}" title="Phụ cấp xăng xe" tabindex="119" class="allow-number-only">
                            {else} 
                              <span class="allow-number-only">{$fields.gas_allowance.value}</span>
                            {/if}',
            ),
          ),
          array(
            array(
              'name' => 'efficient_wage',
              'label' => 'LBL_EFFICIENT_WAGE',
              'customCode' => '{if $IS_MANAGER}
                              <input type="text" class="box-input" name="efficient_wage" id="efficient_wage" size="30" value="{$fields.efficient_wage.value}" title="Phụ cấp xăng xe" tabindex="120" class="allow-number-only">
                            {else} 
                              <span class="allow-number-only">{$fields.efficient_wage.value}</span>
                            {/if}',
            ),
            array(
              'name' => 'lunch_allowance',
              'label' => 'LBL_LUNCH_ALLOWANCE',
              'customCode' => '{if $IS_MANAGER}
                              <input type="text" class="box-input" name="lunch_allowance" id="lunch_allowance" size="30" value="{$fields.lunch_allowance.value}" title="Phụ cấp cơm trưa" tabindex="121" class="allow-number-only">
                            {else} 
                              <span class="allow-number-only">{$fields.lunch_allowance.value}</span>
                            {/if}',
            ),
          ),
          array(
            array(
              'name' => 'start_working_date',
              'label' => 'LBL_START_WORKING_DATE',
              'customCode' => '{if $IS_MANAGER}
                              {$CUS_START_WORKING_DATE}
                            {else} 
                              {$fields.start_working_date.value}
                            {/if}',
            ),
            array(
              'name' => 'tele_allowance',
              'label' => 'LBL_TELE_ALLOWANCE',
              'customCode' => '{if $IS_MANAGER}
                              <input type="text" class="box-input" name="tele_allowance" id="tele_allowance" size="30" value="{$fields.tele_allowance.value}" title="Phụ cấp diện thoại" tabindex="123" class="allow-number-only">
                            {else} 
                              <span class="allow-number-only">{$fields.tele_allowance.value}</span>
                            {/if}',
            ),
          ),
          array(
            array(
              'name' => 'leaveday',
              'label' => 'LBL_LEAVEDAY',
              'customCode' => '{if $IS_MANAGER}
                              <input type="text" class="box-input" name="leaveday" id="leaveday" size="30" value="{$fields.leaveday.value}" title="Ngày phép" tabindex="124" class="allow-number-only">
                            {else} 
                              <span class="allow-number-only">{$fields.leaveday.value}</span>
                            {/if}',
            ),
            array(
              'name' => 'responsible_allowance',
              'label' => 'LBL_RESPONSIBLE_ALLOWANCE',
              'customCode' => '{if $IS_MANAGER}
                              <input type="text" class="box-input" name="responsible_allowance" id="responsible_allowance" size="30" value="{$fields.responsible_allowance.value}" title="Phụ cấp trách nhiệm" tabindex="125" class="allow-number-only">
                            {else} 
                              <span class="allow-number-only">{$fields.responsible_allowance.value}</span>
                            {/if}',
            ),
          ),
          array(
            'target_month',
            array(
              'name' => 'seniority_allowance',
              'label' => 'LBL_SENIORITY_ALLOWANCE',
              'customCode' => '{if $IS_MANAGER}
                              <input type="text" class="box-input" name="seniority_allowance" id="seniority_allowance" size="30" value="{$fields.seniority_allowance.value}" title="Phụ cấp thâm niên" tabindex="127" class="allow-number-only">
                            {else} 
                              <span class="allow-number-only">{$fields.seniority_allowance.value}</span>
                            {/if}',
            ),
          ),
          array(
            'target_quarter',
            array(
              'name' => 'other_allowance1',
              'label' => 'LBL_OTHER_ALLOWANCE1',
              'customCode' => '{if $IS_MANAGER}
                              <input type="text" class="box-input" name="other_allowance1" id="other_allowance1" size="30" value="{$fields.other_allowance1.value}" title="Phụ cấp khác 1" tabindex="129" class="allow-number-only">
                            {else} 
                              <span class="allow-number-only">{$fields.other_allowance1.value}</span>
                            {/if}',
            ),
          ),
          array(
            'target_year',
            array(
              'name' => 'other_allowance2',
              'label' => 'LBL_OTHER_ALLOWANCE2',
              'customCode' => '{if $IS_MANAGER}
                              <input type="text" class="box-input" name="other_allowance2" id="other_allowance2" size="30" value="{$fields.other_allowance2.value}" title="Phụ cấp khác 2" tabindex="131" class="allow-number-only">
                            {else} 
                              <span class="allow-number-only">{$fields.other_allowance2.value}</span>
                            {/if}',
            ),
          ),
          array(
            array(
              'name' => 'init_exp_mark',
              'label' => 'LBL_INIT_EXP_MARK',
              'customCode' => '{if $IS_MANAGER}
                              <input type="text" class="box-input" name="init_exp_mark" id="init_exp_mark" size="30" value="{$fields.init_exp_mark.value}" title="Điểm kinh nghiệm (khởi tạo)" tabindex="121" class="allow-number-only">
                            {else} 
                              <span class="allow-number-only">{$fields.init_exp_mark.value}</span>
                            {/if}',
            ),
            ''
          ),
        ),
        'LBL_WORK_HISTORY' => array(
          array(
            array(
              'name' => 'work_history',
              'label' => 'LBL_WORK_HISTORY',
              'customCode' => '{$WORK_HISTORY}',
            ),
          ),
        ),
      ),
    ),
  );
