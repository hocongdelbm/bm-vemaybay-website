<?php

/**
 * Kiểm tra SDT đã tồn tại hay chưa
 * Sử dụng cho module Contacts
 * 
 * @param mixed $module_table
 * @param mixed $phoneNumber
 * @return bool true/false
 */
function isExitsPhoneNumber($module_table, $phoneNumber)
{
    global $db;

    if (empty($phoneNumber)) {
        return false;
    }

    $phoneNumber = trim($phoneNumber);
    $column = strlen($phoneNumber) > 15 ? 'zalo_id' : 'phone_mobile';
    $phoneNumber = $db->quote($phoneNumber);
    $table = strtolower($module_table);

    $sql = "SELECT id FROM {$table}
            WHERE {$column} = '{$phoneNumber}' 
            AND deleted = 0
            LIMIT 1";

    $result = $db->query($sql);
    $row = $db->fetchByAssoc($result);
    return $row ? true : false;
}
