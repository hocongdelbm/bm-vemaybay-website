<?php 
 //WARNING: The contents of this file are auto-generated

if (!isset($hook_array) || !is_array($hook_array)) {
    $hook_array = array();
}
if (!isset($hook_array['after_save']) || !is_array($hook_array['after_save'])) {
    $hook_array['after_save'] = array();
}
$hook_array['after_save'][] = Array(99, 'AOW_Workflow', 'modules/AOW_WorkFlow/AOW_WorkFlow.php','AOW_WorkFlow', 'run_bean_flows');
?>