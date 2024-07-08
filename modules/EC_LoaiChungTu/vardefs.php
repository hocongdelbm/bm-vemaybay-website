<?php

$dictionary['EC_LoaiChungTu'] = array(
    'table' => 'ec_loaichungtu',
    'audited' => true,
    'inline_edit' => true,
    'duplicate_merge' => true,
    'fields' => 
    array(
      'maloai' =>
      array(
        'required' => '1',
        'name' => 'maloai',
        'vname' => 'LBL_MALOAI',
        'type' => 'varchar',
        'massupdate' => 0,
        'comments' => '',
        'help' => '',
        'importable' => 'true',
        'duplicate_merge' => 'disabled',
        'duplicate_merge_dom_value' => ' ',
        'audited' => 1,
        'reportable' => 0,
        'len' => '10',
      ),
      'taikhoanno' =>
      array(
        'required' => false,
        'name' => 'taikhoanno',
        'vname' => 'LBL_TAIKHOANNO',
        'type' => 'varchar',
        'massupdate' => 0,
        'comments' => '',
        'help' => '',
        'importable' => 'true',
        'duplicate_merge' => 'disabled',
        'duplicate_merge_dom_value' => ' ',
        'audited' => 1,
        'reportable' => 0,
        'len' => '10',
      ),
      'taikhoanco' =>
      array(
        'required' => false,
        'name' => 'taikhoanco',
        'vname' => 'LBL_TAIKHOANCO',
        'type' => 'varchar',
        'massupdate' => 0,
        'comments' => '',
        'help' => '',
        'importable' => 'true',
        'duplicate_merge' => 'disabled',
        'duplicate_merge_dom_value' => ' ',
        'audited' => 1,
        'reportable' => 0,
        'len' => '10',
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
VardefManager::createVardef('EC_LoaiChungTu', 'EC_LoaiChungTu', array('basic','assignable','security_groups'));
