<?php
class ProcessRecordLogicHook {
    public function custom_column(SugarBean $bean, $event, $arguments) {
        global $app_list_strings;

        // Format content (message)
        $arr_sms_content = ['send_sms', 'send_sms_list_static', 'send_sms_list_dynamic'];
        if(in_array($bean->type, $arr_sms_content)) $bean->content = substr($bean->content, 0, 64) . "...";
        else if($bean->type == 'send_zalo_broadcast') $bean->content = '<a href="'.$bean->content.'" target="_blank" style="text-decoration:underline">Bài viết OA liên kết</a>';
        else $bean->content;

        // Format message type
        switch($bean->message_type) {
            case 'customer_care':
                $bean->message_type = '<span class="text-primary">CSKH</span>';
                break;
            case 'advertisement':
                $bean->message_type = '<b class="text-danger">'.$GLOBALS['app_list_strings']['sms_logs_type_message_list'][$bean->message_type].'</b>';
                break;
            default:
                $bean->call_type;
        }

        // Format send to
        if($bean->send_from == 'OA Travelpass') $bean->send_from = '<b style="color:#0091ff">'.$bean->send_from.'</b>';

        // Format send to
        if(empty($bean->send_to) && !empty($bean->send_from)) $bean->send_to = "Hàng loạt";
        else $bean->send_to = '<b>'.$bean->send_to.'</b>';

        // Format status
        switch($bean->status) {
            case 'scheduled':
                $bean->status = '<b class="text-warning">'.$GLOBALS['app_list_strings']['sms_logs_status'][$bean->status].'</b>';
                break;
            case 'done':
                $bean->status = '<b class="text-success">'.$GLOBALS['app_list_strings']['sms_logs_status'][$bean->status].'</b>';
                break;
            case 'fail':
                $bean->status = '<b class="text-danger">'.$GLOBALS['app_list_strings']['sms_logs_status'][$bean->status].'</b>';
                break;
            default:
                $bean->status;
        }
    }
}
