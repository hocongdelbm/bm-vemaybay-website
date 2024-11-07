<?php
class ProcessRecordLogicHook {
    public function custom_column(SugarBean $bean, $event, $arguments) {
        // Format content (message)
        if(in_array($bean->type, ['sms', 'sms_campaign_static', 'sms_campaign_dynamic']))
            $bean->content = substr($bean->content, 0, 64) . "...";
        else if($bean->type == 'zalo_broadcast')
            $bean->content = '<a href="'.$bean->content.'" target="_blank" style="text-decoration:underline">Bài viết OA liên kết</a>';
        else
            $bean->content;

        // Format category
        switch($bean->category) {
            case 'customer_care':
                $bean->category = '<span class="text-primary">CSKH</span>';
                break;
            case 'advertisement':
                $bean->category = '<b class="text-danger">'.$GLOBALS['app_list_strings']['message_category_list'][$bean->message_type].'</b>';
                break;
            default:
                $bean->category;
        }

        // Format send from
        if($bean->send_from == '2941581384627345950') $bean->send_from = '<b style="color:#0091ff">OA Tìm chuyến bay</b>';

        // Format send to
        if(empty($bean->send_to) && !empty($bean->send_from)) $bean->send_to = "Hàng loạt";
        else $bean->send_to = '<b>'.$bean->send_to.'</b>';

        // Format status
        switch($bean->status) {
            case 'scheduled':
                $bean->status = '<b class="text-warning">'.$GLOBALS['app_list_strings']['message_status'][$bean->status].'</b>';
                break;
            case 'done':
                $bean->status = '<b class="text-success">'.$GLOBALS['app_list_strings']['message_status'][$bean->status].'</b>';
                break;
            case 'fail':
                $bean->status = '<b class="text-danger">'.$GLOBALS['app_list_strings']['message_status'][$bean->status].'</b>';
                break;
            default:
                $bean->status;
        }
    }
}
