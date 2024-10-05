<?php

class loginActions
{

        function updateLoginAudit(&$la_bean, $la_event, $la_arguments = 'failed')
        {

                global $current_user, $db;

                // Sugar strangely doesn't populate event on login_failed
                if (empty(trim($la_event))) {
                        $la_event       = 'login_failed';
                        $agent_status   = 'Logged Out';
                }

                switch (trim($la_event)) {
                        case 'login_failed':
                                $la_result = "Failed";
                                break;
                        case 'after_login':
                                $agent_status   = 'Available';
                                $la_result      = "Success";
                                break;
                        case 'before_logout':
                                $la_result = "Logout";
                                break;
                        default:
                                return;
                }

                $uuid = create_guid();
                $typed_name     = $db->quote($_REQUEST['user_name']);
                $ip_address     = $_SERVER['REMOTE_ADDR'];
                $timestamp      = gmdate('Y-m-d H:i:s');
                $query = "INSERT INTO ec_loginaudit (id,name,date_entered,date_modified,modified_user_id,
                        created_by,description,deleted,assigned_user_id,ip_address,typed_name,is_admin,result)
                        VALUES
                        ('$uuid','','$timestamp', '$timestamp','$current_user->id','1','','0','1',
                        '$ip_address','$typed_name','$current_user->is_admin','$la_result')";
                $db->query($query);
        }
}
