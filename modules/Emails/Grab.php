<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $current_user;


$focus = BeanFactory::newBean('Emails');
// Get Group User IDs
$groupUserQuery = 'SELECT name, group_id FROM inbound_email ie INNER JOIN users u ON (ie.group_id = u.id AND u.is_group = 1)';
$r = $focus->db->query($groupUserQuery);
$groupIds = '';
while ($a = $focus->db->fetchByAssoc($r)) {
    $groupIds .= "'".$a['group_id']."', ";
}
$groupIds = substr($groupIds, 0, (strlen($groupIds) - 2));

$query = 'SELECT emails.id AS id FROM emails';
$query .= " WHERE emails.deleted = 0 AND emails.status = 'unread' AND emails.assigned_user_id IN ({$groupIds})";
//$query .= ' LIMIT 1';

$r2 = $focus->db->query($query);
$count = 0;
$a2 = $focus->db->fetchByAssoc($r2);

$focus->retrieve($a2['id']);
$focus->assigned_user_id = $current_user->id;
$focus->save();

if (!empty($a2['id'])) {
    header('Location: index.php?module=Emails&action=ListView&type=inbound&assigned_user_id='.$current_user->id);
} else {
    header('Location: index.php?module=Emails&action=ListView&show_error=true&type=inbound&assigned_user_id='.$current_user->id);
}
