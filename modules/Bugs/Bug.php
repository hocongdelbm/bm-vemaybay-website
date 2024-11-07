<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

// Bug is used to store customer information.
class Bug extends SugarBean
{
    public $field_name_map = array();
    // Stored fields
    public $id;
    public $date_entered;
    public $date_modified;
    public $modified_user_id;
    public $assigned_user_id;
    public $bug_number;
    public $description;
    public $name;
    public $status;
    public $priority;

    // These are related
    public $resolution;
    public $found_in_release;
    public $release_name;
    public $fixed_in_release_name;
    public $created_by;
    public $created_by_name;
    public $modified_by_name;
    public $account_id;
    public $contact_id;
    public $case_id;
    public $task_id;
    public $note_id;
    public $meeting_id;
    public $call_id;
    public $email_id;
    public $assigned_user_name;
    public $type;

    //BEGIN Additional fields being added to Bugs

    public $fixed_in_release;
    public $work_log;
    public $source;
    public $product_category;
    //END Additional fields being added to Bugs

    public $module_dir = 'Bugs';
    public $table_name = "bugs";
    public $rel_account_table = "accounts_bugs";
    public $rel_contact_table = "contacts_bugs";
    public $rel_case_table = "cases_bugs";
    public $importable = true;
    public $object_name = "Bug";

    // This is used to retrieve related fields from form posts.
    public $additional_column_fields = array('assigned_user_name', 'assigned_user_id', 'case_id', 'account_id', 'contact_id', 'task_id', 'note_id', 'meeting_id', 'call_id', 'email_id');

    public $relationship_fields = array(
        'case_id' => 'cases',
        'account_id' => 'accounts',
        'contact_id' => 'contacts',
        'task_id' => 'tasks',
        'note_id' => 'notes',
        'meeting_id' => 'meetings',
        'call_id' => 'calls',
        'email_id' => 'emails'
    );

    public function __construct()
    {
        parent::__construct();


        $this->setupCustomFields('Bugs');

        foreach ($this->field_defs as $field) {
            $this->field_name_map[$field['name']] = $field;
        }
    }



    public $new_schema = true;





    public function get_summary_text()
    {
        return (string)$this->name;
    }

    public function create_list_query($order_by, $where, $show_deleted = 0)
    {
        // Fill in the assigned_user_name
        //		$this->assigned_user_name = get_assigned_user_name($this->assigned_user_id);

        $custom_join = $this->getCustomJoin();

        $query = "SELECT ";

        $query .= "
                               bugs.*

                                ,users.user_name as assigned_user_name, releases.id release_id, releases.name release_name";
        $query .= $custom_join['select'];
        $query .= " FROM bugs ";


        $query .= "				LEFT JOIN releases ON bugs.found_in_release=releases.id
								LEFT JOIN users
                                ON bugs.assigned_user_id=users.id";
        $query .= "  ";
        $query .= $custom_join['join'];
        $where_auto = '1=1';
        if ($show_deleted == 0) {
            $where_auto = " $this->table_name.deleted=0 ";
        } else {
            if ($show_deleted == 1) {
                $where_auto = " $this->table_name.deleted=1 ";
            }
        }


        if ($where != "") {
            $query .= "where $where AND " . $where_auto;
        } else {
            $query .= "where " . $where_auto;
        }
        if (substr_count($order_by, '.') > 0) {
            $query .= " ORDER BY $order_by";
        } else {
            if ($order_by != "") {
                $query .= " ORDER BY $order_by";
            } else {
                $query .= " ORDER BY bugs.name";
            }
        }
        return $query;
    }

