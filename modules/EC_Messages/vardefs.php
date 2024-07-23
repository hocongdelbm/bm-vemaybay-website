<?php
$dictionary['EC_Messages'] = array(
    'table' => 'ec_messages',
    'audited' => true,
    'inline_edit' => true,
    'duplicate_merge' => true,
    'fields' => array(
        'send_from' => array(
            'name'       => 'send_from',
            'vname'      => 'LBL_SEND_FROM',
            'type'       => 'varchar',
            'len'        => 30,
            'required'   => true,
            'default'    => '',
            'importable' => true,
            'reportable' => false,
            'audited'    => true,
        ),

        'send_to' => array(
            'name'       => 'send_to',
            'vname'      => 'LBL_SEND_TO',
            'type'       => 'varchar',
            'len'        => 30,
            'required'   => false,
            'default'    => '',
            'importable' => true,
            'reportable' => false,
            'audited'    => true,
        ),

        'content' => array(
            'name'       => 'content',
            'vname'      => 'LBL_CONTENT',
            'type'       => 'varchar',
            'len'        => 300,
            'required'   => false,
            'default'    => '',
            'importable' => true,
            'reportable' => false,
            'audited'    => true,
        ),

        // Loại tin (Zalo, SMS,...)
        'type' => array(
            'name'      => 'type',
            'vname'     => 'LBL_TYPE',
            'type'      => 'enum',
            'dbtype'    => 'char',
            'options'   => 'message_type_list',
            'len'       => 32,
            'required'  => true,
            'default'   => '',
            'massupdate' => false,
            'importable' => true,
            'audited'    => true,
            'reportable' => false,
            'studio'     => 'visible',
        ),

        // Loại tin (CSKH, Ad, Transaction,..,)
        'category' => array(
            'name'      => 'category',
            'vname'     => 'LBL_CATEGORY',
            'type'      => 'enum',
            'dbtype'    => 'char',
            'options'   => 'message_category_list',
            'len'       => 40,
            'required'  => false,
            'default'   => '',
            'massupdate' => false,
            'importable' => true,
            'audited'    => true,
            'reportable' => false,
            'studio'     => 'visible'
        ),

        'status' => array(
            'name'       => 'status',
            'vname'      => 'LBL_STATUS',
            'type'       => 'enum',
            'options'    => 'message_status',
            'default'    => 'new',
            'len'        => 12,
            'required'   => false,
            'audited'    => true,
            'reportable' => false,
        ),

        // Thời gian gửi tin - YYYY/MM/dd HH:mm:ss
        'send_time' => array(
            'name'       => 'send_time',
            'vname'      => 'LBL_SEND_TIME',
            'type'       => 'datetimecombo',
            'required'   => false,
            'default'    => '',
            'importable' => true,
            'reportable' => false,
            'audited'    => true,
            'enable_range_search' => true,
            'options' => 'date_range_search_dom',
        ),

        'parent_type' => array(
            'name'       => 'parent_type',
            'vname'      => 'LBL_PARENT_TYPE',
            'type'       => 'varchar',
            'len'        => 64,
            'required'   => false,
            'default'    => '',
            'audited'    => true,
            'importable' => true,
            'reportable' => false,
            'studio'     => 'hidden',
        ),

        'parent_id' => array(
            'name'       => 'parent_id',
            'vname'      => 'LBL_PARENT_ID',
            'type'       => 'id',
            'len'        => 36,
            'required'   => false,
            'default'    => '',
            'audited'    => true,
            'importable' => true,
            'reportable' => false,
        ),

        // Request data
        'data' => array(
            'name'       => 'data',
            'vname'      => 'LBL_DATA',
            'type'       => 'text',
            'default'    => '',
            'importable' => true,
            'reportable' => false,
            'audited'    => false,
        ),

        'response' => array(
            'name'       => 'response',
            'vname'      => 'LBL_RESPONSE',
            'type'       => 'text',
            'default'    => '',
            'importable' => true,
            'reportable' => false,
            'audited'    => true,
        ),

        // Đường dẫn file dữ liệu gửi tin (Dùng cho gửi SMS hàng loạt)
        'file' => array(
            'name'       => 'file',
            'vname'      => 'LBL_FILE',
            'type'       => 'file',
            'required'   => false,
            'default'    => '',
            'importable' => true,
            'reportable' => true,
            'audited'    => true,
        ),

        'cost' => array(
            'name' => 'cost',
            'vname' => 'LBL_COST',
            'type' => 'int',
            'dbtype' => 'tinyint',
            'default' => 0,
            'required' => true,
            'importable' => true,
            'audited' => true,
        ),
    ),
    'indices' => array(
        array('name' => 'idx_messages_send_from', 'type' => 'index', 'fields' => array('send_from')),
        array('name' => 'idx_messages_send_to', 'type' => 'index', 'fields' => array('send_to')),
        array('name' => 'idx_messages_send_time','type' => 'index','fields' => array('send_time')),
        array('name' => 'idx_messages_parent','type' => 'index','fields' => array('parent_id', 'parent_type')),
        array('name' => 'idx_messages_category', 'type' => 'index', 'fields' => array('category')),
        array('name' => 'idx_messages_type', 'type' => 'index', 'fields' => array('type')),
    ),
    'relationships' => array(),
    'optimistic_locking' => true,
    'unified_search' => true,
);
if (!class_exists('VardefManager')) {
    require_once('include/SugarObjects/VardefManager.php');
}
VardefManager::createVardef('EC_Messages', 'EC_Messages', array('basic', 'assignable', 'security_groups'));
