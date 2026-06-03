<?php
$dictionary['EC_Zalo_Messages'] = array(
    'table' => 'ec_zalo_messages',
    'audited' => true,
    'inline_edit' => false,
    'duplicate_merge' => false,
    'fields' => array(
        // description => message

        'message_id' => array(
            'name' => 'message_id',
            'vname' => 'LBL_MESSAGE_ID',
            'type' => 'varchar',
            'len' => 64,
            'default' => '',
            'required' => true,
            'importable' => true,
            'audited' => true,
        ),

        'src' => array(
            'name' => 'src',
            'vname' => 'LBL_SRC',
            'type' => 'int',
            'dbtype' => 'tinyint',
            'comments' => '0: Send from OA to user ; 1: Send from user to OA',
            'required' => true,
            'importable' => true,
            'audited' => true,
        ),

        'from_id' => array(
            'name' => 'from_id',
            'vname' => 'LBL_FROM_ID',
            'type' => 'varchar',
            'len' => 28,
            'default' => '',
            'required' => true,
            'importable' => true,
            'audited' => true,
        ),

        'to_id' => array(
            'name' => 'to_id',
            'vname' => 'LBL_TO_ID',
            'type' => 'varchar',
            'len' => 28,
            'default' => '',
            'required' => true,
            'importable' => true,
            'audited' => true,
        ),

        'timestamp' => array(
            'name' => 'timestamp',
            'vname' => 'LBL_TIMESTAMP',
            'type' => 'int',
            'dbtype' => 'bigint',
            'default' => 0,
            'comment' => 'Milliseconds format, +7 timezone',
            'required' => true,
            'importable' => true,
            'audited' => true,
        ),
        // non-db helper field to show datetime picker in views
        'timestamp_dt' => array(
            'name' => 'timestamp_dt',
            'vname' => 'LBL_TIMESTAMP',
            'type' => 'datetime',
            'source' => 'non-db',
            'importable' => true,
            'studio' => array('editview' => true, 'detailview' => true, 'quickcreate' => true),
        ),

        'type' => array(
            'name' => 'type',
            'vname' => 'LBL_TYPE',
            'type' => 'enum',
            'options' => 'zalo_message_type_list',
            'len' => 20,
            'default' => '',
            'comment' => 'zbs, consultation, promotion, call',
            'importable' => true,
            'audited' => true,
        ),

        'sub_type' => array(
            'name' => 'sub_type',
            'vname' => 'LBL_SUB_TYPE',
            'type' => 'varchar',
            'len' => 30,
            'default' => '',
            'comment' => 'Sub type by type',
            'importable' => true,
            'audited' => true,
        ),

        'thumbnail' => array(
            'name' => 'thumbnail',
            'vname' => 'LBL_THUMBNAIL',
            'type' => 'varchar',
            'len' => 150,
            'default' => '',
            'importable' => true,
            'audited' => true,
        ),

        'url' => array(
            'name' => 'url',
            'vname' => 'LBL_URL',
            'type' => 'varchar',
            'len' => 150,
            'default' => '',
            'importable' => true,
            'audited' => true,
        ),

        'attached_description' => array(
            'name' => 'attached_description',
            'vname' => 'LBL_ATTACHED_DESCRIPTION',
            'type' => 'varchar',
            'len' => 128,
            'default' => '',
            'importable' => true,
            'audited' => true,
        ),

        'latitude' => array(
            'name' => 'latitude',
            'vname' => 'LBL_LATITUDE',
            'type' => 'varchar',
            'len' => 20,
            'default' => '',
            'comment' => 'Latitude in location message',
            'importable' => true,
            'audited' => true,
        ),

        'longitude' => array(
            'name' => 'longitude',
            'vname' => 'LBL_LONGITUDE',
            'type' => 'varchar',
            'len' => 20,
            'default' => '',
            'comment' => 'Longitude in location message',
            'importable' => true,
            'audited' => true,
        ),

        'quote_message_id' => array(
            'name' => 'quote_message_id',
            'vname' => 'LBL_QUOTE_MESSAGE_ID',
            'type' => 'id',
            'default' => '',
            'importable' => true,
            'audited' => true,
        ),

        'template_id' => array(
            'name' => 'template_id',
            'vname' => 'LBL_TEMPLATE_ID',
            'type' => 'varchar',
            'len' => 20,
            'default' => '',
            'comment' => 'Using for ZBS',
            'importable' => true,
            'audited' => true,
        ),

        'data' => array(
            'name' => 'data',
            'vname' => 'LBL_DATA',
            'type' => 'varchar',
            'len' => 2056,
            'default' => '',
            'comment' => 'Data used to send messages',
            'importable' => true,
            'audited' => true,
        ),

        'response' => array(
            'name' => 'response',
            'vname' => 'LBL_RESPONSE',
            'type' => 'varchar',
            'len' => 2056,
            'default' => '',
            'importable' => false,
            'audited' => true,
        ),

        'cost' => array(
            'name' => 'cost',
            'vname' => 'LBL_COST',
            'type' => 'int',
            'default' => 0,
            'required' => true,
            'importable' => true,
            'audited' => true,
        ),
        'booking_id' => array(
            'name' => 'booking_id',
            'vname' => 'LBL_BOOKING',
            'type' => 'id',
            'len' => 36,
            'required' => false,
            'default' => '',
            'massupdate' => 0,
            'importable' => 1,
            'audited' => 1,
            'reportable' => 0,
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => 0,
        ),
        'booking' => array(
            'name' => 'booking',
            'vname' => 'LBL_BOOKING',
            'type' => 'relate',
            'source' => 'non-db',
            'id_name' => 'booking_id',
            'ext2' => 'EC_Flight_Bookings',
            'module' => 'EC_Flight_Bookings',
            'rname' => 'name',
            'quicksearch' => 'enabled',
            'studio' => 'visible',
        ),
    ),
    'indices' => array(
        array('name' => 'idx_zalo_messages_from_id', 'type' => 'index', 'fields' => array('from_id')),
        array('name' => 'idx_zalo_messages_to_id', 'type' => 'index', 'fields' => array('to_id')),
        array('name' => 'idx_zalo_messages_message_id', 'type' => 'index', 'fields' => array('message_id')),
        array('name' => 'idx_zalo_messages_quote_message_id', 'type' => 'index', 'fields' => array('quote_message_id')),
        array('name' => 'idx_zalo_messages_booking_id', 'type' => 'index', 'fields' => array('booking_id')),
    ),
    'relationships' => array(),
    'optimistic_locking' => true,
    'unified_search' => true,
);
if (!class_exists('VardefManager')) {
    require_once('include/SugarObjects/VardefManager.php');
}
VardefManager::createVardef('EC_Zalo_Messages', 'EC_Zalo_Messages', array('basic', 'assignable', 'security_groups'));