    public function create_export_query($order_by, $where, $relate_link_join = '')
    {
        $custom_join = $this->getCustomJoin(true, true, $where);
        $custom_join['join'] .= $relate_link_join;
        $query = "SELECT
                                bugs.*,
                                r1.name found_in_release_name,
                                r2.name fixed_in_release_name,
                                users.user_name assigned_user_name";
        $query .=  $custom_join['select'];
        $query .= " FROM bugs ";
        $query .= "				LEFT JOIN releases r1 ON bugs.found_in_release = r1.id
								LEFT JOIN releases r2 ON bugs.fixed_in_release = r2.id
								LEFT JOIN users
                                ON bugs.assigned_user_id=users.id";
        $query .=  $custom_join['join'];
        $query .= "";
        $where_auto = "  bugs.deleted=0
                ";

        if ($where != "") {
            $query .= " where $where AND " . $where_auto;
        } else {
            $query .= " where " . $where_auto;
        }

        if ($order_by != "") {
            $query .= " ORDER BY $order_by";
        } else {
            $query .= " ORDER BY bugs.bug_number";
        }

        return $query;
    }
    public function fill_in_additional_list_fields()
    {
        parent::fill_in_additional_list_fields();
        // Fill in the assigned_user_name
        //$this->assigned_user_name = get_assigned_user_name($this->assigned_user_id);

        //	   $this->set_fixed_in_release();
    }

    public function fill_in_additional_detail_fields()
    {

        /*
        // Fill in the assigned_user_name
        $this->assigned_user_name = get_assigned_user_name($this->assigned_user_id);
        */
        parent::fill_in_additional_detail_fields();
        //$this->created_by_name = get_assigned_user_name($this->created_by);
        //$this->modified_by_name = get_assigned_user_name($this->modified_user_id);
        $this->set_release();
        $this->set_fixed_in_release();
    }


    public function set_release()
    {
        static $releases;

        if (empty($this->found_in_release)) {
            return;
        }
        if (isset($releases[$this->found_in_release])) {
            $this->release_name = $releases[$this->found_in_release];
            return;
        }

        $query = "SELECT r1.name from releases r1, $this->table_name i1 where r1.id = i1.found_in_release and i1.id = '$this->id' and i1.deleted=0 and r1.deleted=0";
        $result = $this->db->query($query, true, " Error filling in additional detail fields: ");

        // Get the id and the name.
        $row = $this->db->fetchByAssoc($result);

        if ($row != null) {
            $this->release_name = $row['name'];
        } else {
            $this->release_name = '';
        }

        $releases[$this->found_in_release] = $this->release_name;
    }


    public function set_fixed_in_release()
    {
        static $releases;

        if (empty($this->fixed_in_release)) {
            return;
        }
        if (isset($releases[$this->fixed_in_release])) {
            $this->fixed_in_release_name = $releases[$this->fixed_in_release];
            return;
        }

        $query = "SELECT r1.name from releases r1, $this->table_name i1 where r1.id = i1.fixed_in_release and i1.id = '$this->id' and i1.deleted=0 and r1.deleted=0";
        $result = $this->db->query($query, true, " Error filling in additional detail fields: ");

        // Get the id and the name.
        $row = $this->db->fetchByAssoc($result);



        if ($row != null) {
            $this->fixed_in_release_name = $row['name'];
        } else {
            $this->fixed_in_release_name = '';
        }

        $releases[$this->fixed_in_release] = $this->fixed_in_release_name;
    }


    public function get_list_view_data()
    {
        global $current_language;
        $the_array = parent::get_list_view_data();
        $app_list_strings = return_app_list_strings_language($current_language);
        $mod_strings = return_module_language($current_language, 'Bugs');

        $this->set_release();

        // The new listview code only fetches columns that we're displaying and not all
        // the columns so we need these checks.
        $the_array['NAME'] = (($this->name == "") ? "<em>blank</em>" : $this->name);
        $the_array['PRIORITY'] = empty($this->priority) ? "" : (!isset($app_list_strings[$this->field_name_map['priority']['options']][$this->priority]) ? $this->priority : $app_list_strings[$this->field_name_map['priority']['options']][$this->priority]);
        $the_array['STATUS'] = empty($this->status) ? "" : (!isset($app_list_strings[$this->field_name_map['status']['options']][$this->status]) ? $this->status : $app_list_strings[$this->field_name_map['status']['options']][$this->status]);
        $the_array['TYPE'] = empty($this->type) ? "" : (!isset($app_list_strings[$this->field_name_map['type']['options']][$this->type]) ? $this->type : $app_list_strings[$this->field_name_map['type']['options']][$this->type]);

        $the_array['RELEASE'] = $this->release_name;
        $the_array['BUG_NUMBER'] = $this->bug_number;
        $the_array['ENCODED_NAME'] = $this->name;

        return  $the_array;
    }

