<?php
$dictionary['Favorites'] = array(
    'table' => 'favorites',
    'audited' => false,
    'duplicate_merge' => false,
    'fields' => array(

        'parent_id' => array(
            'name' => 'parent_id',
            'vname' => 'LBL_PARENT_FAVORITE_ID',
            'type' => 'id',
            'required' => false,
            'reportable' => false,
            'audited' => true,
            'comment' => 'Favorite ID of the parent of this account',
        ),

        'parent_type' => array(
            'name' => 'parent_type',
            'vname' => 'LBL_PARENT_TYPE',
            'type' => 'parent_type',
            'dbType' => 'varchar',
            'required' => false,
            'group' => 'parent_name',
            'options' => 'parent_type_display',
            'len' => 255,
            'comment' => 'The Sugar object to which the call is related',
        ),

    ),
    'relationships' => array(),
    'optimistic_locking' => true,
    'unified_search' => false,
);
if (!class_exists('VardefManager')) {
    require_once 'include/SugarObjects/VardefManager.php';
}
VardefManager::createVardef('Favorites', 'Favorites', array('basic', 'assignable'));
