<?php

$dictionary['EC_Commission'] = array(
    'table' => 'ec_commission',
    'audited' => true,
    'inline_edit' => true,
    'duplicate_merge' => true,
    'fields' => array(
        'month' =>
        array(
          'required' => false,
          'name' => 'month',
          'vname' => 'LBL_MONTH',
          'type' => 'int',
          'massupdate' => 0,
          'comments' => '',
          'help' => '',
          'importable' => 'true',
          'duplicate_merge' => 'disabled',
          'duplicate_merge_dom_value' => '',
          'audited' => 0,
          'reportable' => 0,
          'len' => '11',
          'disable_num_format' => '1',
        ),
        'year' =>
        array(
          'required' => false,
          'name' => 'year',
          'vname' => 'LBL_YEAR',
          'type' => 'int',
          'massupdate' => 0,
          'comments' => '',
          'help' => '',
          'importable' => 'true',
          'duplicate_merge' => 'disabled',
          'duplicate_merge_dom_value' => '',
          'audited' => 0,
          'reportable' => 0,
          'len' => '11',
          'disable_num_format' => '1',
        ),
        'from_value' =>
        array(
          'required' => false,
          'name' => 'from_value',
          'vname' => 'LBL_FROM_VALUE',
          'type' => 'currency',
          'massupdate' => 0,
          'comments' => '',
          'help' => '',
          'importable' => 'true',
          'duplicate_merge' => 'disabled',
          'duplicate_merge_dom_value' => '',
          'audited' => 0,
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
        'to_value' =>
        array(
          'required' => false,
          'name' => 'to_value',
          'vname' => 'LBL_TO_VALUE',
          'type' => 'currency',
          'massupdate' => 0,
          'comments' => '',
          'help' => '',
          'importable' => 'true',
          'duplicate_merge' => 'disabled',
          'duplicate_merge_dom_value' => '',
          'audited' => 0,
          'reportable' => 0,
          'len' => 26,
        ),
        'percentage' =>
        array(
          'required' => false,
          'name' => 'percentage',
          'vname' => 'LBL_PERCENTAGE',
          'type' => 'int',
          'massupdate' => 0,
          'comments' => '',
          'help' => '',
          'importable' => 'true',
          'duplicate_merge' => 'disabled',
          'duplicate_merge_dom_value' => '',
          'audited' => 0,
          'reportable' => 0,
          'len' => '11',
          'disable_num_format' => '',
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
VardefManager::createVardef('EC_Commission', 'EC_Commission', array('basic','assignable','security_groups'));
