<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
//THIS IS TO FIX ANY VARDEFS IN CREATING LIST QUERIES (specifically relationships)
if (isset($this->field_defs['assigned_user_name'])) {
    $this->field_defs['assigned_user_name'] = array_merge(
        $this->field_defs['assigned_user_name'],
        array(
            'name' => 'assigned_user_name',
            'rname' => 'user_name',
            'vname' => 'LBL_ASSIGNED_TO',
            'type' => 'relate',
            'reportable' => false,
            'source' => 'non-db',
            'link' => 'assigned_user_link',
            'id_name' => 'assigned_user_id',
            'massupdate' => false
        )
    );
}

if (isset($this->field_defs['created_by'])) {
    if (!isset($this->field_defs['created_by_name'])) {
        $this->field_defs['created_by_name'] = array();
    }

    $this->field_defs['created_by_name'] = array_merge(
        $this->field_defs['created_by_name'],
        array(
            'name' => 'created_by_name',
            'rname' => 'user_name',
            'vname' => 'LBL_CREATED',
            'type' => 'relate',
            'reportable' => false,
            'source' => 'non-db',
            'link' => 'created_by_link'
        )
    );
}

if (isset($this->field_defs['modified_user_id'])) {
    if (!isset($this->field_defs['modified_by_name'])) {
        $this->field_defs['modified_by_name'] = array();
    }

    $this->field_defs['modified_by_name'] = array_merge(
        $this->field_defs['modified_by_name'],
        array(
            'name' => 'modified_by_name',
            'rname' => 'user_name',
            'vname' => 'LBL_MODIFIED',
            'type' => 'relate',
            'reportable' => false,
            'source' => 'non-db',
            'link' => 'modified_user_link'
        )
    );
}
