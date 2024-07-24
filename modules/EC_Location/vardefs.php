<?php

$dictionary['EC_Location'] = array(
    'table' => 'ec_location',
    'audited' => true,
    'inline_edit' => true,
    'duplicate_merge' => true,
    'fields' => array(
      'company_id' =>
      array(
        'required' => false,
        'name' => 'company_id',
        'vname' => '',
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
      ),
      'company' =>
      array(
        'required' => '1',
        'source' => 'non-db',
        'name' => 'company',
        'vname' => 'LBL_COMPANY',
        'type' => 'relate',
        'massupdate' => 0,
        'comments' => '',
        'help' => '',
        'importable' => 'true',
        'duplicate_merge' => 'disabled',
        'duplicate_merge_dom_value' => '',
        'audited' => 1,
        'reportable' => 0,
        'len' => '255',
        'id_name' => 'company_id',
        'ext2' => 'SecurityGroups',
        'module' => 'SecurityGroups',
        'rname' => 'name',
        'quicksearch' => 'enabled',
        'studio' => 'visible',
      ),
      'is_display' => array(
        'name'      => 'is_display',
        'vname'     => 'LBL_IS_DISPLAY',
        'type'      => 'bool',
        'required'  => false,
        'massupdate' => 0,
        'comments' => '',
        'help' => '',
        'importable' => 'true',
        'duplicate_merge' => 'disabled',
        'duplicate_merge_dom_value' => '',
        'audited' => 1,
        'reportable' => 0,
        'len' => '1',
        'default' => '0',
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
VardefManager::createVardef('EC_Location', 'EC_Location', array('basic','assignable','security_groups'));