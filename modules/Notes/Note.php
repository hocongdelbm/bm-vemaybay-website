<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once __DIR__ . '/../../include/upload_file.php';
require_once __DIR__ . '/../../include/SugarObjects/templates/file/File.php';

// Note is used to store customer information.
class Note extends File
{
    public $field_name_map;
    // Stored fields
    public $id;
    public $date_entered;
    public $date_modified;
    public $modified_user_id;
    public $assigned_user_id;
    public $created_by;
    public $created_by_name;
    public $modified_by_name;
    public $description;
    public $name;
    public $filename;
    // handle to an upload_file object
    // used in emails
    public $file;
    public $embed_flag; // inline image flag
    public $parent_type;
    public $parent_id;
    public $contact_id;
    public $portal_flag;

    public $parent_name;
    public $contact_name;
    public $contact_phone;
    public $contact_email;
    public $file_mime_type;
    public $module_dir = "Notes";
    public $default_note_name_dom = array('Meeting notes', 'Reminder');
    public $table_name = "notes";
    public $new_schema = true;
    public $object_name = "Note";
    public $importable = true;

    public $working_process_id;

    // This is used to retrieve related fields from form posts.
    public $additional_column_fields = array(
        'contact_name',
        'contact_phone',
        'contact_email',
        'parent_name',
        'first_name',
        'last_name'
    );

    public $booking_status;

    public function __construct()
    {
        parent::__construct();
    }

    public function safeAttachmentName()
    {
        global $sugar_config;

        //get position of last "." in file name
        $file_ext_beg = strrpos($this->filename, ".");
        $file_ext = "";

        //get file extension
        if ($file_ext_beg !== false) {
            $file_ext = substr($this->filename, $file_ext_beg + 1);
        }

        //check to see if this is a file with extension located in "badext"
        foreach ($sugar_config['upload_badext'] as $badExt) {
            if (strtolower($file_ext) == strtolower($badExt)) {
                //if found, then append with .txt and break out of lookup
                $this->name = $this->name . ".txt";
                $this->file_mime_type = 'text/';
                $this->filename = $this->filename . ".txt";
                break; // no need to look for more
            }
        }
    }

    /**
     * overrides SugarBean's method.
     * If a system setting is set, it will mark all related notes as deleted, and attempt to delete files that are
     * related to those notes
     * @param string id ID
     */
    public function mark_deleted($id)
    {
        global $sugar_config;

        if ($this->parent_type == 'Emails') {
            if (isset($sugar_config['email_default_delete_attachments']) && $sugar_config['email_default_delete_attachments'] == true) {
                $removeFile = "upload://$id";
                if (file_exists($removeFile)) {
                    if (!unlink($removeFile)) {
                        $GLOBALS['log']->error("*** Could not unlink() file: [ {$removeFile} ]");
                    }
                }
            }
        }

        // delete note
        parent::mark_deleted($id);
    }

    public function deleteAttachment($isduplicate = "false")
    {
        $removeFile = null;
        
        if ($this->ACLAccess('edit')) {
            if ($isduplicate == "true") {
                return true;
            }
            $removeFile = "upload://{$this->id}";
        }

        if (file_exists($removeFile)) {
            if (!unlink($removeFile)) {
                $GLOBALS['log']->error("*** Could not unlink() file: [ {$removeFile} ]");
            } else {
                $this->uploadfile = '';
                $this->filename = '';
                $this->file_mime_type = '';
                $this->file = '';
                $this->save();

                return true;
            }
        } else {
            $this->uploadfile = '';
            $this->filename = '';
            $this->file_mime_type = '';
            $this->file = '';
            $this->save();

            return true;
        }

        return false;
    }


    public function get_summary_text()
    {
        return (string)$this->name;
    }

    /**
     * Returns the content as string or false if there is no attachment or it
     * couldn't be located.
     *
     * @return bool|string
     */
    public function getAttachmentContent()
    {
        $path = "upload://{$this->id}";
        if (!file_exists($path)) {
            return false;
        }

        return file_get_contents($path);
    }

