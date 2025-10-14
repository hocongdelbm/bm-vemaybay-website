<?php

/**
 * Kiểm tra SĐT đã tồn tại hay chưa
 * Sử dụng cho module Contacts
 * 
 * @param string $module_table
 * @param string $phoneNumber
 * @return bool
 */
function isExitsPhoneNumber($module_table, $phoneNumber) {
    global $db;

    $module_table = strtolower($module_table);
    $phoneNumber = trim($phoneNumber);

    if (!is_string($phoneNumber) || strlen($phoneNumber) < 10) return false;

    $column = strlen($phoneNumber) > 15 ? 'zalo_id' : 'phone_mobile';
    $phoneNumber = $db->quote($phoneNumber);
    
    $sql = "SELECT id FROM {$module_table} WHERE {$column} = '{$phoneNumber}' AND deleted = 0 ORDER BY date_entered LIMIT 1";
    $result = $db->query($sql);
    $row = $db->fetchByAssoc($result);
    return $row ? true : false;
}
