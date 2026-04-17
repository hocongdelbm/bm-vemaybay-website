<?php


$dictionary['EC_Booking_Passengers'] = array(
    'table' => 'ec_booking_passengers',
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
VardefManager::createVardef('EC_Booking_Passengers', 'EC_Booking_Passengers', array('basic', 'assignable', 'security_groups'));
