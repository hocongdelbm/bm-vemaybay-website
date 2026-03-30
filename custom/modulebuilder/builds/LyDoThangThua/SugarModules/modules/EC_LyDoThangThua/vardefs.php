<?php


$dictionary['EC_LyDoThangThua'] = array(
    'table' => 'ec_lydothangthua',
    'audited' => true,
    'inline_edit' => true,
    'duplicate_merge' => true,
    'fields' => array(),
    'relationships' => array(),
    'optimistic_locking' => true,
    'unified_search' => true,
);
if (!class_exists('VardefManager')) {
    require_once('include/SugarObjects/VardefManager.php');
}
VardefManager::createVardef('EC_LyDoThangThua', 'EC_LyDoThangThua', array('basic', 'assignable', 'security_groups'));
