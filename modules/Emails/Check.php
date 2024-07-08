<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $current_user;

if (isset($_REQUEST['type']) && $_REQUEST['type'] == 'personal') {
    if ($current_user->hasPersonalEmail()) {
        $ie = BeanFactory::newBean('InboundEmail');
        $beans = $ie->retrieveByGroupId($current_user->id);
        if (!empty($beans)) {
            /** @var InboundEmail $bean */
            foreach ($beans as $bean) {
                $bean->importMessages();
            }
        }
    }
    header('Location: index.php?module=Emails&action=ListView&type=inbound&assigned_user_id='.$current_user->id);
} elseif (isset($_REQUEST['type']) && $_REQUEST['type'] == 'group') {
    $ie = BeanFactory::newBean('InboundEmail');
    // this query only polls Group Inboxes
    $r = $ie->db->query('SELECT inbound_email.id FROM inbound_email JOIN users ON inbound_email.group_id = users.id WHERE inbound_email.deleted=0 AND inbound_email.status = \'Active\' AND mailbox_type != \'bounce\' AND users.deleted = 0 AND users.is_group = 1');

    while ($a = $ie->db->fetchByAssoc($r)) {
        $ieX = BeanFactory::newBean('InboundEmail');
        $ieX->retrieve($a['id']);
        $ieX->importMessages();
    }
    
    header('Location: index.php?module=Emails&action=ListViewGroup');
} else { // fail gracefully
    header('Location: index.php?module=Emails&action=index');
}
