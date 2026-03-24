<?php
// Do not store anything in this file that is not part of the array or the hook version.  This file will	
// be automatically rebuilt in the future. 
$hook_version  = 1;
$hook_array    = array();

// position, file, function 
$hook_array['before_save']    = array();
$hook_array['before_save'][]  = array(77, 'updateGeocodeInfo', 'modules/Accounts/AccountsJjwg_MapsLogicHook.php', 'AccountsJjwg_MapsLogicHook', 'updateGeocodeInfo');
$hook_array['after_save']     = array();
$hook_array['after_save'][]   = array(77, 'updateRelatedMeetingsGeocodeInfo', 'modules/Accounts/AccountsJjwg_MapsLogicHook.php', 'AccountsJjwg_MapsLogicHook', 'updateRelatedMeetingsGeocodeInfo');
$hook_array['after_save'][]   = array(78, 'updateRelatedProjectGeocodeInfo', 'modules/Accounts/AccountsJjwg_MapsLogicHook.php', 'AccountsJjwg_MapsLogicHook', 'updateRelatedProjectGeocodeInfo');
$hook_array['after_save'][]   = array(79, 'updateRelatedOpportunitiesGeocodeInfo', 'modules/Accounts/AccountsJjwg_MapsLogicHook.php', 'AccountsJjwg_MapsLogicHook', 'updateRelatedOpportunitiesGeocodeInfo');
$hook_array['after_save'][]   = array(80, 'updateRelatedCasesGeocodeInfo', 'modules/Accounts/AccountsJjwg_MapsLogicHook.php', 'AccountsJjwg_MapsLogicHook', 'updateRelatedCasesGeocodeInfo');
$hook_array['after_relationship_add']        = array();
$hook_array['after_relationship_add'][]      = array(77, 'addRelationship', 'modules/Accounts/AccountsJjwg_MapsLogicHook.php', 'AccountsJjwg_MapsLogicHook', 'addRelationship');
$hook_array['after_relationship_delete']     = array();
$hook_array['after_relationship_delete'][]   = array(77, 'deleteRelationship', 'modules/Accounts/AccountsJjwg_MapsLogicHook.php', 'AccountsJjwg_MapsLogicHook', 'deleteRelationship');

// Custom by DucPham 12/05/2023
$hook_array["process_record"]    = array();
$hook_array["process_record"][]  = array(1, "showEmailAddress", "custom/modules/Accounts/AccountsLogicHook.php", "AccountsLogicHook", "showEmailAddress");
