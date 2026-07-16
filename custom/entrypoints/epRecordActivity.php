<?php

if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/**
 * Ghi nhận hoạt động của user cho các kênh KHÔNG sinh record tracker
 * (vd: chat widget chạy qua WebSocket ngoài CRM).
 *
 * Chèn 1 dòng vào bảng `tracker` cho current_user để job checkStatusOnlineUser
 * (chỉ đọc tracker) coi user còn hoạt động, tránh bị OFF oan.
 */

global $current_user, $db;

header('Content-Type: application/json');

if (empty($current_user) || empty($current_user->id)) {
    echo json_encode(['success' => false]);
    return;
}

$source = isset($_POST['source']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $_POST['source']) : 'activity';
if ($source === '') $source = 'activity';

$uid = $db->quote($current_user->id);
$now = gmdate('Y-m-d H:i:s'); // GMT - khớp tracker.date_modified
$sid = $db->quote(session_id() ?: '');
$act = $db->quote($source);

// tracker.id là int auto-increment -> không set; visible = 0 để không lọt vào "recently viewed".
$db->query(
    "INSERT INTO tracker (monitor_id, user_id, module_name, item_id, item_summary, action, session_id, visible, date_modified, deleted)
     VALUES ('', '$uid', 'Activity', '', 'External activity ping chat', '$act', '$sid', 0, '$now', 0)"
);

echo json_encode(['success' => true]);
