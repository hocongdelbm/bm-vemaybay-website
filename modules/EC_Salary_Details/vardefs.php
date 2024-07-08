<?php
$dictionary['EC_Salary_Details'] = array(
    'table' => 'ec_salary_details',
    'audited' => true,
    'inline_edit' => true,
    'duplicate_merge' => true,
    'fields' => array(
        'bonus_amount' =>
        array(
          'required' => false,
          'name' => 'bonus_amount',
          'vname' => 'LBL_BONUS_AMOUNT',
          'type' => 'currency',
          'massupdate' => 0,
          'comments' => '',
          'help' => '',
          'importable' => 'true',
          'duplicate_merge' => 'disabled',
          'duplicate_merge_dom_value' => '0',
          'audited' => 1,
          'reportable' => 0,
          'len' => 26,
        ),
        'currency_id' =>
        array(
          'required' => false,
          'name' => 'currency_id',
          'vname' => 'LBL_CURRENCY',
          'type' => 'id',
          'massupdate' => 0,
          'comments' => '',
          'help' => '',
          'importable' => 'true',
          'duplicate_merge' => 'disabled',
          'duplicate_merge_dom_value' => 0,
          'audited' => 0,
          'reportable' => 0,
          'len' => 36,
          'studio' => 'visible',
          'function' =>
          array(
            'name' => 'getCurrencyDropDown',
            'returns' => 'html',
          ),
        ),
        'minus_amount' =>
        array(
          'required' => false,
          'name' => 'minus_amount',
          'vname' => 'LBL_MINUS_AMOUNT',
          'type' => 'currency',
          'massupdate' => 0,
          'comments' => '',
          'help' => '',
          'importable' => 'true',
          'duplicate_merge' => 'disabled',
          'duplicate_merge_dom_value' => '0',
          'audited' => 1,
          'reportable' => 0,
          'len' => 26,
        ),
        'reason' =>
        array(
          'required' => false,
          'name' => 'reason',
          'vname' => 'LBL_REASON',
          'type' => 'text',
          'massupdate' => 0,
          'comments' => '',
          'help' => '',
          'importable' => 'true',
          'duplicate_merge' => 'disabled',
          'duplicate_merge_dom_value' => '0',
          'audited' => 0,
          'reportable' => 0,
          'studio' => 'visible',
        ),
        'type' =>
        array(
          'required' => false,
          'name' => 'type',
          'vname' => 'LBL_TYPE',
          'type' => 'varchar',
          'massupdate' => 0,
          'comments' => '',
          'help' => '',
          'importable' => 'true',
          'duplicate_merge' => 'disabled',
          'duplicate_merge_dom_value' => '0',
          'audited' => 0,
          'reportable' => 0,
          'len' => '30',
        ),
        'voucher_date' =>
        array(
          'required' => '1',
          'name' => 'voucher_date',
          'vname' => 'LBL_VOUCHER_DATE',
          'type' => 'date',
          'massupdate' => 0,
          'comments' => '',
          'help' => '',
          'importable' => 'true',
          'duplicate_merge' => 'disabled',
          'duplicate_merge_dom_value' => ' ',
          'audited' => 1,
          'reportable' => 0,
        ),
      ),
    'relationships' => array (
),
    'optimistic_locking' => true,
    'unified_search' => true,
);
if (!class_exists('VardefManager')) {
        require_once('include/SugarObjects/VardefManager.php');
}
VardefManager::createVardef('EC_Salary_Details', 'EC_Salary_Details', array('basic','assignable','security_groups'));
