<?php



$vardefs = array(
    'fields' => array(),

    'relationships' => array(
        strtolower($module) . '_team_count_relationship' =>
        array(
            'lhs_module' => 'Teams',
            'lhs_table' => 'team_sets',
            'lhs_key' => 'id',
            'rhs_module' => $module,
            'rhs_table' => $table_name,
            'rhs_key' => 'team_set_id',
            'relationship_type' => 'one-to-many'
        ),
        strtolower($module) . '_teams' =>
        array(
            'lhs_module'        => $module,
            'lhs_table'         => $table_name,
            'lhs_key'           => 'team_set_id',
            'rhs_module'        => 'Teams',
            'rhs_table'         => 'teams',
            'rhs_key'           => 'id',
            'relationship_type' => 'many-to-many',
            'join_table'        => 'team_sets_teams',
            'join_key_lhs'      => 'team_set_id',
            'join_key_rhs'      => 'team_id',
        ),
    ),
    'indices' => array(
        array(
            'name' => 'idx_' . strtolower($table_name) . '_tmst_id',
            'type' => 'index',
            'fields' => array('team_set_id')
        ),
    )
);
