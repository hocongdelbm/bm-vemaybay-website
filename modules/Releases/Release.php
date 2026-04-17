<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/**

 * Description:
 */

class Release extends SugarBean
{
    // Stored fields
    public $id;
    public $deleted;
    public $date_entered;
    public $date_modified;
    public $modified_user_id;
    public $created_by;
    public $created_by_name;
    public $modified_by_name;
    public $name;
    public $status;

    public $table_name = "releases";

    public $object_name = "Release";
    public $module_dir = 'Releases';
    public $new_schema = true;

    // This is used to retrieve related fields from form posts.
    public $additional_column_fields = array();

    public function __construct()
    {
        parent::__construct();
    }




    public function get_summary_text()
    {
        return (string)$this->name;
    }

    public function get_releases($add_blank=false, $status='Active', $where='')
    {
        if ($where!='') {
            $query = "SELECT id, name FROM $this->table_name where ". $where ." and deleted=0 ";
        } else {
            $query = "SELECT id, name FROM $this->table_name where deleted=0 ";
        }
        if ($status=='Active') {
            $query .= " and status='Active' ";
        } elseif ($status=='Hidden') {
            $query .= " and status='Hidden' ";
        } elseif ($status=='All') {
        }
        $query .= " order by list_order asc";
        $result = $this->db->query($query, false);
        $GLOBALS['log']->debug("get_releases: result is ".var_export($result, true));

        $list = array();
        if ($add_blank) {
            $list['']='';
        }
        //if($this->db->getRowCount($result) > 0){
        // We have some data.
        while (($row = $this->db->fetchByAssoc($result)) != null) {
            //while ($row = $this->db->fetchByAssoc($result)) {
            $list[$row['id']] = $row['name'];
            $GLOBALS['log']->debug("row id is:".$row['id']);
            $GLOBALS['log']->debug("row name is:".$row['name']);
        }
        //}
        return $list;
    }

    public function fill_in_additional_list_fields()
    {
        $this->fill_in_additional_detail_fields();
    }

    public function fill_in_additional_detail_fields()
    {
    }

    public function get_list_view_data()
    {
        global $app_list_strings;
        $temp_array = $this->get_list_view_array();
        $temp_array["ENCODED_NAME"]=$this->name;

        if (!isset($app_list_strings['release_status_dom'][$this->status])) {
            LoggerManager::getLogger()->warn('Release get_list_view_data: Undefined index: "' . $this->status . '"');
            $appListStringReleaseStatusDomThisStatus = null;
        } else {
            $appListStringReleaseStatusDomThisStatus = $app_list_strings['release_status_dom'][$this->status];
        }

        $temp_array['ENCODED_STATUS'] = $appListStringReleaseStatusDomThisStatus;
        //	$temp_array["ENCODED_NAME"]=htmlspecialchars($this->name, ENT_QUOTES);
        return $temp_array;
    }
    /**
    	builds a generic search based on the query string using or
    	do not include any $this-> because this is called on without having the class instantiated
    */
    public function build_generic_where_clause($the_query_string)
    {
        $where_clauses = array();
        $the_query_string = DBManagerFactory::getInstance()->quote($the_query_string);
        array_push($where_clauses, "name like '$the_query_string%'");

        $the_where = "";
        foreach ($where_clauses as $clause) {
            if ($the_where != "") {
                $the_where .= " or ";
            }
            $the_where .= $clause;
        }


        return $the_where;
    }
}
