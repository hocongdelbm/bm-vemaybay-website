<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}


if (!is_admin($current_user)) {
    sugar_die("Unauthorized access to administration.");
}

global $timedate;

$callBean = BeanFactory::newBean('Calls');
$callQuery = "SELECT * FROM calls where calls.status != 'Held' and calls.deleted=0";

$result = $callBean->db->query($callQuery, true, "");
$row = $callBean->db->fetchByAssoc($result);
while ($row != null) {
    $date_end = $timedate->fromDb($row['date_start'])->modify("+{$row['duration_hours']} hours {$row['duration_minutes']} mins")->asDb();
    $updateQuery = "UPDATE calls set calls.date_end='{$date_end}' where calls.id='{$row['id']}'";
    $call = BeanFactory::newBean('Calls');
    $call->db->query($updateQuery);
    $row = $callBean->db->fetchByAssoc($result);
}

$meetingBean = BeanFactory::newBean('Meetings');
$meetingQuery = "SELECT * FROM meetings where meetings.status != 'Held' and meetings.deleted=0";

$result = $meetingBean->db->query($meetingQuery, true, "");
$row = $meetingBean->db->fetchByAssoc($result);
while ($row != null) {
    $date_end = $timedate->fromDb($row['date_start'])->modify("+{$row['duration_hours']} hours {$row['duration_minutes']} mins")->asDb();
    $updateQuery = "UPDATE meetings set meetings.date_end='{$date_end}' where meetings.id='{$row['id']}'";
    $call = BeanFactory::newBean('Calls');
    $call->db->query($updateQuery);
    $row = $callBean->db->fetchByAssoc($result);
}
echo $mod_strings['LBL_DIAGNOSTIC_DONE'];
