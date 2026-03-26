<?php
// Do not store anything in this file that is not part of the array or the hook version.  This file will	
// be automatically rebuilt in the future. 
$hook_version = 1;
$hook_array = array();
// position, file, function 
$hook_array['before_save'] = array();
$hook_array['before_save'][] = array(10, 'Save case updates', 'modules/AOP_Case_Updates/CaseUpdatesHook.php', 'CaseUpdatesHook', 'saveUpdate');
$hook_array['before_save'][] = array(11, 'Save case events', 'modules/AOP_Case_Events/CaseEventsHook.php', 'CaseEventsHook', 'saveUpdate');
$hook_array['before_save'][] = array(12, 'Case closure prep', 'modules/AOP_Case_Updates/CaseUpdatesHook.php', 'CaseUpdatesHook', 'closureNotifyPrep');
$hook_array['after_save'] = array();
$hook_array['after_save'][] = array(10, 'Send contact case closure email', 'modules/AOP_Case_Updates/CaseUpdatesHook.php', 'CaseUpdatesHook', 'closureNotify');
$hook_array['after_relationship_add'] = array();
$hook_array['after_relationship_add'][] = array(9, 'Assign account', 'modules/AOP_Case_Updates/CaseUpdatesHook.php', 'CaseUpdatesHook', 'assignAccount');
$hook_array['after_relationship_add'][] = array(10, 'Send contact case email', 'modules/AOP_Case_Updates/CaseUpdatesHook.php', 'CaseUpdatesHook', 'creationNotify');
$hook_array['after_retrieve'] = array();
$hook_array['after_retrieve'][] = array(10, 'Filter HTML', 'modules/AOP_Case_Updates/CaseUpdatesHook.php', 'CaseUpdatesHook', 'filterHTML');