    /**
    	builds a generic search based on the query string using or
    	do not include any $this-> because this is called on without having the class instantiated
     */
    public function build_generic_where_clause($the_query_string)
    {
        $where_clauses = array();
        $the_query_string = $this->db->quote($the_query_string);
        array_push($where_clauses, "bugs.name like '$the_query_string%'");
        if (is_numeric($the_query_string)) {
            array_push($where_clauses, "bugs.bug_number like '$the_query_string%'");
        }

        $the_where = "";
        foreach ($where_clauses as $clause) {
            if ($the_where != "") {
                $the_where .= " or ";
            }
            $the_where .= $clause;
        }

        return $the_where;
    }

    public function set_notification_body($xtpl, $bug)
    {
        global $mod_strings, $app_list_strings;

        $bug->set_release();

        $xtpl->assign("BUG_SUBJECT", $bug->name);
        $xtpl->assign("BUG_TYPE", $app_list_strings['bug_type_dom'][$bug->type]);
        $xtpl->assign("BUG_PRIORITY", $app_list_strings['bug_priority_dom'][$bug->priority]);
        $xtpl->assign("BUG_STATUS", $app_list_strings['bug_status_dom'][$bug->status]);
        $xtpl->assign("BUG_RESOLUTION", $app_list_strings['bug_resolution_dom'][$bug->resolution]);
        $xtpl->assign("BUG_RELEASE", $bug->release_name);
        $xtpl->assign("BUG_DESCRIPTION", nl2br($bug->description));
        $xtpl->assign("BUG_WORK_LOG", $bug->work_log);
        $xtpl->assign("BUG_BUG_NUMBER", $bug->bug_number);
        return $xtpl;
    }

    public function bean_implements($interface)
    {
        switch ($interface) {
            case 'ACL':
                return true;
        }
        return false;
    }

    public function save($check_notify = false)
    {
        global $sugar_config;

        // SENTELE NOTIFY
        $is_new_record = $this->new_with_id || empty($this->fetched_row); 

        $return_id = parent::save($check_notify);

        $is_work_log_updated = !$is_new_record 
        && $GLOBALS['current_user']->is_admin 
        && (isset($this->fetched_row['work_log']) ? $this->fetched_row['work_log'] !== $this->work_log : !empty($this->work_log));

        
        $user_created = $GLOBALS['current_user']->last_name . ' ' .  $GLOBALS['current_user']->first_name;
        $subject = $this->name;

        if ($is_new_record) {
            $content = html_entity_decode($user_created . ' đã tạo: ' . $subject, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            sendTelegramSupportKTTP(
                json_encode(array(
                    'text' => $content,
                    'reply_markup' => array(
                        'inline_keyboard' => array(
                            array(
                                array(
                                    'text' => 'Mở phiếu',
                                    'url' => $sugar_config['site_url'] . '/index.php?module=' . $this->object_name . 's&record=' . $this->id . '&action=DetailView&dothis=true',
                                ),
                            ),
                        ),
                    ),
                ), JSON_UNESCAPED_UNICODE)
            );
        } elseif ($is_work_log_updated) {
            $content = html_entity_decode($user_created . ' đã phản hồi: ' . $subject, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            sendTelegramSupportKTTP(
                json_encode(array(
                    'text' => $content,
                    'reply_markup' => array(
                        'inline_keyboard' => array(
                            array(
                                array(
                                    'text' => 'Mở phiếu',
                                    'url' => $sugar_config['site_url'] . '/index.php?module=' . $this->object_name . 's&record=' . $this->id . '&action=DetailView&dothis=true',
                                ),
                            ),
                        ),
                    ),
                ), JSON_UNESCAPED_UNICODE)
            );
        } 

        return $return_id;
    }
}

function getReleaseDropDown()
{
    static $releases = null;
    if (!$releases) {
        $seedRelease = BeanFactory::newBean('Releases');
        $releases = $seedRelease->get_releases(true, "Active");
    }
    return $releases;
}
