<?php
require_once('modules/Calls_Reschedule/Calls_Reschedule.php');

function reschedule_history($focus, $field, $value, $view)
{
    global $app_list_strings,$mod_strings;

    if ($view == 'DetailView') {
        $html = '';
        $html .= '<ul id="history_list">';

        $query = "SELECT calls_reschedule.id FROM calls_reschedule JOIN users ON calls_reschedule.modified_user_id = users.id WHERE call_id='".$focus->id."' ORDER BY calls_reschedule.date_entered DESC";
        
        $result = $focus->db->query($query);
        
        $reschedule = BeanFactory::newBean('Calls_Reschedule');

        while ($row = $focus->db->fetchByAssoc($result)) {
            $reschedule->retrieve($row['id']);
                       
            $html .= '<li>'.$app_list_strings["call_reschedule_dom"][$reschedule->reason].' - '.$reschedule->date_entered.' '.$mod_strings['LBL_RESCHEDULED_BY'].' '.$reschedule->created_by_name.'</li>';
        }

        $html .= '</ul>';

        return $html;
    }
}

function reschedule_count($focus, $field, $value, $view)
{
    $query = "SELECT COUNT(*) FROM calls_reschedule WHERE call_id='".$focus->id."'";
    $result = $focus->db->getOne($query);

    $focus->reschedule_count = $result;
}
