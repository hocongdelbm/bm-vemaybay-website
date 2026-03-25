<?php


$dictionary['EC_Booking_Itineraries'] = array(
    'table' => 'ec_booking_itineraries',
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
VardefManager::createVardef('EC_Booking_Itineraries', 'EC_Booking_Itineraries', array('basic', 'assignable', 'security_groups'));
