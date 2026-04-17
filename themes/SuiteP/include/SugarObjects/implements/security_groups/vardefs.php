<?php



$vardefs = array(
    'fields' => array(

        'SecurityGroups' => array(
            'name' => 'SecurityGroups',
            'type' => 'link',
            'relationship' => 'securitygroups_' . strtolower($module),
            'module' => 'SecurityGroups',
            'bean_name' => 'SecurityGroup',
            'source' => 'non-db',
            'vname' => 'LBL_SECURITYGROUPS',
        ),
    ),

    'relationships' => array(
        'securitygroups_' . strtolower($module) =>
        array(
            'lhs_module' => 'SecurityGroups',
            'lhs_table' => 'securitygroups',
            'lhs_key' => 'id',
            'rhs_module' => $module,
            'rhs_table' => $table_name,
            'rhs_key' => 'id',
            'relationship_type' => 'many-to-many',
            'join_table' => 'securitygroups_records',
            'join_key_lhs' => 'securitygroup_id',
            'join_key_rhs' => 'record_id',
            'relationship_role_column' => 'module',
            'relationship_role_column_value' => $module

        ),
    ),
    'indices' => array()
);
