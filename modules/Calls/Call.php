<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class Call extends SugarBean
{
    public $field_name_map;
    // Stored fields
    public $id;
    public $json_id;
    public $date_entered;
    public $date_modified;
    public $assigned_user_id;
    public $modified_user_id;
    public $description;
    public $name;
    public $status;
    public $date_start;
    public $time_start;
    public $duration_hours;
    public $duration_minutes;
    public $date_end;
    public $parent_type;
    public $parent_type_options;
    public $parent_id;
    public $contact_id;
    public $user_id;
    public $lead_id;
    public $direction;
    public $reminder_time;
    public $reminder_time_options;
    public $reminder_checked;
    public $email_reminder_time;
    public $email_reminder_checked;
    public $email_reminder_sent;
    public $required;
    public $accept_status;
    public $created_by;
    public $created_by_name;
    public $modified_by_name;
    public $parent_name;
    public $contact_name;
    public $contact_phone;
    public $contact_email;
    public $account_id;
    public $opportunity_id;
    public $case_id;
    public $assigned_user_name;
    public $note_id;
    public $outlook_id;
    public $update_vcal = true;
    public $contacts_arr;
    public $users_arr;
    public $leads_arr;
    public $default_call_name_values = array('Assemble catalogs', 'Make travel arrangements', 'Send a letter', 'Send contract', 'Send fax', 'Send a follow-up letter', 'Send literature', 'Send proposal', 'Send quote');
    public $minutes_value_default = 15;
    public $minutes_values = array('0' => '00', '15' => '15', '30' => '30', '45' => '45');
    public $table_name = "calls";
    public $rel_users_table = "calls_users";
    public $rel_contacts_table = "calls_contacts";
    public $rel_leads_table = "calls_leads";
    public $module_dir = 'Calls';
    public $object_name = "Call";
    public $new_schema = true;
    public $importable = true;
    public $syncing = false;
    public $recurring_source;

    // This is used to retrieve related fields from form posts.
    public $additional_column_fields = array('assigned_user_name', 'assigned_user_id', 'contact_id', 'user_id', 'contact_name');
    public $relationship_fields = array(
        'account_id'        => 'accounts',
        'opportunity_id'    => 'opportunities',
        'contact_id'        => 'contacts',
        'case_id'            => 'cases',
        'user_id'            => 'users',
        'assigned_user_id'    => 'users',
        'note_id'            => 'notes',
        'lead_id'            => 'leads',
    );

    public function __construct()
    {
        parent::__construct();
        global $app_list_strings;

        $this->setupCustomFields('Calls');

        foreach ($this->field_defs as $field) {
            $this->field_name_map[$field['name']] = $field;
        }

        if (!empty($GLOBALS['app_list_strings']['duration_intervals'])) {
            $this->minutes_values = $GLOBALS['app_list_strings']['duration_intervals'];
        }
    }

    /**
     * Disable edit if call is recurring and source is not Sugar. It should be edited only from Outlook.
     * @param $view string
     * @param $is_owner bool
     */
    public function ACLAccess($view, $is_owner = 'not_set', $in_group = 'not_set')
    {
        // don't check if call is being synced from Outlook
        if ($this->syncing == false) {
            $view = strtolower($view);
            switch ($view) {
                case 'edit':
                case 'save':
                case 'editview':
                case 'delete':
                    if (!empty($this->recurring_source) && $this->recurring_source != "Sugar") {
                        return false;
                    }
            }
        }
        return parent::ACLAccess($view, $is_owner, $in_group);
    }

    // save date_end by calculating user input
    // this is for calendar
    private static $remindersInSaving = false;

    public function save($check_notify = false)
    {
        global $timedate, $current_user, $app_list_strings, $sugar_config;

        // if (!empty($this->date_start)) {
        //     if (!empty($this->duration_hours) && !empty($this->duration_minutes)) {
        //         $td = $timedate->fromDb($this->date_start);
        //         if ($td) {
        //             $this->date_end = $td->modify(
        //                 "+{$this->duration_hours} hours {$this->duration_minutes} mins"
        //             )->asDb();
        //         }
        //     } else {
        //         $this->date_end = $this->date_start;
        //     }
        // }

        // custom subject - Mã cuộc gọi
        $is_tele = 0;
        if (empty($this->name)) {
            $date = date('ymd', strtotime('+7 hours', strtotime(date('d-m-Y H:i:s'))));
            $sql_date = date('Y-m-d', strtotime('+7 hours', strtotime(date('d-m-Y H:i:s'))));

            $total_row = $this->db->getOne("SELECT COUNT(id) + 1 FROM calls WHERE DATE_FORMAT(DATE_ADD(date_entered, INTERVAL 7 HOUR), '%Y-%m-%d') = '" . $sql_date . "'");
            $this->name = 'CALL-' . $date . '-' . $total_row;

            $is_tele = 1;
        }

        // if (!empty($_REQUEST['send_invites']) && $_REQUEST['send_invites'] == '1') {
        //     $check_notify = true;
        // } else {
        //     $check_notify = false;
        // }
        // if (empty($_REQUEST['send_invites'])) {
        //     if (!empty($this->id)) {
        //         $old_record = BeanFactory::newBean('Calls');
        //         $old_record->retrieve($this->id);
        //         $old_assigned_user_id = $old_record->assigned_user_id;
        //     }
        //     if ((empty($this->id) && isset($_REQUEST['assigned_user_id']) && !empty($_REQUEST['assigned_user_id']) && $GLOBALS['current_user']->id != $_REQUEST['assigned_user_id']) || (isset($old_assigned_user_id) && !empty($old_assigned_user_id) && isset($_REQUEST['assigned_user_id']) && !empty($_REQUEST['assigned_user_id']) && $old_assigned_user_id != $_REQUEST['assigned_user_id'])) {
        //         $this->special_notification = true;
        //         if (!isset($GLOBALS['resavingRelatedBeans']) || $GLOBALS['resavingRelatedBeans'] == false) {
        //             $check_notify = true;
        //         }
        //         if (isset($_REQUEST['assigned_user_name'])) {
        //             $this->new_assigned_user_name = $_REQUEST['assigned_user_name'];
        //         }
        //     }
        // }
        // if (empty($this->status)) {
        //     $this->status = $this->getDefaultStatus();
        // }

        // prevent a mass mailing for recurring meetings created in Calendar module
        // if (empty($this->id) && !empty($_REQUEST['module']) && $_REQUEST['module'] == "Calendar" && !empty($_REQUEST['repeat_type']) && !empty($this->repeat_parent_id)) {
        //     $check_notify = false;
        // }
        /*nsingh 7/3/08  commenting out as bug #20814 is invalid
        if($current_user->getPreference('reminder_time')!= -1 &&  isset($_POST['reminder_checked']) && isset($_POST['reminder_time']) && $_POST['reminder_checked']==0  && $_POST['reminder_time']==-1){
        	$this->reminder_checked = '1';
        	$this->reminder_time = $current_user->getPreference('reminder_time');
        }*/

        $return_id = parent::save($check_notify);

        // KPI FOR CALLS - Hungnh
        if (
            (string)$this->status === 'done'
            && !empty($this->description)
            && (
                ((int)$this->call_talk >= 20 && strtolower((string)$this->direction) === 'outbound')
                || ((int)$this->call_talk > 0 && strtolower((string)$this->direction) === 'inbound')
            )
        ) {
            switch ($this->type_call_sources) {
                case 'called':
                    if (!isWorkingProcessExisting($this->module_dir, $this->id, 'called') && empty($this->booking_id)) {
                        myCreateWorkingProcess($this->module_dir, $this->id, $this->name, $this->description . ' (Calls)', $current_user->id, 'called');
                    }
                    else if (!isWorkingProcessExisting($this->module_dir, $this->id, 'called') && !empty($this->booking_id)) {
                        if ($this->is_ExitsRowKpi('EC_Flight_Bookings', $this->booking_id)) {
                            $this->updateKpiField('EC_Flight_Bookings', $this->booking_id, 'called');
                        } else {
                            myCreateWorkingProcess($this->module_dir, $this->id, $this->name, $this->description . ' (Call Have Bookings)', $current_user->id, 'called');
                        }
                    } 
                    break;
                case 'recall':
                    $this->updateKpiField('EC_Flight_Bookings', $this->booking_id, 'recall');
                    break;
                case 'remind':
                    $this->updateKpiField('EC_Flight_Bookings', $this->booking_id, 'remind');
                    // Update ec_booking_itineraries
                    $update_remind = "UPDATE ec_booking_itineraries 
                                    SET is_remind = 1
                                    WHERE id = '" . trim($this->journey_id) . "'
                                    AND deleted = 0";
                    $this->db->query($update_remind);
                    break;
            }
        }

        // if ($this->update_vcal) {
        //     vCal::cache_sugar_vcal($current_user);
        // }

        // if (isset($_REQUEST['reminders_data']) && !self::$remindersInSaving) {
        //     self::$remindersInSaving = true;
        //     $reminderData = json_encode(
        //         $this->removeUnInvitedFromReminders(json_decode(html_entity_decode($_REQUEST['reminders_data']), true))
        //     );
        //     Reminder::saveRemindersDataJson('Calls', $return_id, $reminderData);
        //     self::$remindersInSaving = false;
        // }

        // Nhỡ, đến => tele
        $user_list = get_user_array(true, '', '', true);

        if ($is_tele == 1) {
            if ((string)$this->direction === 'missed' || (string)$this->direction === 'inbound') {
                $log = json_decode(html_entity_decode($this->log), true);
                $text_name_agent = '';
                
                $info_phone = getInfoCallSource($this->call_to);
                $format_phone   = isset($info_phone['format_phone']) && !empty($info_phone['format_phone']) ? $info_phone['format_phone'] : $info_phone['phone'];
                $site           = isset($info_phone['website']) && !empty($info_phone['website']) ? $info_phone['website'] : $this->call_sources;

                if ((string)$this->direction === 'missed') {
                    if (isset($log['list_agent']) && !empty($log['list_agent'])) {
                        $list_agent_missed = explode(',', $log['list_agent']);
                        foreach ($list_agent_missed as $agent) {
                            $user_id = custom_get_sip_number($agent);
                            $user_name[] = $user_list[$user_id];
                        }
                        $text_name_agent = implode(" - ", $user_name);
                    }
                    $text = $app_list_strings['calls_direction_list'][$this->direction] . ' : ' . $this->call_from . ' - gọi vào <b>' . $format_phone . '</b> - ' . $site . ' - thời lượng ' . $log['call_duration'] . 's - lời chào & chuông ' . ($log['call_duration'] - $log['call_talk']) . 's - hội thoại ' . $log['call_talk'] . 's lúc ' . date('H:i:s', strtotime($this->date_start)) . ' - ' . $text_name_agent . '';
                } else if ((string)$this->direction === 'inbound') {
                    if (isset($log['list_agent']) && !empty($log['list_agent'])) {
                        $list_agent_inbound = explode(',', $log['list_agent']);

                        if (count($list_agent_inbound) > 1) {
                            $element_last =  array_pop($list_agent_inbound); //101
                            $text_name_agent = $user_list[custom_get_sip_number($element_last)];
                        } else {
                            $text_name_agent = $user_list[custom_get_sip_number($list_agent_inbound[0])];
                        }

                    } else {
                        $text_name_agent = $user_list[custom_get_sip_number($log['dialed'])];
                    }
                    $text = $app_list_strings['calls_direction_list'][$this->direction] . ' : ' . $this->call_from . ' - gọi vào <b>' . $format_phone . '</b> - ' . $site . ' - thời lượng ' . $log['call_duration'] . 's - lời chào & chuông ' . ($log['call_duration'] - $log['call_talk']) . 's - hội thoại ' . $log['call_talk'] . 's lúc ' . date('H:i:s', strtotime($this->date_start)) . ' - ' . $text_name_agent . '';
                }

                // myTelegramSendMessage(
                //     json_encode(array(
                //         'text' => $text,
                //         'parse_mode' => 'HTML',
                //         'reply_markup' => array(
                //             'inline_keyboard' => array(
                //                 array(
                //                     array(
                //                         'text' => 'Mở cuộc gọi',
                //                         'url' => $sugar_config['site_url'] . '/index.php?module=' . $this->object_name . 's&record=' . $this->id . '&action=DetailView&dothis=true',
                //                     ),
                //                 ),
                //             ),
                //         ),
                //     )),
                //     $app_list_strings['system_config_list']['telegram_token_id'],
                //     $app_list_strings['system_config_list']['telegram_chat_id'],
                // );
                try {
                    $text = str_replace('<b>', '**', $text);
                    $text = str_replace('</b>', '**', $text);
                    $link = Mattermost::markdownLink($sugar_config['site_url'] . "/index.php?module={$this->object_name}s&record={$this->id}&action=DetailView&dothis=true", "Mở cuộc gọi");
                    $message = Mattermost::$line_separation;
                    $message .= $text;
                    $message .= "\n\n$link";
                    Mattermost::sendMessage($sugar_config['mattermost']['channel_id_cty'] ?? '', $message);
                }
                catch(Throwable $th) {}
            }
        }

        return $return_id;
    }

    /**
     * Check isExits ec_working_process with called = 0.
     * field: booking_id => parent_id, 
     * author: hungnh
     */
    public function is_ExitsRowKpi($parent_type, $parentId)
    {
        $query = "SELECT COUNT(*) 
                    FROM ec_working_process
                    WHERE called = 0
                    AND parent_type = '{$parent_type}'
                    AND parent_id = '{$parentId}'
                    AND deleted = 0";

        $count = $this->db->getOne($query);
        return $count > 0;
    }

    /**
     * Update KPI field function to reduce code duplication.
     * author: hungnh
     */
    public function updateKpiField($parent_type, $parentId, $field)
    {
        $sql = 'UPDATE ec_working_process
                SET ' . $field . ' = 1
                WHERE ' . $field . ' = 0
                AND parent_id = "' . $parentId . '"
                AND parent_type = "' . $parent_type . '"
                AND deleted = 0';

        $this->db->query($sql);
    }


    /**
     * @param array $reminders
     * @return array
     */
    public function removeUnInvitedFromReminders($reminders)
    {
        $reminderData = $reminders;
        $uninvited = array();
        foreach ($reminders as $r => $reminder) {
            foreach ($reminder['invitees'] as $i => $invitee) {
                switch ($invitee['module']) {
                    case "Users":
                        if (in_array($invitee['module_id'], $this->users_arr) === false) {
                            // add to uninvited
                            $uninvited[] = $reminderData[$r]['invitees'][$i];
                            // remove user
                            unset($reminderData[$r]['invitees'][$i]);
                        }
                        break;
                    case "Contacts":
                        if (in_array($invitee['module_id'], $this->contacts_arr) === false) {
                            // add to uninvited
                            $uninvited[] = $reminderData[$r]['invitees'][$i];
                            // remove contact
                            unset($reminderData[$r]['invitees'][$i]);
                        }
                        break;
                    case "Leads":
                        if (in_array($invitee['module_id'], $this->leads_arr) === false) {
                            // add to uninvited
                            $uninvited[] = $reminderData[$r]['invitees'][$i];
                            // remove lead
                            unset($reminderData[$r]['invitees'][$i]);
                        }
                        break;
                }
            }
        }
        return $reminderData;
    }

    /** Returns a list of the associated contacts
     * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
     * All Rights Reserved..
     * Contributor(s): ______________________________________..
     */
    public function get_contacts()
    {
        // First, get the list of IDs.
        $query = "SELECT contact_id as id from calls_contacts where call_id='$this->id' AND deleted=0";

        $contact = BeanFactory::newBean('Contacts');
        return $this->build_related_list($query, $contact);
    }


    public function get_summary_text()
    {
        return (string)$this->name;
    }

    public function create_list_query($order_by, $where, $show_deleted = 0)
    {
        $custom_join = $this->getCustomJoin();
        $query = "SELECT ";
        $query .= "
			calls.*,";
        if (preg_match("/calls_users\.user_id/", $where)) {
            $query .= "calls_users.required,
				calls_users.accept_status,";
        }

        $query .= "
			users.user_name as assigned_user_name";
        $query .= $custom_join['select'];

        // this line will help generate a GMT-metric to compare to a locale's timezone

        if (preg_match("/contacts/", $where)) {
            $query .= ", contacts.first_name, contacts.last_name";
            $query .= ", contacts.assigned_user_id contact_name_owner";
        }
        $query .= " FROM calls ";

        if (preg_match("/contacts/", $where)) {
            $query .=    "LEFT JOIN calls_contacts
	                    ON calls.id=calls_contacts.call_id
	                    LEFT JOIN contacts
	                    ON calls_contacts.contact_id=contacts.id ";
        }
        if (preg_match('/calls_users\.user_id/', $where)) {
            $query .= "LEFT JOIN calls_users
			ON calls.id=calls_users.call_id and calls_users.deleted=0 ";
        }
        $query .= "
			LEFT JOIN users
			ON calls.assigned_user_id=users.id ";
        $query .= $custom_join['join'];
        $where_auto = '1=1';
        if ($show_deleted == 0) {
            $where_auto = " $this->table_name.deleted=0  ";
        } elseif ($show_deleted == 1) {
            $where_auto = " $this->table_name.deleted=1 ";
        }

        //$where_auto .= " GROUP BY calls.id";

        if ($where != "") {
            $query .= "where $where AND " . $where_auto;
        } else {
            $query .= "where " . $where_auto;
        }

        $order_by = $this->process_order_by($order_by);
        if (empty($order_by)) {
            $order_by = 'calls.name';
        }
        $query .= ' ORDER BY ' . $order_by;

        return $query;
    }

    public function create_export_query($order_by, $where, $relate_link_join = '')
    {
        $custom_join = $this->getCustomJoin(true, true, $where);
        $custom_join['join'] .= $relate_link_join;
        $contact_required = stristr($where, "contacts");
        if ($contact_required) {
            $query = "SELECT calls.*, contacts.first_name, contacts.last_name, users.user_name as assigned_user_name ";
            $query .= $custom_join['select'];
            $query .= " FROM contacts, calls, calls_contacts ";
            $where_auto = "calls_contacts.contact_id = contacts.id AND calls_contacts.call_id = calls.id AND calls.deleted=0 AND contacts.deleted=0";
        } else {
            $query = 'SELECT calls.*, users.user_name as assigned_user_name ';
            $query .= $custom_join['select'];
            $query .= ' FROM calls ';
            $where_auto = "calls.deleted=0";
        }


        $query .= "  LEFT JOIN users ON calls.assigned_user_id=users.id ";

        $query .= $custom_join['join'];

        if ($where != "") {
            $query .= "where $where AND " . $where_auto;
        } else {
            $query .= "where " . $where_auto;
        }

        $order_by = $this->process_order_by($order_by);
        if (empty($order_by)) {
            $order_by = 'calls.name';
        }
        $query .= ' ORDER BY ' . $order_by;

        return $query;
    }

    public function fill_in_additional_detail_fields()
    {
        global $locale;
        parent::fill_in_additional_detail_fields();
        if (!empty($this->contact_id)) {
            $query  = "SELECT first_name, last_name FROM contacts ";
            $query .= "WHERE id='$this->contact_id' AND deleted=0";
            $result = $this->db->limitQuery($query, 0, 1, true, " Error filling in additional detail fields: ");

            // Get the contact name.
            $row = $this->db->fetchByAssoc($result);
            $GLOBALS['log']->info("additional call fields $query");
            if ($row != null) {
                $this->contact_name = $locale->getLocaleFormattedName($row['first_name'], $row['last_name'], '', '');
                $GLOBALS['log']->debug("Call($this->id): contact_name = $this->contact_name");
                $GLOBALS['log']->debug("Call($this->id): contact_id = $this->contact_id");
            }
        }
        if (!isset($this->duration_minutes)) {
            $this->duration_minutes = $this->minutes_value_default;
        }

        global $timedate;
        //setting default date and time
        if (is_null($this->date_start)) {
            $this->date_start = $timedate->now();
        }

        if (is_null($this->duration_hours)) {
            $this->duration_hours = "0";
        }
        if (is_null($this->duration_minutes)) {
            $this->duration_minutes = "1";
        }

        $this->fill_in_additional_parent_fields();

        global $app_list_strings;
        $parent_types = $app_list_strings['record_type_display'];
        $disabled_parent_types = ACLController::disabledModuleList($parent_types, false, 'list');
        foreach ($disabled_parent_types as $disabled_parent_type) {
            if ($disabled_parent_type != $this->parent_type) {
                unset($parent_types[$disabled_parent_type]);
            }
        }

        $this->parent_type_options = get_select_options_with_id($parent_types, $this->parent_type);

        if (empty($this->reminder_time)) {
            $this->reminder_time = -1;
        }

        if (empty($this->id)) {
            $reminder_t = $GLOBALS['current_user']->getPreference('reminder_time');
            if (isset($reminder_t)) {
                $this->reminder_time = $reminder_t;
            }
        }
        $this->reminder_checked = $this->reminder_time == -1 ? false : true;

        if (empty($this->email_reminder_time)) {
            $this->email_reminder_time = -1;
        }
        if (empty($this->id)) {
            $reminder_t = $GLOBALS['current_user']->getPreference('email_reminder_time');
            if (isset($reminder_t)) {
                $this->email_reminder_time = $reminder_t;
            }
        }
        $this->email_reminder_checked = $this->email_reminder_time == -1 ? false : true;

        if (isset($_REQUEST['parent_type']) && empty($this->parent_type)) {
            $this->parent_type = $_REQUEST['parent_type'];
        } elseif (is_null($this->parent_type)) {
            $this->parent_type = $app_list_strings['record_type_default_key'];
        }
    }


    // public function get_list_view_data()
    // {
    //     $call_fields = $this->get_list_view_array();
    //     global $app_list_strings, $focus, $action, $currentModule;
    //     if (isset($focus->id)) {
    //         $id = $focus->id;
    //     } else {
    //         $id = '';
    //     }
    //     if (isset($this->parent_type) && $this->parent_type != null) {
    //         $call_fields['PARENT_MODULE'] = $this->parent_type;
    //     }
    //     if ($this->status == "Planned") {
    //         //cn: added this if() to deal with sequential Closes in Meetings.  this is a hack to a hack (formbase.php->handleRedirect)
    //         if (empty($action)) {
    //             $action = "index";
    //         }

    //         $setCompleteUrl = "<b><a id='{$this->id}' class='list-view-data-icon' title='" . translate('LBL_CLOSEINLINE') . "' onclick='SUGAR.util.closeActivityPanel.show(\"{$this->module_dir}\",\"{$this->id}\",\"Held\",\"listview\",\"1\");'>";
    //         if ($this->ACLAccess('edit')) {
    //             $call_fields['SET_COMPLETE'] = $setCompleteUrl . "<span class='suitepicon suitepicon-action-clear'></span></a></b>";
    //         } else {
    //             $call_fields['SET_COMPLETE'] = '';
    //         }
    //     }
    //     global $timedate;
    //     $today = $timedate->nowDb();
    //     $nextday = $timedate->asDbDate($timedate->getNow()->modify("+1 day"));
    //     if (!isset($call_fields['DATE_START'])) {
    //         LoggerManager::getLogger()->warn('Call has not DATE_START field for list view data.');
    //     }
    //     $mergeTime = isset($call_fields['DATE_START']) ? $call_fields['DATE_START'] : null; //$timedate->merge_date_time($call_fields['DATE_START'], $call_fields['TIME_START']);
    //     $date_db = $timedate->to_db($mergeTime);
    //     if ($date_db    < $today) {
    //         if ($call_fields['STATUS'] == 'Held' || $call_fields['STATUS'] == 'Not Held') {
    //             $call_fields['DATE_START'] = "<font>" . $call_fields['DATE_START'] . "</font>";
    //         } else {
    //             if (!isset($call_fields['DATE_START'])) {
    //                 LoggerManager::getLogger()->warn('Call field has not START_DATE when trying to get list view data.');
    //                 $dateStart = null;
    //             } else {
    //                 $dateStart = $call_fields['DATE_START'];
    //             }
    //             $call_fields['DATE_START'] = "<font class='overdueTask'>" . $dateStart . "</font>";
    //         }
    //     } elseif ($date_db < $nextday) {
    //         $call_fields['DATE_START'] = "<font class='todaysTask'>" . $call_fields['DATE_START'] . "</font>";
    //     } else {
    //         $call_fields['DATE_START'] = "<font class='futureTask'>" . $call_fields['DATE_START'] . "</font>";
    //     }
    //     $this->fill_in_additional_detail_fields();

    //     //make sure we grab the localized version of the contact name, if a contact is provided
    //     if (!empty($this->contact_id)) {
    //         // Bug# 46125 - make first name, last name, salutation and title of Contacts respect field level ACLs
    //         $contact_temp = BeanFactory::getBean("Contacts", $this->contact_id);
    //         if (!empty($contact_temp)) {
    //             $contact_temp->_create_proper_name_field();
    //             $this->contact_name = $contact_temp->full_name;
    //         }
    //     }

    //     $call_fields['CONTACT_ID'] = $this->contact_id;
    //     $call_fields['CONTACT_NAME'] = $this->contact_name;
    //     $call_fields['PARENT_NAME'] = $this->parent_name;
    //     $call_fields['REMINDER_CHECKED'] = $this->reminder_time == -1 ? false : true;
    //     $call_fields['EMAIL_REMINDER_CHECKED'] = $this->email_reminder_time == -1 ? false : true;

    //     return $call_fields;
    // }

    public function get_list_view_data()
    {
        $call_fields = $this->get_list_view_array();
        global $app_list_strings, $focus, $action, $currentModule;

        if (isset($this->parent_type) && $this->parent_type != null) {
            $call_fields['PARENT_MODULE'] = $this->parent_type;
        }
        if ($this->status == "Planned") {
            if (empty($action)) {
                $action = "index";
            }
        }

        $this->fill_in_additional_detail_fields();

        //make sure we grab the localized version of the contact name, if a contact is provided
        if (!empty($this->contact_id)) {
            // Bug# 46125 - make first name, last name, salutation and title of Contacts respect field level ACLs
            $contact_temp = BeanFactory::getBean("Contacts", $this->contact_id);
            if (!empty($contact_temp)) {
                $contact_temp->_create_proper_name_field();
                $this->contact_name = $contact_temp->full_name;
            }
        }

        $call_fields['CONTACT_ID'] = $this->contact_id;
        $call_fields['CONTACT_NAME'] = $this->contact_name;
        $call_fields['PARENT_NAME'] = $this->parent_name;
        $call_fields['REMINDER_CHECKED'] = $this->reminder_time == -1 ? false : true;
        $call_fields['EMAIL_REMINDER_CHECKED'] = $this->email_reminder_time == -1 ? false : true;

        return $call_fields;
    }

    public function set_notification_body($xtpl, $call)
    {
        global $sugar_config;
        global $app_list_strings;
        global $current_user;
        global $app_list_strings;
        global $timedate;

        // rrs: bug 42684 - passing a contact breaks this call
        $notifyUser = ($call->current_notify_user->object_name == 'User') ? $call->current_notify_user : $current_user;


        // Assumes $call dates are in user format
        $calldate = $timedate->fromDb($call->date_start);

        // Đổi typeof date_start và date_end từ datetime thành varchar nên fix lỗi timedate
        // $xOffset = $timedate->asUser($calldate, $notifyUser).' '.$timedate->userTimezoneSuffix($calldate, $notifyUser);
        $xOffset = '';

        if (strtolower(get_class($call->current_notify_user)) == 'contact') {
            $xtpl->assign("ACCEPT_URL", $sugar_config['site_url'] .
                '/index.php?entryPoint=acceptDecline&module=Calls&contact_id=' . $call->current_notify_user->id . '&record=' . $call->id);
        } elseif (strtolower(get_class($call->current_notify_user)) == 'lead') {
            $xtpl->assign("ACCEPT_URL", $sugar_config['site_url'] .
                '/index.php?entryPoint=acceptDecline&module=Calls&lead_id=' . $call->current_notify_user->id . '&record=' . $call->id);
        } else {
            $xtpl->assign("ACCEPT_URL", $sugar_config['site_url'] .
                '/index.php?entryPoint=acceptDecline&module=Calls&user_id=' . $call->current_notify_user->id . '&record=' . $call->id);
        }

        $xtpl->assign("CALL_TO", $call->current_notify_user->new_assigned_user_name);
        $xtpl->assign("CALL_SUBJECT", $call->name);
        $xtpl->assign("CALL_STARTDATE", $xOffset);
        $xtpl->assign("CALL_HOURS", $call->duration_hours);
        $xtpl->assign("CALL_MINUTES", $call->duration_minutes);
        $xtpl->assign("CALL_STATUS", ((isset($call->status)) ? $app_list_strings['call_status_dom'][$call->status] : ""));
        $xtpl->assign("CALL_DESCRIPTION", nl2br($call->description));

        return $xtpl;
    }

    public function get_call_users()
    {
        $template = BeanFactory::newBean('Users');
        // First, get the list of IDs.
        $query = "SELECT calls_users.required, calls_users.accept_status, calls_users.user_id from calls_users where calls_users.call_id='$this->id' AND calls_users.deleted=0";
        $GLOBALS['log']->debug("Finding linked records $this->object_name: " . $query);
        $result = $this->db->query($query, true);
        $list = array();

        while ($row = $this->db->fetchByAssoc($result)) {
            $template = BeanFactory::newBean('Users'); // PHP 5 will retrieve by reference, always over-writing the "old" one
            $record = $template->retrieve($row['user_id']);
            $template->required = $row['required'];
            $template->accept_status = $row['accept_status'];

            if ($record != null) {
                // this copies the object into the array
                $list[] = $template;
            }
        }
        return $list;
    }

    public function get_invite_calls(&$user)
    {
        $template = $this;
        // First, get the list of IDs.
        $query = "SELECT calls_users.required, calls_users.accept_status, calls_users.call_id from calls_users where calls_users.user_id='$user->id' AND ( calls_users.accept_status IS NULL OR  calls_users.accept_status='none') AND calls_users.deleted=0";
        $GLOBALS['log']->debug("Finding linked records $this->object_name: " . $query);

        $result = $this->db->query($query, true);
        $list = array();

        while ($row = $this->db->fetchByAssoc($result)) {
            $record = $template->retrieve($row['call_id']);
            $template->required = $row['required'];
            $template->accept_status = $row['accept_status'];

            if ($record != null) {
                // this copies the object into the array
                $list[] = $template;
            }
        }
        return $list;
    }

    public function set_accept_status(&$user, $status)
    {
        if ($user->object_name == 'User') {
            $relate_values = array('user_id' => $user->id, 'call_id' => $this->id);
            $data_values = array('accept_status' => $status);
            $this->set_relationship($this->rel_users_table, $relate_values, true, true, $data_values);
            global $current_user;

            if ($this->update_vcal) {
                vCal::cache_sugar_vcal($user);
            }
        } elseif ($user->object_name == 'Contact') {
            $relate_values = array('contact_id' => $user->id, 'call_id' => $this->id);
            $data_values = array('accept_status' => $status);
            $this->set_relationship($this->rel_contacts_table, $relate_values, true, true, $data_values);
        } elseif ($user->object_name == 'Lead') {
            $relate_values = array('lead_id' => $user->id, 'call_id' => $this->id);
            $data_values = array('accept_status' => $status);
            $this->set_relationship($this->rel_leads_table, $relate_values, true, true, $data_values);
        }
    }

    public function get_notification_recipients()
    {
        if ($this->special_notification) {
            return parent::get_notification_recipients();
        }

        //		$GLOBALS['log']->debug('Call.php->get_notification_recipients():'.print_r($this,true));
        $list = array();
        if (!is_array($this->contacts_arr)) {
            $this->contacts_arr =    array();
        }

        if (!is_array($this->users_arr)) {
            $this->users_arr =    array();
        }

        if (!is_array($this->leads_arr)) {
            $this->leads_arr =    array();
        }

        foreach ($this->users_arr as $user_id) {
            $notify_user = BeanFactory::newBean('Users');
            $notify_user->retrieve($user_id);
            $notify_user->new_assigned_user_name = $notify_user->full_name;
            $GLOBALS['log']->info("Notifications: recipient is $notify_user->new_assigned_user_name");
            $list[$notify_user->id] = $notify_user;
        }

        foreach ($this->contacts_arr as $contact_id) {
            $notify_user = BeanFactory::newBean('Contacts');
            $notify_user->retrieve($contact_id);
            $notify_user->new_assigned_user_name = $notify_user->full_name;
            $GLOBALS['log']->info("Notifications: recipient is $notify_user->new_assigned_user_name");
            $list[$notify_user->id] = $notify_user;
        }

        foreach ($this->leads_arr as $lead_id) {
            $notify_user = BeanFactory::newBean('Leads');
            $notify_user->retrieve($lead_id);
            $notify_user->new_assigned_user_name = $notify_user->full_name;
            $GLOBALS['log']->info("Notifications: recipient is $notify_user->new_assigned_user_name");
            $list[$notify_user->id] = $notify_user;
        }
        global $sugar_config;
        if (isset($sugar_config['disable_notify_current_user']) && $sugar_config['disable_notify_current_user']) {
            global $current_user;
            if (isset($list[$current_user->id])) {
                unset($list[$current_user->id]);
            }
        }
        //		$GLOBALS['log']->debug('Call.php->get_notification_recipients():'.print_r($list,true));
        return $list;
    }

    public function bean_implements($interface)
    {
        switch ($interface) {
            case 'ACL':
                return true;
        }
        return false;
    }

    public function listviewACLHelper()
    {
        $array_assign = parent::listviewACLHelper();
        $is_owner = false;
        $in_group = false; //SECURITY GROUPS
        if (!empty($this->parent_name)) {
            if (!empty($this->parent_name_owner)) {
                global $current_user;
                $is_owner = $current_user->id == $this->parent_name_owner;
            }
            /* BEGIN - SECURITY GROUPS */
            //parent_name_owner not being set for whatever reason so we need to figure this out
            elseif (!empty($this->parent_type) && !empty($this->parent_id)) {
                global $current_user;
                $parent_bean = BeanFactory::getBean($this->parent_type, $this->parent_id);
                if ($parent_bean !== false) {
                    $is_owner = $current_user->id == $parent_bean->assigned_user_id;
                }
            }
            require_once("modules/SecurityGroups/SecurityGroup.php");
            $in_group = SecurityGroup::groupHasAccess($this->parent_type, $this->parent_id, 'view');
            /* END - SECURITY GROUPS */
        }

        /* BEGIN - SECURITY GROUPS */
        if (!ACLController::moduleSupportsACL($this->parent_type) || ACLController::checkAccess($this->parent_type, 'view', $is_owner, 'module', $in_group)) {
            /* END - SECURITY GROUPS */
            $array_assign['PARENT'] = 'a';
        } else {
            $array_assign['PARENT'] = 'span';
        }
        $is_owner = false;
        $in_group = false; //SECURITY GROUPS
        if (!empty($this->contact_name)) {
            if (!empty($this->contact_name_owner)) {
                global $current_user;
                $is_owner = $current_user->id == $this->contact_name_owner;
            }
            /* BEGIN - SECURITY GROUPS */
            //contact_name_owner not being set for whatever reason so we need to figure this out
            else {
                global $current_user;
                $parent_bean = BeanFactory::getBean('Contacts', $this->contact_id);
                if ($parent_bean !== false) {
                    $is_owner = $current_user->id == $parent_bean->assigned_user_id;
                }
            }
            require_once("modules/SecurityGroups/SecurityGroup.php");
            $in_group = SecurityGroup::groupHasAccess('Contacts', $this->contact_id, 'view');
            /* END - SECURITY GROUPS */
        }
        /* BEGIN - SECURITY GROUPS */
        if (ACLController::checkAccess('Contacts', 'view', $is_owner, 'module', $in_group)) {
            /* END - SECURITY GROUPS */
            $array_assign['CONTACT'] = 'a';
        } else {
            $array_assign['CONTACT'] = 'span';
        }

        return $array_assign;
    }

    public function save_relationship_changes($is_update, $exclude = array())
    {
        if (empty($this->in_workflow)) {
            if (empty($this->in_import)) {
                //if the global soap_server_object variable is not empty (as in from a soap/OPI call), then process the assigned_user_id relationship, otherwise
                //add assigned_user_id to exclude list and let the logic from MeetingFormBase determine whether assigned user id gets added to the relationship
                if (!empty($GLOBALS['soap_server_object'])) {
                    $exclude = array('lead_id', 'contact_id', 'user_id');
                } else {
                    $exclude = array('lead_id', 'contact_id', 'user_id', 'assigned_user_id');
                }
            } else {
                $exclude = array('user_id');
            }
        }
        parent::save_relationship_changes($is_update, $exclude);
    }

    public function getDefaultStatus()
    {
        $def = $this->field_defs['status'];
        if (isset($def['default'])) {
            return $def['default'];
        }
        $app = return_app_list_strings_language($GLOBALS['current_language']);
        if (isset($def['options']) && isset($app[$def['options']])) {
            $keys = array_keys($app[$def['options']]);
            return $keys[0];
        }

        return '';
    }

    public function mark_deleted($id)
    {
        require_once("modules/Calendar/CalendarUtils.php");
        CalendarUtils::correctRecurrences($this, $id);

        parent::mark_deleted($id);
    }

    /**
     * @param array $params
     * @return string The hangup cause of the call.
     * recv_cancel: Hệ thống nhận được một yêu cầu CANCEL từ bên kia (người gọi hoặc được gọi) trước khi cuộc gọi được kết nối
     * send_refuse: Hệ thống từ chối cuộc gọi.
     * send_cancel: Hệ thống gửi yêu cầu CANCEL đến bên kia trước khi cuộc gọi được kết nối
     * send_bye: Hệ thống gửi tín hiệu BYE để kết thúc cuộc gọi.
     */
    public function determineHangupCause2($params)
    {
        if (!is_array($params) || empty($params)) {
            return 'Không xác định';
        }

        $direction      = strtolower($params['call_direction'] ?? '');
        $disposition    = strtolower($params['call_hangup_disposition'] ?? '');
        $hangupCause    = strtoupper($params['hangup_cause'] ?? '');
        $ccCancelReason = $params['cc_cancel_reason'] ?? null; //cc_cancel_reason chỉ tồn tại khi cuộc gọi liên quan đến hàng đợi (Call Center Queue)
        $billsec        = (int)($params['call_bill'] ?? 0);

        $messages = [
            'ORIGINATOR_CANCEL' => [
                /**
                 * Bên gọi (originator) đã hủy cuộc gọi trước khi nó được kết nối hoặc trả lời.
                 * Nguyên nhân có thể do khách hàng, hệ thống, hoặc tổng đài viên hủy cuộc gọi.
                 */
                'inbound' => ($ccCancelReason === null && $billsec == 0) ? 'Khách hàng chủ động hủy cuộc gọi trước khi kết nối.' : 'Hệ thống tự động hủy cuộc gọi inbound (timeout, không agent, hoặc lỗi SIP).',
                'outbound' => ($disposition === 'recv_cancel' && $ccCancelReason === null) ? 'Tổng đài viên hủy cuộc gọi.' : ($ccCancelReason !== null ? 'Hệ thống tự động hủy cuộc gọi outbound (timeout hoặc lỗi hàng đợi).' : 'Tổng đài viên hủy trước khi kết nối.'),
                'internal' => ($disposition === 'recv_cancel') ? 'Người gọi nội bộ chủ động hủy cuộc gọi trước khi người nhận nghe máy.' : '',
            ],
            'NORMAL_CLEARING' => [
                'internal' => ($disposition === 'recv_bye') ? 'Người nhận chủ động hủy cuộc gọi.' : 'Cuộc gọi nội bộ bị hủy trước khi kết nối.',
            ]
        ];

        return $messages[$hangupCause][$direction] ?? 'Nguyên nhân ngắt máy không xác định';
    }


    public function determineHangupCause($params)
    {
        if (is_array($params) && count($params) > 0) {
            $direction          = $params['call_direction'] ?? '';
            $disposition        = $params['call_hangup_disposition'] ?? '';
            $hangupCause        = $params['hangup_cause'] ?? '';
            $ccCancelReason     = $params['cc_cancel_reason'] ?? null;

            if ($hangupCause === 'DESTINATION_OUT_OF_ORDER') {
                if ($direction === 'inbound') {
                    if ($ccCancelReason === 'NO_AGENT_TIMEOUT') {
                        return 'Không có tổng đài viên nào phản hồi, cuộc gọi bị hủy do hết thời gian chờ.';
                    } elseif ($ccCancelReason === 'TIMEOUT') {
                        return 'Cuộc gọi bị gián đoạn do lỗi mạng hoặc hệ thống tổng đài không phản hồi.';
                    } else {
                        return 'Không thể kết nối với tổng đài viên do lỗi đường truyền hoặc thiết bị.';
                    }
                } elseif ($direction === 'outbound') {
                    return 'Không thể kết nối với khách hàng do thiết bị không hoạt động hoặc mất kết nối.';
                } elseif ($direction === 'internal') {
                    return 'Không thể kết nối giữa các tổng đài viên do lỗi hệ thống hoặc mất kết nối mạng.';
                }
            }

            if ($hangupCause === 'INCOMPATIBLE_DESTINATION') {
                if ($direction === 'inbound') {
                    if ($disposition === 'recv_refuse') {
                        return 'Cuộc gọi bị tổng đài từ chối do không hỗ trợ codec hoặc cấu hình không tương thích.';
                    } elseif ($disposition === 'recv_bye') {
                        return 'Cuộc gọi được tiếp nhận nhưng bị ngắt kết nối do vấn đề tương thích.';
                    } elseif ($disposition === 'send_refuse') {
                        return 'Tổng đài không hỗ trợ cuộc gọi từ khách hàng.';
                    } else {
                        return 'Cuộc gọi không thể tiếp tục do vấn đề tương thích thiết bị hoặc mạng.';
                    }
                } elseif ($direction === 'outbound') {
                    return 'Cuộc gọi ra ngoài bị từ chối do thiết bị đích không hỗ trợ cuộc gọi.';
                } elseif ($direction === 'local') {
                    return 'Không thể kết nối giữa các tổng đài viên do thiết bị hoặc cấu hình không phù hợp.';
                }
            }

            // Xử lý cho cuộc gọi inbound
            if ($direction === 'inbound') {
                if ($hangupCause === 'UNALLOCATED_NUMBER') {
                    return 'Khách hàng gọi vào số tổng đài chưa được cấp phát hoặc không khả dụng.';
                }

                if ($hangupCause === 'USER_BUSY') {
                    return ($disposition === 'recv_refuse') ? 'Tổng đài viên từ chối cuộc gọi hoặc đang bận, không thể tiếp nhận cuộc gọi' : 'Hệ thống tổng đài từ chối cuộc gọi vì không có tổng đài viên nào tiếp nhận';
                }

                if ($hangupCause === 'NO_ANSWER') {
                    return ($disposition === 'send_bye')
                        ? 'Tổng đài viên không bắt máy, khách hàng kết thúc cuộc gọi'
                        : 'Tổng đài viên không bắt máy, cuộc gọi tự động kết thúc';
                }

                if ($disposition === 'recv_bye' && $hangupCause === 'NORMAL_CLEARING') {
                    return ($ccCancelReason === 'BREAK_OUT')
                        ? 'Khách hàng kết thúc cuộc gọi khi không có agent trả lời'
                        : 'khách hàng chủ động kết thúc cuộc gọi';
                }

                if ($disposition === 'send_bye') {
                    return ($ccCancelReason === 'TIMEOUT')
                        ? 'Cuộc gọi tự động kết thúc vì không có agent trả lời'
                        : 'Tổng đài viên chủ động kết thúc cuộc gọi';
                }

                if ($disposition === 'send_refuse') {
                    return 'Tổng đài viên đang bận. Người nhận từ chối cuộc gọi';
                }
            }

            // Xử lý cho cuộc gọi outbound
            if ($direction === 'outbound') {
                if ($hangupCause === 'UNALLOCATED_NUMBER') {
                    return 'Số điện thoại không hợp lệ';
                }

                if ($disposition === 'recv_cancel' && $hangupCause === 'ORIGINATOR_CANCEL') {
                    return 'Tổng đài viên chủ động hủy cuộc gọi';
                }

                if ($disposition === 'send_refuse') {
                    return ($hangupCause === 'USER_BUSY') ? 'Khách hàng đang bận hoặc từ chối cuộc gọi' : 'Khách hàng không liên lạc được, cuộc gọi tự động kết thúc';
                }

                if ($hangupCause === 'NORMAL_CLEARING') {
                    if ($disposition === 'recv_bye') {
                        return 'Tổng đài viên chủ động kết thúc cuộc gọi';
                    }

                    if ($disposition === 'send_bye') {
                        return 'Khách hàng chủ động kết thúc cuộc gọi';
                    }

                    if ($disposition === 'send_refuse') {
                        return 'Cuộc gọi bị từ chối hoặc không thể tiếp tục';
                    }
                }
            }

            if ($direction === 'internal') {
                if ($hangupCause === 'UNALLOCATED_NUMBER') {
                    return 'Số điện thoại không hợp lệ';
                }

                if ($disposition === 'recv_cancel' && $hangupCause === 'ORIGINATOR_CANCEL') {
                    return 'Người gọi chủ động hủy cuộc gọi';
                }

                if ($hangupCause === 'NORMAL_CLEARING') {
                    if ($disposition === 'send_bye') {
                        return 'Người nhận chủ động kết thúc cuộc gọi';
                    }

                    if ($disposition === 'send_refuse') {
                        return 'Tổng đài viên đang bận. Người nhận từ chối cuộc gọi';
                    }

                    if ($disposition === 'recv_bye') {
                        return 'Người gọi chủ động kết thúc cuộc gọi';
                    }
                }
            }

            return 'Nguyên nhân ngắt máy không xác định';
        }

        return 'Không xác định';
    }

    public function summaryLogForCalls($log_calls)
    {
        global $timedate;
        $date_format = $timedate->get_date_format();

        $call_start = $log_calls['call_start'];
        $call_accepted = getCallAcceptDatetime($log_calls);
        $call_end = $log_calls['call_end'];
        $call_answer = calculateWaitTime($log_calls) ?? 0;
        $call_talk = $log_calls['call_talk'] ?? 0;
        $call_duration = $log_calls['call_duration'] ?? 0;

        $description = "Cuộc gọi bắt đầu lúc <code>" . date('H:i:s ' . $date_format . '', strtotime($call_start)) . "</code>. ";
        if ($call_accepted) {
            $description .= "Bắt máy lúc <code>" . date('H:i:s ' . $date_format . '', strtotime($call_accepted)) . "</code> sau <code>$call_answer</code> giây chờ đợi. ";
            $description .= "Hội thoại <code>$call_talk</code> giây và kết thúc lúc <code>" . date('H:i:s ' . $date_format . '', strtotime($call_end)) . "</code>. ";
        } else {
            $description .= "Cuộc gọi không được bắt máy và kết thúc lúc <code>" . date('H:i:s ' . $date_format . '', strtotime($call_end)) . "</code>. ";
        }

        $description .= "Tổng thời gian cuộc gọi được ghi nhận là <code>$call_duration</code> giây.";

        return $description;
    }

    /**
     * Get list outbound phone pbx
     * @param string $direction (inbound/outbound/all)
     * @return array  $condition (only_inbound)
     */
    public function get_list_phone_pbx($only_inbound = '', $round_robin = '')
    {
        global $db;
        $result = [];

        $conditon = '';
        if ($only_inbound === 1) {
            $conditon .= ' AND only_inbound = 1';
        } else if ($only_inbound === 0) {
            $conditon .= ' AND only_inbound = 0';
        }

        if ($round_robin === 1) {
            $conditon .= ' AND round_robin = 1';
        }

        $sql = "
            SELECT id, name, description, format_phone, network_provider, proxy, brand_name, website, label, only_inbound
            FROM ec_outbound_phone
            WHERE status = 'active'
            AND deleted = 0
            $conditon
        ";

        $res = $db->query($sql);
        $row_count = $db->getRowCount($res);
        if ($row_count > 0) {
            while ($row = $db->fetchByAssoc($res)) {
                // Nhóm dữ liệu theo `network_provider`
                $network_provider = $row['network_provider'];
                if (!isset($result[$network_provider])) {
                    $result[$network_provider] = [];
                }
                $result[$network_provider][] = $row;
            }
        }

        return $result;
    }

    /**
     * Get statistics of CDR 
     * 
     * @param array $params
     * @return array
     */
    public function getCDRStatistics()
    {
        global $db;

        $graph = [];

        $sql =  'SELECT
                        s_id AS hours,
                        DATE_FORMAT(start_date, "%d %b") AS date,
                        CONCAT(DATE_FORMAT(start_date, "%h:%i %p"), " - ", DATE_FORMAT(end_date, "%h:%i %p")) AS time,
                        UNIX_TIMESTAMP(start_date) AS start_epoch,
                        UNIX_TIMESTAMP(end_date) AS end_epoch,
                        s_hour,
                        start_date,
                        end_date,
                        COALESCE(total, 0) AS total,
                        COALESCE(answered, 0) AS answered,
                        COALESCE(seconds, 0) AS seconds,
                        (ROUND(seconds / 60, 1)) AS minutes,
                        COALESCE(total, 0) / (s_hour * 60) AS call_per_min,
                        COALESCE(answered, 0) / (s_hour * 60) AS cpm_answered,
                        COALESCE(total, 0) / s_hour AS calls_per_hour,
                        COALESCE(failed, 0) AS failed,
                        COALESCE(ROUND(100 * (answered / NULLIF(total, 0)), 2), 0) AS asr,
                        COALESCE(ROUND(seconds / NULLIF(answered, 0) / 60, 2), 0) AS aloc
                    FROM
                    (
                        SELECT
                        s.s_id,
                        s.start_date,
                        s.end_date,
                        s.s_hour,
                        COUNT(c.call_id) AS total,
                        SUM(CASE WHEN c.call_talk = 0 THEN 1 ELSE 0 END) AS failed,
                        SUM(CASE WHEN c.call_talk > 0 THEN 1 ELSE 0 END) AS answered,
                        SUM(CASE WHEN c.call_talk > 0 THEN c.call_talk ELSE 0 END) AS seconds
                        FROM
                        (
                            SELECT
                                h.s_id,
                                h.s_start,
                                h.s_end,
                                h.s_hour,
                                DATE_SUB(DATE_FORMAT(NOW(), "%Y-%m-%d %H:00:00"), INTERVAL h.s_start HOUR) AS start_date,
                                DATE_SUB(DATE_FORMAT(NOW(), "%Y-%m-%d %H:00:00"), INTERVAL h.s_end HOUR) AS end_date 
                            FROM (
                                SELECT 1 AS s_id, 1 AS s_start, 0 AS s_end, 1 AS s_hour UNION ALL
                                SELECT 2, 2, 1, 1 UNION ALL
                                SELECT 3, 3, 2, 1 UNION ALL
                                SELECT 4, 4, 3, 1 UNION ALL
                                SELECT 5, 5, 4, 1 UNION ALL
                                SELECT 6, 6, 5, 1 UNION ALL
                                SELECT 7, 7, 6, 1 UNION ALL
                                SELECT 8, 8, 7, 1 UNION ALL
                                SELECT 9, 9, 8, 1 UNION ALL
                                SELECT 10, 10, 9, 1 UNION ALL
                                SELECT 11, 11, 10, 1 UNION ALL
                                SELECT 12, 12, 11, 1 UNION ALL
                                SELECT 13, 13, 12, 1 UNION ALL
                                SELECT 14, 14, 13, 1 UNION ALL
                                SELECT 15, 15, 14, 1 UNION ALL
                                SELECT 16, 16, 15, 1 UNION ALL
                                SELECT 17, 17, 16, 1 UNION ALL
                                SELECT 18, 18, 17, 1 UNION ALL
                                SELECT 19, 19, 18, 1 UNION ALL
                                SELECT 20, 20, 19, 1 UNION ALL
                                SELECT 21, 21, 20, 1 UNION ALL
                                SELECT 22, 22, 21, 1 UNION ALL
                                SELECT 23, 23, 22, 1 UNION ALL
                                SELECT 24, 24, 23, 1 UNION ALL
                                SELECT 25, 24, 0, 24 UNION ALL
                                SELECT 26, 168, 0, 168 UNION ALL
                                SELECT 27, 720, 0, 720
                            ) AS h 
                            GROUP BY s_id, s_hour, s_start, s_end 
                            ORDER BY s_id ASC
                        ) AS s
                        LEFT JOIN calls AS c ON STR_TO_DATE(c.date_start, "%d-%m-%Y %H:%i:%s") BETWEEN s.start_date AND s.end_date AND c.deleted = 0 
                        GROUP BY s.s_id, s.start_date, s.end_date, s.s_hour 
                        ORDER BY s.s_id ASC
                    ) AS d';

        $res = $db->query($sql);
        $x = 0;
        $hours = 24;

        while ($row = $db->fetchByAssoc($res)) {
            $graph['data'][] = $row;

            if ($x < $hours) {
                $graph['total'][$x][] = $row['start_epoch'] * 1000;
                $graph['total'][$x][] = $row['total'] / 1;

                $graph['failed'][$x][] = $row['start_epoch'] * 1000;
                $graph['failed'][$x][] = $row['failed'] / 1;

                $graph['answered'][$x][] = $row['start_epoch'] * 1000;
                $graph['answered'][$x][] = $row['answered'] / 1;

                $graph['minutes'][$x][] = $row['start_epoch'] * 1000;
                $graph['minutes'][$x][] = round($row['minutes'] ?? 0, 2);

                $graph['call_per_min'][$x][] = $row['start_epoch'] * 1000;
                $graph['call_per_min'][$x][] = round($row['call_per_min'], 2);


                $graph['asr'][$x][] = $row['start_epoch'] * 1000;
                $graph['asr'][$x][] = round($row['asr'] ?? 0, 2) / 100;

                $graph['aloc'][$x][] = $row['start_epoch'] * 1000;
                $graph['aloc'][$x][] = round($row['aloc'] ?? 0, 2);
            }

            $x++;
        }

        return $graph;
    }
}
