<?php
$dictionary['EC_LyDoThangThua'] = array(
    'table' => 'ec_lydothangthua',
    'audited' => true,
    'inline_edit' => true,
    'duplicate_merge' => true,
    'fields' => array(
        'loailydo' => array (
            'required' => false,
            'name' => 'loailydo',
            'vname' => 'LBL_LOAILYDO',
            'type' => 'enum',
            'massupdate' => 0,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '',
            'audited' => 1,
            'reportable' => 0,
            'len' => 100,
            'options' => 'loailydothangthua_list',
            'studio' => 'visible',
            'dependency' => false,
        ),
    ),
    'relationships' => array(),
    'optimistic_locking' => true,
    'unified_search' => true,
);

if (!class_exists('VardefManager')) {
    require_once('include/SugarObjects/VardefManager.php');
}
VardefManager::createVardef('EC_LyDoThangThua', 'EC_LyDoThangThua', array('basic','assignable','security_groups'));