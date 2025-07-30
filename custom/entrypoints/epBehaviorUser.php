<?php 

if (isset($_POST['for']) && $_POST['for'] == 'saveBehaviorUser') {
     global $current_user;
     $time_current  = date('Y-m-d H:i:s', strtotime('+7 hour'));
     $user_name     = $current_user->user_name;
     $url_behavior  = $_POST['url_behavior'];
     $e_target_class  = isset($_POST['e_target']) ? $_POST['e_target']:'';

     content_logs_behavior($current_user->id, $time_current, $user_name, $url_behavior, $e_target_class);
}