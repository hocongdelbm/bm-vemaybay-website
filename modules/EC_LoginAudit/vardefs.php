<?php


$dictionary['EC_LoginAudit'] = array(
    'table' => 'ec_loginaudit',
    'audited' => true,
    'inline_edit' => true,
    'duplicate_merge' => true,
    'fields' =>  
    array(
        'ip_address' =>
        array(
          'required' => false,
          'name' => 'ip_address',
          'vname' => 'LBL_IP_ADDRESS',
          'type' => 'varchar',
          'massupdate' => 0,
          'comments' => '',
          'help' => '',
          'importable' => 'false',
          'duplicate_merge' => 'disabled',
          'duplicate_merge_dom_value' => '0',
          'audited' => 0,
          'reportable' => 0,
          'len' => '15',
        ),
        'typed_name' =>
        array(
          'required' => false,
          'name' => 'typed_name',
          'vname' => 'LBL_TYPED_NAME',
          'type' => 'varchar',
          'massupdate' => 0,
          'comments' => '',
          'help' => '',
          'importable' => 'false',
          'duplicate_merge' => 'disabled',
          'duplicate_merge_dom_value' => '0',
          'audited' => 0,
          'reportable' => 0,
          'len' => '25',
        ),
        'is_admin' =>
        array(
          'required' => false,
          'name' => 'is_admin',
          'vname' => 'LBL_IS_ADMIN',
          'type' => 'bool',
          'massupdate' => 0,
          'comments' => '',
          'help' => '',
          'importable' => 'false',
          'duplicate_merge' => 'disabled',
          'duplicate_merge_dom_value' => '0',
          'audited' => 0,
          'reportable' => 0,
          'len' => '255',
        ),
        'result' =>
        array(
          'required' => false,
          'name' => 'result',
          'vname' => 'LBL_RESULT',
          'type' => 'varchar',
          'massupdate' => 0,
          'comments' => '',
          'help' => '',
          'importable' => 'false',
          'duplicate_merge' => 'disabled',
          'duplicate_merge_dom_value' => '0',
          'audited' => 0,
          'reportable' => 0,
          'len' => '10',
        ),

        // CUSTOME BY HAIHUGN
        'platform' =>
        array(
          'required' => false,
          'name' => 'platform',
          'vname' => 'LBL_PLATFORM',
          'type' => 'varchar',
          'massupdate' => 0,
          'comments' => '',
          'help' => '',
          'importable' => 'false',
          'duplicate_merge' => 'disabled',
          'duplicate_merge_dom_value' => '0',
          'audited' => 0,
          'reportable' => 0,
          'len' => '50',
        ),
        'browser' =>
        array(
          'required' => false,
          'name' => 'browser',
          'vname' => 'LBL_BROWER',
          'type' => 'varchar',
          'massupdate' => 0,
          'comments' => '',
          'help' => '',
          'importable' => 'false',
          'duplicate_merge' => 'disabled',
          'duplicate_merge_dom_value' => '0',
          'audited' => 0,
          'reportable' => 0,
          'len' => '255',
        ),
        'user_agent' =>
        array(
          'required' => false,
          'name' => 'user_agent',
          'vname' => 'LBL_USER_AGENT',
          'type' => 'varchar',
          'massupdate' => 0,
          'comments' => '',
          'help' => '',
          'importable' => 'false',
          'duplicate_merge' => 'disabled',
          'duplicate_merge_dom_value' => '0',
          'audited' => 0,
          'reportable' => 0,
          'len' => '255',
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
VardefManager::createVardef('EC_LoginAudit', 'EC_LoginAudit', array('basic','assignable','security_groups'));
