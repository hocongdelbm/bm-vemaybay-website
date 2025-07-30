<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$dictionary['Bug'] = array(
    'table' => 'bugs',
    'audited' => true,
    'comment' => 'Bugs are defects in products and services',
    'duplicate_merge' => true,
    'unified_search' => true,
    'fields' => array(
        'found_in_release' => array(
            'name' => 'found_in_release',
            'type' => 'enum',
            'function' => 'getReleaseDropDown',
            'vname' => 'LBL_FOUND_IN_RELEASE',
            'reportable' => false,
            //'merge_filter' => 'enabled', //bug 22994, I think the former fixing is just avoiding the cross table query, it is not a good method.
            'comment' => 'The software or service release that manifested the bug',
            'duplicate_merge' => 'disabled',
            'audited' => true,
            'studio' => array(
                'fields' => 'false',  // tyoung bug 16442 - don't show in studio fields list
                'listview' => false,
            ),
            'massupdate' => true,
        ),
        'release_name' => array(
            'name' => 'release_name',
            'rname' => 'name',
            'vname' => 'LBL_FOUND_IN_RELEASE',
            'type' => 'relate',
            'dbType' => 'varchar',
            'group' => 'found_in_release',
            'reportable' => false,
            'source' => 'non-db',
            'table' => 'releases',
            'merge_filter' => 'enabled',
            //bug 22994, we should use the release name to search, I have write codes to operate the cross table query.
            'id_name' => 'found_in_release',
            'module' => 'Releases',
            'link' => 'release_link',
            'massupdate' => false,
            'studio' => array(
                'editview' => false,
                'detailview' => false,
                'quickcreate' => false,
                'basic_search' => false,
                'advanced_search' => false,
            ),
        ),

        'fixed_in_release' => array(
            'name' => 'fixed_in_release',
            'type' => 'enum',
            'function' => 'getReleaseDropDown',
            'vname' => 'LBL_FIXED_IN_RELEASE',
            'reportable' => false,
            'comment' => 'The software or service release that corrected the bug',
            'duplicate_merge' => 'disabled',
            'audited' => true,
            'studio' => array(
                'fields' => 'false', // tyoung bug 16442 - don't show in studio fields list
                'listview' => false,
            ),
            'massupdate' => true,
        ),
        'fixed_in_release_name' => array(
            'name' => 'fixed_in_release_name',
            'rname' => 'name',
            'group' => 'fixed_in_release',
            'id_name' => 'fixed_in_release',
            'vname' => 'LBL_FIXED_IN_RELEASE',
            'type' => 'relate',
            'table' => 'releases',
            'isnull' => 'false',
            'massupdate' => false,
            'module' => 'Releases',
            'dbType' => 'varchar',
            'len' => 36,
            'source' => 'non-db',
            'link' => 'fixed_in_release_link',
            'studio' => array(
                'editview' => false,
                'detailview' => false,
                'quickcreate' => false,
                'basic_search' => false,
                'advanced_search' => false,
            ),
        ),
        'source' => array(
            'name' => 'source',
            'vname' => 'LBL_SOURCE',
            'type' => 'enum',
            'options' => 'source_dom',
            'len' => 255,
            'comment' => 'An indicator of how the bug was entered (ex: via web, email, etc.)'
        ),
        'product_category' => array(
            'name' => 'product_category',
            'vname' => 'LBL_PRODUCT_CATEGORY',
            'type' => 'enum',
            'options' => 'product_category_dom',
            'len' => 255,
            'comment' => 'Where the bug was discovered (ex: Accounts, Contacts, Leads)'
        ),

        'tasks' => array(
            'name' => 'tasks',
            'type' => 'link',
            'relationship' => 'bug_tasks',
            'source' => 'non-db',
            'vname' => 'LBL_TASKS'
        ),
        'notes' => array(
            'name' => 'notes',
            'type' => 'link',
            'relationship' => 'bug_notes',
            'source' => 'non-db',
            'vname' => 'LBL_NOTES'
        ),
        'meetings' => array(
            'name' => 'meetings',
            'type' => 'link',
            'relationship' => 'bug_meetings',
            'source' => 'non-db',
            'vname' => 'LBL_MEETINGS'
        ),
        'calls' => array(
            'name' => 'calls',
            'type' => 'link',
            'relationship' => 'bug_calls',
            'source' => 'non-db',
            'vname' => 'LBL_CALLS'
        ),
        'emails' => array(
            'name' => 'emails',
            'type' => 'link',
            'relationship' => 'emails_bugs_rel',/* reldef in emails */
            'source' => 'non-db',
            'vname' => 'LBL_EMAILS'
        ),
        'documents' => array(
            'name' => 'documents',
            'type' => 'link',
            'relationship' => 'documents_bugs',
            'source' => 'non-db',
            'vname' => 'LBL_DOCUMENTS_SUBPANEL_TITLE',
        ),
        'contacts' => array(
            'name' => 'contacts',
            'type' => 'link',
            'relationship' => 'contacts_bugs',
            'source' => 'non-db',
            'vname' => 'LBL_CONTACTS'
        ),
        'accounts' => array(
            'name' => 'accounts',
            'type' => 'link',
            'relationship' => 'accounts_bugs',
            'source' => 'non-db',
            'vname' => 'LBL_ACCOUNTS'
        ),
        'cases' => array(
            'name' => 'cases',
            'type' => 'link',
            'relationship' => 'cases_bugs',
            'source' => 'non-db',
            'vname' => 'LBL_CASES'
        ),
        'project' => array(
            'name' => 'project',
            'type' => 'link',
            'relationship' => 'projects_bugs',
            'source' => 'non-db',
            'vname' => 'LBL_PROJECTS',
        ),
        'release_link' => array(
            'name' => 'release_link',
            'type' => 'link',
            'relationship' => 'bugs_release',
            'vname' => 'LBL_FOUND_IN_RELEASE',
            'link_type' => 'one',
            'module' => 'Releases',
            'bean_name' => 'Release',
            'source' => 'non-db',
        ),
        'fixed_in_release_link' => array(
            'name' => 'fixed_in_release_link',
            'type' => 'link',
            'relationship' => 'bugs_fixed_in_release',
            'vname' => 'LBL_FIXED_IN_RELEASE',
            'link_type' => 'one',
            'module' => 'Releases',
            'bean_name' => 'Release',
            'source' => 'non-db',
        ),

        // custom
        'photo' => array(
            'name' => 'photo',
            'vname' => 'LBL_PHOTO',
            'type' => 'image',
            'massupdate' => false,
            'comments' => '',
            'help' => '',
            'importable' => false,
            'border' => true,
            'reportable' => true,
            'len' => 255,
            'dbType' => 'varchar',
            'width' => '500',
            'height' => 'auto',
            'studio' => array('listview' => true),
        ),
        'photo_sub' => array(
            'name' => 'photo_sub',
            'vname' => 'LBL_PHOTO_SUB',
            'type' => 'image',
            'massupdate' => false,
            'comments' => '',
            'help' => '',
            'importable' => false,
            'border' => true,
            'reportable' => true,
            'len' => 255,
            'dbType' => 'varchar',
            'width' => '500',
            'height' => 'auto',
            'studio' => array('listview' => true),
        ),

        'parent_id' => array(
            'required'   => false,
            'name' => 'parent_id',
            'vname' => 'LBL_PARENT_ID',
            'type' => 'id',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => 0,
            'massupdate' => false,
            'comments' => '',
            'audited'    => true,
            'massupdate' => 0,
            'help' => '',
            'len' => 36,
        ),
        'parent_type' => array(
            'required'   => false,
            'name' => 'parent_type',
            'vname' => 'LBL_PARENT_TYPE',
            'type' => 'parent_type',
            'dbType' => 'varchar',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => 0,
            'len'        => 64,
            'massupdate' => false,
            'comments' => '',
            'audited'    => true,
            'massupdate' => 0,
            'help' => '',
        ),
        'parent_name' => array(
            'required' => false,
            'source' => 'non-db',
            'name' => 'parent_name',
            'vname' => 'LBL_FLEX_RELATE',
            'type' => 'parent',
            'massupdate' => 0,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '0',
            'audited' => 1,
            'reportable' => 0,
            'len' => 25,
            'studio' => 'visible',
            'type_name' => 'parent_type',
            'id_name' => 'parent_id',
            'parent_type' => 'record_type_display',
            'options' => 'parent_type_display',
         ),
    ),
    'indices' => array(
        array('name' => 'bug_number', 'type' => 'index', 'fields' => array('bug_number')),
        array('name' => 'idx_bug_name', 'type' => 'index', 'fields' => array('name')),
        array('name' => 'idx_bugs_assigned_user', 'type' => 'index', 'fields' => array('assigned_user_id')),
    ),
    'relationships' => array(
        'bug_tasks' => array(
            'lhs_module' => 'Bugs',
            'lhs_table' => 'bugs',
            'lhs_key' => 'id',
            'rhs_module' => 'Tasks',
            'rhs_table' => 'tasks',
            'rhs_key' => 'parent_id',
            'relationship_type' => 'one-to-many',
            'relationship_role_column' => 'parent_type',
            'relationship_role_column_value' => 'Bugs'
        ),
        'bug_meetings' => array(
            'lhs_module' => 'Bugs',
            'lhs_table' => 'bugs',
            'lhs_key' => 'id',
            'rhs_module' => 'Meetings',
            'rhs_table' => 'meetings',
            'rhs_key' => 'parent_id',
            'relationship_type' => 'one-to-many',
            'relationship_role_column' => 'parent_type',
            'relationship_role_column_value' => 'Bugs'
        ),
        'bug_calls' => array(
            'lhs_module' => 'Bugs',
            'lhs_table' => 'bugs',
            'lhs_key' => 'id',
            'rhs_module' => 'Calls',
            'rhs_table' => 'calls',
            'rhs_key' => 'parent_id',
            'relationship_type' => 'one-to-many',
            'relationship_role_column' => 'parent_type',
            'relationship_role_column_value' => 'Bugs'
        ),
        'bug_emails' => array(
            'lhs_module' => 'Bugs',
            'lhs_table' => 'bugs',
            'lhs_key' => 'id',
            'rhs_module' => 'Emails',
            'rhs_table' => 'emails',
            'rhs_key' => 'parent_id',
            'relationship_type' => 'one-to-many',
            'relationship_role_column' => 'parent_type',
            'relationship_role_column_value' => 'Bugs'
        ),
        'bug_notes' => array(
            'lhs_module' => 'Bugs',
            'lhs_table' => 'bugs',
            'lhs_key' => 'id',
            'rhs_module' => 'Notes',
            'rhs_table' => 'notes',
            'rhs_key' => 'parent_id',
            'relationship_type' => 'one-to-many',
            'relationship_role_column' => 'parent_type',
            'relationship_role_column_value' => 'Bugs'
        ),
        'bugs_assigned_user' => array(
            'lhs_module' => 'Users',
            'lhs_table' => 'users',
            'lhs_key' => 'id',
            'rhs_module' => 'Bugs',
            'rhs_table' => 'bugs',
            'rhs_key' => 'assigned_user_id',
            'relationship_type' => 'one-to-many'
        ),
        'bugs_modified_user' => array(
            'lhs_module' => 'Users',
            'lhs_table' => 'users',
            'lhs_key' => 'id',
            'rhs_module' => 'Bugs',
            'rhs_table' => 'bugs',
            'rhs_key' => 'modified_user_id',
            'relationship_type' => 'one-to-many'
        ),
        'bugs_created_by' => array(
            'lhs_module' => 'Users',
            'lhs_table' => 'users',
            'lhs_key' => 'id',
            'rhs_module' => 'Bugs',
            'rhs_table' => 'bugs',
            'rhs_key' => 'created_by',
            'relationship_type' => 'one-to-many'
        ),
        'bugs_release' => array(
            'lhs_module' => 'Releases',
            'lhs_table' => 'releases',
            'lhs_key' => 'id',
            'rhs_module' => 'Bugs',
            'rhs_table' => 'bugs',
            'rhs_key' => 'found_in_release',
            'relationship_type' => 'one-to-many'
        ),
        'bugs_fixed_in_release' => array(
            'lhs_module' => 'Releases',
            'lhs_table' => 'releases',
            'lhs_key' => 'id',
            'rhs_module' => 'Bugs',
            'rhs_table' => 'bugs',
            'rhs_key' => 'fixed_in_release',
            'relationship_type' => 'one-to-many'
        )
    ),
    'optimistic_locking' => true,
);

VardefManager::createVardef(
    'Bugs',
    'Bug',
    array(
        'default',
        'assignable',
        'security_groups',
        'issue',
    )
);
