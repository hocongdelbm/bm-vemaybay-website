<?php
class BugsLogicHook
{
    public function custom_column_list_view(SugarBean $bean, $event, $arguments)
    {
        global $app_list_strings, $current_user;

        // TRẠNG THÁI
        $resolution = $app_list_strings['bug_resolution_dom'][$bean->resolution];
        switch ($bean->resolution) {
            case 'Accepted':
                $bean->resolution = '<b class="text-success">' . $resolution . '</b>';
                break;
            case 'Following':
                $bean->resolution = '<b class="text-warning">' . $resolution . '</b>';
                break;
            case 'Duplicate':
                $bean->resolution = '<b class="text-secondary">' . $resolution . '</b>';
                break;
            case 'Out of Date':
            case 'Invalid':
                $bean->resolution = '<b class="text-danger">' . $resolution . '</b>';
                break;
            case 'Fixed':
                $bean->resolution = '<b class="text-primary">' . $resolution . '</b>';
                break;
            case 'Later':
                $bean->resolution = '<b class="text-info">' . $resolution . '</b>';
                break;
            default:
                $bean->resolution = '<b class="text-dark">' . $resolution . '</b>';
        }

        // LOẠI
        $type = $app_list_strings['bug_type_dom'][$bean->type];
        switch ($bean->type) {
            case 'Defect':
                $bean->type = '<b class="text-danger">' . $type . '</b>';
                break;
            case 'Feature':
                $bean->type = '<b class="text-info">' . $type . '</b>';
                break;
            case 'Require':
                $bean->type = '<b class="text-warning">' . $type . '</b>';
                break;
            default:
                $bean->type = '<b class="text-dark">' . $type . '</b>';
        }

        // LOẠI
        $status = $app_list_strings['bug_status_dom'][$bean->status];
        switch ($bean->status) {
            case 'Assigned':
                $bean->status = '<b class="text-primary">' . $status . '</b>';
                break;
            case 'Pending':
                $bean->status = '<b class="text-warning">' . $status . '</b>';
                break;
            case 'Rejected':
                $bean->status = '<b class="text-danger">' . $status . '</b>';
                break;
            default:
                $bean->status = '<b class="text-dark">' . $status . '</b>';
        }

        // ĐỘ ƯU TIÊN
        $priority = $app_list_strings['bug_priority_dom'][$bean->priority];
        switch ($bean->priority) {
            case 'Urgent':
                $bean->priority = '<b class="text-danger">' . $priority . '</b>';
                break;
            case 'High':
                $bean->priority = '<b class="text-warning">' . $priority . '</b>';
                break;
            case 'Medium':
                $bean->priority = '<b class="text-primary">' . $priority . '</b>';
                break;
            default:
                $bean->priority = '<b class="text-dark">' . $priority . '</b>';
        }

    }
}