    public function create_export_query($order_by, $where, $relate_link_join = '')
    {
        $custom_join = $this->getCustomJoin(true, true, $where);
        $custom_join['join'] .= $relate_link_join;
        $query = "SELECT notes.*, contacts.first_name, contacts.last_name, users.user_name as assigned_user_name ";

        $query .= $custom_join['select'];

        $query .= " FROM notes ";

        $query .= "	LEFT JOIN contacts ON notes.contact_id=contacts.id ";
        $query .= "  LEFT JOIN users ON notes.assigned_user_id=users.id ";

        $query .= $custom_join['join'];

        $where_auto = " notes.deleted=0 AND (contacts.deleted IS NULL OR contacts.deleted=0)";

        if ($where != "") {
            $query .= "where $where AND " . $where_auto;
        } else {
            $query .= "where " . $where_auto;
        }

        $order_by = $this->process_order_by($order_by);
        if (empty($order_by)) {
            $order_by = 'notes.name';
        }
        $query .= ' ORDER BY ' . $order_by;

        return $query;
    }

    public function fill_in_additional_list_fields()
    {
        $this->fill_in_additional_detail_fields();
    }

    public function fill_in_additional_detail_fields()
    {
        parent::fill_in_additional_detail_fields();
        //TODO:  Seems odd we need to clear out these values so that list views don't show the previous rows value if current value is blank
        $this->getRelatedFields(
            'Contacts',
            $this->contact_id,
            array('name' => 'contact_name', 'phone_work' => 'contact_phone')
        );
        if (!empty($this->contact_name)) {
            $emailAddress = new SugarEmailAddress();
            $this->contact_email = $emailAddress->getPrimaryAddress(false, $this->contact_id, 'Contacts');
        }

        if (isset($this->contact_id) && $this->contact_id != '') {
            $contact = BeanFactory::newBean('Contacts');
            $contact->retrieve($this->contact_id);
            if (isset($contact->id)) {
                $this->contact_name = $contact->full_name;
            }
        }
    }


    public function get_list_view_data()
    {
        $note_fields = $this->get_list_view_array();
        global $app_list_strings, $focus, $action, $currentModule, $mod_strings, $sugar_config;

        if (isset($this->parent_type)) {
            $note_fields['PARENT_MODULE'] = $this->parent_type;
        }

        if (!empty($this->filename)) {
            if (file_exists("upload://{$this->id}")) {
                $note_fields['FILENAME'] = $this->filename;
                $note_fields['FILE_URL'] = UploadFile::get_upload_url($this);
            }
        }
        if (isset($this->contact_id) && $this->contact_id != '') {
            $contact = BeanFactory::newBean('Contacts');
            $contact->retrieve($this->contact_id);
            if (isset($contact->id)) {
                $this->contact_name = $contact->full_name;
            }
        }
        if (isset($this->contact_name)) {
            $note_fields['CONTACT_NAME'] = $this->contact_name;
        }

        global $current_language;
        $mod_strings = return_module_language($current_language, 'Notes');
        $note_fields['STATUS'] = $mod_strings['LBL_NOTE_STATUS'];


        return $note_fields;
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
            else {
                if (!empty($this->parent_type) && !empty($this->parent_id)) {
                    global $current_user;
                    $parent_bean = BeanFactory::getBean($this->parent_type, $this->parent_id);
                    if ($parent_bean !== false) {
                        $is_owner = $current_user->id == $parent_bean->assigned_user_id;
                    }
                }
            }
            require_once("modules/SecurityGroups/SecurityGroup.php");
            $in_group = SecurityGroup::groupHasAccess($this->parent_type, $this->parent_id, 'view');
            /* END - SECURITY GROUPS */
        }

        /* BEGIN - SECURITY GROUPS */
        /**
         * if(!ACLController::moduleSupportsACL($this->parent_type) || ACLController::checkAccess($this->parent_type, 'view', $is_owner)) {
         */
        if (!ACLController::moduleSupportsACL($this->parent_type) || ACLController::checkAccess(
            $this->parent_type,
            'view',
            $is_owner,
            'module',
            $in_group
        )
        ) {
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
        /**
         * if( ACLController::checkAccess('Contacts', 'view', $is_owner)) {
         */
        if (ACLController::checkAccess('Contacts', 'view', $is_owner, 'module', $in_group)) {
            /* END - SECURITY GROUPS */
            $array_assign['CONTACT'] = 'a';
        } else {
            $array_assign['CONTACT'] = 'span';
        }

        return $array_assign;
    }

    public function bean_implements($interface)
    {
        switch ($interface) {
            case 'ACL':
                return true;
            case 'FILE':
                return true;
        }

        return false;
    }
    
    /**
     * Check text has money
     * 
     * @param string $text
     * @return bool
     * @author DucPham
     */
    public function hasMoney(string $text): bool {
        $moneyRegex = '/(\d{1,3}([.,]\d{3})+|\d{6,})(\s?(₫|VND|USD|\$))?/u';
        return preg_match($moneyRegex, $text);
    }
}
