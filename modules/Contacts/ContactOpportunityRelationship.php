<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

// Contact is used to store customer information.
class ContactOpportunityRelationship extends SugarBean
{
    // Stored fields
    public $id;
    public $contact_id;
    public $contact_role;
    public $opportunity_id;

    // Related fields
    public $contact_name;
    public $opportunity_name;

    public $table_name = "opportunities_contacts";
    public $object_name = "ContactOpportunityRelationship";
    public $column_fields = array(
        "id",
        "contact_id",
        "opportunity_id",
        "contact_role",
        'date_modified'
    );

    public $new_schema = true;

    public $additional_column_fields = array();
    public $field_defs = array(
        'id' => array('name' =>'id', 'type' =>'char', 'len'=>'36', 'default'=>''),
        'contact_id' => array('name' =>'contact_id', 'type' =>'char', 'len'=>'36', ),
        'opportunity_id' => array('name' =>'opportunity_id', 'type' =>'char', 'len'=>'36',),
        'contact_role' => array('name' =>'contact_role', 'type' =>'char', 'len'=>'50'),
        'date_modified' => array('name' => 'date_modified','type' => 'datetime'),
        'deleted' => array('name' =>'deleted', 'type' =>'bool', 'len'=>'1', 'default'=>'0', 'required'=>true)
    );

    public function __construct()
    {
        parent::__construct();
        $this->db = DBManagerFactory::getInstance();
        $this->dbManager = DBManagerFactory::getInstance();
        $this->disable_row_level_security = true;
    }




    public function fill_in_additional_detail_fields()
    {
        global $locale;
        if (isset($this->contact_id) && $this->contact_id != "") {
            $query = "SELECT first_name, last_name from contacts where id='$this->contact_id' AND deleted=0";
            $result =$this->db->query($query, true, " Error filling in additional detail fields: ");
            // Get the id and the name.
            $row = $this->db->fetchByAssoc($result);

            if ($row != null) {
                $this->contact_name = $locale->getLocaleFormattedName($row['first_name'], $row['last_name']);
            }
        }

        if (isset($this->opportunity_id) && $this->opportunity_id != "") {
            $query = "SELECT name from opportunities where id='$this->opportunity_id' AND deleted=0";
            $result =$this->db->query($query, true, " Error filling in additional detail fields: ");
            // Get the id and the name.
            $row = $this->db->fetchByAssoc($result);

            if ($row != null) {
                $this->opportunity_name = $row['name'];
            }
        }
    }
}
