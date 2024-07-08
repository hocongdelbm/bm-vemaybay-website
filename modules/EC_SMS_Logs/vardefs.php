<?php
$dictionary['EC_SMS_Logs'] = array(
    'table' => 'ec_sms_logs',
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
        
        'type' => array(
            'name'      => 'type',
            'vname'     => 'LBL_TYPE',
            'type'      => 'enum',
            'dbtype'    => 'char',
            'options'   => 'sms_logs_type_list',
            'len'       => 32,
            'required'  => false,
            'default'   => 'send_sms', // Gửi tin
            'massupdate' => false,
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '',
            'audited' => 0,
            'reportable' => 0,
            'studio' => 'visible',
            'dependency' => false,
        ),

        'message_type' => array(
            'name'      => 'message_type',
            'vname'     => 'LBL_MESSAGE_TYPE',
            'type'      => 'enum',
            'dbtype'    => 'char',
            'options'   => 'sms_logs_type_message_list',
            'len'       => 32,
            'required'  => false,
            'default'   => 'customer_care', // CSKH
            'massupdate' => false,
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '',
            'audited' => 0,
            'reportable' => 0,
            'studio' => 'visible',
            'dependency' => false,
        ),

        // Thời gian gửi tin YYYY/MM/dd HH:mm:ss
        'send_date' => array(
            'name'       => 'send_date',
            'vname'      => 'LBL_SEND_DATE',
            'type'       => 'datetimecombo',
            'required'   => false,
            'default'    => '',
            'importable' => true,
            'reportable' => false,
            'audited'    => true,
            'enable_range_search' => true,
            'options' => 'date_range_search_dom',
        ),

        // Đường dẫn file dữ liệu gửi tin
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

        // Dữ liệu gửi tin (chuyển đổi từ file)
        'data' => array(
            'name'       => 'data',
            'vname'      => 'LBL_DATA',
            'type'       => 'longtext',
            'default'    => '',
            'importable' => true,
            'reportable' => false,
            'audited'    => false,
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

        // Campaign id trên tổng đài hoặc zalo (dùng check tin nhắn đã gửi thành công hay chưa)
        'campaign_id' => array(
            'name'       => 'campaign_id',
            'vname'      => 'LBL_CAMPAIGN_ID',
            'type'       => 'varchar',
            'len'        => 50,
            'required'   => false,
            'default'    => '',
            'audited'    => true,
            'reportable' => false,
        ),

        // Link download result
        'url_download' => array(
            'name'       => 'url_download',
            'vname'      => 'LBL_URL_DOWNLOAD',
            'type'       => 'varchar',
            'len'        => 128,
            'required'   => false,
            'default'    => '',
            'audited'    => true,
            'reportable' => false,
        ),

        'status' => array(
            'name'       => 'status',
            'vname'      => 'LBL_STATUS',
            'type'       => 'enum',
            'options'    => 'sms_logs_status',
            'default'    => 'new',
            'len'        => 12,
            'required'   => false,
            'audited'    => true,
            'reportable' => false,
        ),
    ),
    'indices' => array(
        array('name' => 'idx_smslog_name', 'type' => 'index', 'fields' => array('name')),
        array('name' => 'idx_smslog_assign', 'type' => 'index', 'fields' => array('assigned_user_id')),
        array('name' => 'idx_smslog_campaign', 'type' => 'index', 'fields' => array('campaign_id')),
        array('name' => 'idx_smslog_sendfrom', 'type' => 'index', 'fields' => array('send_from')),
        array('name' => 'idx_smslog_sendto', 'type' => 'index', 'fields' => array('send_to')),
        array('name' => 'idx_smslog_parent', 'type' => 'index', 'fields' => array('parent_id')),
    ),
    'relationships' => array(),
    'optimistic_locking' => true,
    'unified_search' => true,
);
if (!class_exists('VardefManager')) {
    require_once('include/SugarObjects/VardefManager.php');
}
VardefManager::createVardef('EC_SMS_Logs', 'EC_SMS_Logs', array('basic', 'assignable', 'security_groups'));
