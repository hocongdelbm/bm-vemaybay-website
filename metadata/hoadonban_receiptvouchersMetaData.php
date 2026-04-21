<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$dictionary['hoadonban_receiptvouchers'] = array(
    'table' => 'hoadonban_receiptvouchers',
    'fields' => array(
        array('name' => 'id', 'type' => 'varchar', 'len' => 36),
        array('name' => 'hoadon_id', 'type' => 'varchar', 'len' => 36),
        array('name' => 'receipt_id', 'type' => 'varchar', 'len' => 36),

        array('name' => 'date_modified', 'type' => 'datetime'),
        array('name' => 'deleted', 'type' => 'bool', 'len' => 1, 'required' => false, 'default' => 0)
    ),
    'indices' => array(
        array('name' => 'hoadonban_receiptvouchers_pk', 'type' => 'primary', 'fields' => array('id')),
        array('name' => 'idx_hoadonban_receiptvouchers_ak', 'type' => 'unique', 'fields' => array('hoadon_id', 'receipt_id')),
    ),
    'relationships' => array(
        'hoadonban_receiptvouchers' => array(
            'lhs_module' => 'EC_HoaDonBan',
            'lhs_table' => 'ec_hoadonban',
            'lhs_key' => 'id',
            'rhs_module' => 'EC_Receipt_Voucher',
            'rhs_table' => 'ec_receipt_voucher',
            'rhs_key' => 'id',
            'relationship_type' => 'many-to-many',
            'join_table' => 'hoadonban_receiptvouchers',
            'join_key_lhs' => 'hoadon_id',
            'join_key_rhs' => 'receipt_id'
        )
    )
);
