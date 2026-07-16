<?php

if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/**
 * Ghi nhận hoạt động của user cho các thao tác KHÔNG sinh record tracker
 * (thao tác AJAX trong-trang, chat widget WebSocket, cuộc gọi softphone...).
 *
 * Chỉ UPDATE cột last_activity trên dòng ec_online_report của user (1 dòng/ngày)
 * -> KHÔNG phát sinh dòng mới, không làm phình bảng. Job checkStatusOnlineUser
 * đọc last_activity để coi user còn hoạt động, tránh OFF oan.
 */

global $current_user, $db;

header('Content-Type: application/json');

if (empty($current_user) || empty($current_user->id)) {
    echo json_encode(['success' => false]);
    return;
}

$uid      = $db->quote($current_user->id);
$now      = gmdate('Y-m-d H:i:s'); // GMT - đồng nhất với $timedate->nowDb()
$today_vn = (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d');

// Chỉ cập nhật (không tạo mới). User không đủ điều kiện online (không có dòng) -> no-op.
$db->query(
    "UPDATE ec_online_report
     SET last_activity = '$now'
     WHERE assigned_user_id = '$uid'
         AND DATE(DATE_ADD(date_entered, INTERVAL 7 HOUR)) = '$today_vn'
         AND deleted = 0"
);

echo json_encode(['success' => true]);
