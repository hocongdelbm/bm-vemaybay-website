<?php
$dictionary['EC_Banks'] = array(
    'table' => 'ec_banks',
    'audited' => true,
    'inline_edit' => true,
    'duplicate_merge' => true,
    'fields' => array(
        'short_name' => array(
            'required'  => '1',
            'name'      => 'short_name',
            'vname'     => 'LBL_SHORT_NAME',
            'type'      => 'varchar',
            'massupdate' => 0,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => ' ',
            'audited' => 1,
            'reportable' => 0,
            'len' => '50',
        ),

        'english_name' => array(
            'required'  => false,
            'name'      => 'english_name',
            'vname'     => 'LBL_ENGLISH_NAME',
            'type'      => 'varchar',
            'massupdate' => 0,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => ' ',
            'audited' => 1,
            'reportable' => 0,
            'len' => '255',
        ),

        'headquarters' => array(
            'required'  => false,
            'name'      => 'headquarters',
            'vname'     => 'LBL_HEADQUARTERS',
            'type'      => 'text',
            'massupdate' => 0,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => ' ',
            'audited' => 1,
            'reportable' => 0,
            'studio' => 'visible',
        ),

        'unfollow' => array(
            'required'  => false,
            'name'      => 'unfollow',
            'vname'     => 'LBL_UNFOLLOW',
            'type'      => 'bool',
            'massupdate' => 0,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => ' ',
            'audited' => 1,
            'reportable' => 0
        ),

        'image' => array(
            'required'  => false,
            'name'      => 'image',
            'vname'     => 'LBL_IMAGE',
            'type'      => 'varchar',
            'massupdate' => 0,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => ' ',
            'audited' => 1,
            'reportable' => 0,
            'len' => '255',
        ),
    ),
    'relationships' => array(),
    'optimistic_locking' => true,
    'unified_search' => true,
);

if (!class_exists('VardefManager')) {
    require_once('include/SugarObjects/VardefManager.php');
}
VardefManager::createVardef('EC_Banks', 'EC_Banks', array('basic', 'assignable', 'security_groups'));
