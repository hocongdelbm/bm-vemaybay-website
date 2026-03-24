<?php
// Do not store anything in this file that is not part of the array or the hook version.  This file will	
// be automatically rebuilt in the future. 
$hook_version = 1;
$hook_array = array();
// position, file, function 
$hook_array['before_save'] = array();
$hook_array['before_save'][] = array(77, 'updateGeocodeInfo', 'modules/Opportunities/OpportunitiesJjwg_MapsLogicHook.php', 'OpportunitiesJjwg_MapsLogicHook', 'updateGeocodeInfo');
$hook_array['after_save'] = array();
$hook_array['after_save'][] = array(77, 'updateRelatedMeetingsGeocodeInfo', 'modules/Opportunities/OpportunitiesJjwg_MapsLogicHook.php', 'OpportunitiesJjwg_MapsLogicHook', 'updateRelatedMeetingsGeocodeInfo');
$hook_array['after_save'][] = array(78, 'updateRelatedProjectGeocodeInfo', 'modules/Opportunities/OpportunitiesJjwg_MapsLogicHook.php', 'OpportunitiesJjwg_MapsLogicHook', 'updateRelatedProjectGeocodeInfo');
$hook_array['after_relationship_add'] = array();
$hook_array['after_relationship_add'][] = array(77, 'addRelationship', 'modules/Opportunities/OpportunitiesJjwg_MapsLogicHook.php', 'OpportunitiesJjwg_MapsLogicHook', 'addRelationship');
$hook_array['after_relationship_delete'] = array();
$hook_array['after_relationship_delete'][] = array(77, 'deleteRelationship', 'modules/Opportunities/OpportunitiesJjwg_MapsLogicHook.php', 'OpportunitiesJjwg_MapsLogicHook', 'deleteRelationship');
