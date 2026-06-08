<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$dictionary['EC_Post_Categories'] = array(
    'table'           => 'ec_post_categories',
    'audited'         => true,
    'inline_edit'     => true,
    'duplicate_merge' => true,
    'fields' => array(
        'slug' => array(
            'name'  => 'slug',
            'vname' => 'LBL_SLUG',
            'type'  => 'varchar',
            'len'   => 255,
        ),
        'parent_id' => array(
            'name'  => 'parent_id',
            'vname' => 'LBL_PARENT_CATEGORY',
            'type'  => 'id',
        ),
        // non-db field — giá trị được set bởi process_record hook
        'parent_category' => array(
            'name'    => 'parent_category',
            'vname'   => 'LBL_PARENT_CATEGORY',
            'type'    => 'varchar',
            'source'  => 'non-db',
            'len'     => 255,
        ),
        'thumbnail_url' => array(
            'name'  => 'thumbnail_url',
            'vname' => 'LBL_THUMBNAIL_URL',
            'type'  => 'varchar',
            'len'   => 500,
        ),
        'post_count' => array(
            'name'    => 'post_count',
            'vname'   => 'LBL_POST_COUNT',
            'type'    => 'int',
            'default' => 0,
            'source'  => 'non-db',
        ),
        'term_order' => array(
            'name'    => 'term_order',
            'vname'   => 'LBL_TERM_ORDER',
            'type'    => 'int',
            'default' => 0,
        ),
    ),
    'relationships'      => array(),
    'optimistic_locking' => true,
    'unified_search'     => true,
);

if (!class_exists('VardefManager')) {
    require_once('include/SugarObjects/VardefManager.php');
}
VardefManager::createVardef('EC_Post_Categories', 'EC_Post_Categories', array('basic', 'assignable', 'security_groups'));