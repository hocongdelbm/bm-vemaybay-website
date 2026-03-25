<?php


$dictionary['EC_Flight_Bookings'] = array(
    'table' => 'ec_flight_bookings',
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
VardefManager::createVardef('EC_Flight_Bookings', 'EC_Flight_Bookings', array('basic', 'assignable', 'security_groups'));
