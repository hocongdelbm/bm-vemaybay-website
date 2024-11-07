<?php
// Do not store anything in this file that is not part of the array or the hook version.  This file will	
// be automatically rebuilt in the future. 
 $hook_version = 1; 
$hook_array = Array(); 
// position, file, function 
$hook_array['process_record'] = Array(); 
$hook_array['process_record'][] = Array(1, 'count', 'modules/Calls_Reschedule/reschedule_count.php','reschedule_count', 'count'); 

$hook_array['process_record'][] = Array(
    2,
    'custom_column',
    'custom/modules/Calls/ProcessRecordLogicHook.php',
    'ProcessRecordLogicHook',
    'custom_column'
);

$hook_array['process_record'][] = Array(
    3,
    'get duration call value',
    'custom/modules/Calls/ProcessRecordLogicHook.php',
    'ProcessRecordLogicHook',
    'getDurationCallsValue'
);

?>