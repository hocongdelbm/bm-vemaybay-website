<?php
$dictionary['EC_Live_Chat_Messages'] = array(
    'table' => 'ec_live_chat_messages',
    'audited' => true,
    'inline_edit' => true,
    'duplicate_merge' => true,
    'fields' => array(
        // description => message

        'src' => array(
            'name' => 'src',
            'vname' => 'LBL_SRC',
            'type' => 'int',
            'dbtype' => 'tinyint',
            'comments' => '0: Send from BM to client ; 1: Send from client to BM',
            'required' => true,
            'audited' => true,
        ),

        'client_phone' => array(
            'name' => 'client_phone',
            'vname' => 'LBL_CLIENT_PHONE',
            'type' => 'varchar',
            'len' => 14,
            'default' => '',
            'importable' => true,
            'audited' => true,
        ),

        'client_url' => array(
            'name' => 'client_url',
            'vname' => 'LBL_CLIENT_URL',
            'type' => 'varchar',
            'len' => 200,
            'default' => '',
            'comment' => 'URL the visitor was viewing when the message was sent',
            'importable' => true,
            'audited' => true,
        ),

        'client_user_agent' => array(
            'name' => 'client_user_agent',
            'vname' => 'LBL_CLIENT_USER_AGENT',
            'type' => 'varchar',
            'len' => 255,
            'default' => '',
            'importable' => true,
            'audited' => false,
        ),

        'client_ip' => array(
            'name' => 'client_ip',
            'vname' => 'LBL_CLIENT_IP',
            'type' => 'varchar',
            'len' => 45,
            'default' => '',
            'comment' => 'IPv4/IPv6 address of the visitor',
            'importable' => true,
            'audited' => false,
        ),

        'sender_type' => array(
            'name' => 'sender_type',
            'vname' => 'LBL_SENDER_TYPE',
            'type' => 'varchar',
            'len' => 16,
            'default' => 'manual',
            'comment' => 'manual, bot, auto',
            'importable' => true,
            'audited' => true,
        ),

        'message_type' => array(
            'name' => 'message_type',
            'vname' => 'LBL_MESSAGE_TYPE',
            'type' => 'varchar',
            'len' => 16,
            'default' => 'text',
            'comment' => 'text, image, file, link',
            'importable' => true,
            'audited' => true,
        ),

        'attachment_url' => array(
            'name' => 'attachment_url',
            'vname' => 'LBL_ATTACHMENT_URL',
            'type' => 'varchar',
            'len' => 150,
            'default' => '',
            'importable' => true,
            'audited' => true,
        ),

        'attachment_name' => array(
            'name' => 'attachment_name',
            'vname' => 'LBL_ATTACHMENT_NAME',
            'type' => 'varchar',
            'len' => 80,
            'default' => '',
            'importable' => true,
            'audited' => false,
        ),

        'seen_at' => array(
            'name' => 'seen_at',
            'vname' => 'LBL_SEEN_AT',
            'type' => 'datetime',
            'comment' => 'When the message was seen by the recipient',
            'importable' => true,
            'audited' => true,
        ),

        'contact_id' => array(
            'name' => 'contact_id',
            'vname' => 'LBL_CONTACT',
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
        'contact' => array(
            'name' => 'contact',
            'vname' => 'LBL_CONTACT',
            'type' => 'relate',
            'source' => 'non-db',
            'id_name' => 'contact_id',
            'ext2' => 'Contacts',
            'module' => 'Contacts',
            'rname' => 'name',
            'quicksearch' => 'enabled',
            'studio' => 'visible',
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
        array('name' => 'idx_live_chat_client_phone', 'type' => 'index', 'fields' => array('client_phone')),
        array('name' => 'idx_live_chat_assign', 'type' => 'index', 'fields' => array('assigned_user_id')),
        array('name' => 'idx_live_chat_contact_id', 'type' => 'index', 'fields' => array('contact_id')),
        array('name' => 'idx_live_chat_booking_id', 'type' => 'index', 'fields' => array('booking_id')),
    ),
    'relationships' => array(),
    'optimistic_locking' => true,
    'unified_search' => true,
);

if (!class_exists('VardefManager')) {
    require_once('include/SugarObjects/VardefManager.php');
}
VardefManager::createVardef('EC_Live_Chat_Messages', 'EC_Live_Chat_Messages', array('basic', 'assignable', 'security_groups'));
