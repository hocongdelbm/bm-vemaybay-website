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

    $phoneNumber = $db->quote($phoneNumber);

    $sql = "SELECT id FROM " . strtolower($module_table) . "
               WHERE phone_mobile = '{$phoneNumber}' 
               AND deleted = 0";
    $result = $db->query($sql);
    $row = $db->fetchByAssoc($result);
    return $row ? true : false;
}