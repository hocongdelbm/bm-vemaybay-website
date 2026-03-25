<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
function build_related_list_by_user_id($bean, $user_id, $where)
{
    $bean_id_name = strtolower($bean->object_name) . '_id';

    if (isset($bean->rel_users_table) && !empty($bean->rel_users_table)) {
        $select = "SELECT {$bean->table_name}.* from {$bean->rel_users_table},{$bean->table_name} ";

        $auto_where = ' WHERE ';
        if (!empty($where)) {
            $auto_where .= $where . ' AND ';
        }

        $auto_where .= " {$bean->rel_users_table}.{$bean_id_name}={$bean->table_name}.id AND {$bean->rel_users_table}.user_id='{$user_id}' AND {$bean->table_name}.deleted=0 AND {$bean->rel_users_table}.deleted=0";


        $query = $select . $auto_where;

        $result = $bean->db->query($query, true);

        $list = array();

        while ($row = $bean->db->fetchByAssoc($result)) {
            $row = $bean->convertRow($row);
            $bean->fetched_row = $row;
            $bean->fromArray($row);
            $bean->processed_dates_times = array();
            $bean->check_date_relationships_load();
            $bean->fill_in_additional_detail_fields();

            /**
             * PHP  5+ always treats objects as passed by reference
             * Need to clone it if we're using 5.0+
             * clone() not supported by 4.x
             */
            if (version_compare(phpversion(), "5.0", ">=")) {
                $newBean = clone($bean);
            } else {
                $newBean = $bean;
            }
            $list[] = $newBean;
        }

        return $list;
    } else {
        $select = "SELECT {$bean->table_name}.* from {$bean->table_name} ";

        $auto_where = ' WHERE ';
        if (!empty($where)) {
            $auto_where .= $where . ' AND ';
        }

        $auto_where .= " {$bean->table_name}.assigned_user_id='{$user_id}' AND {$bean->table_name}.deleted=0 ";


        $query = $select . $auto_where;

        $result = $bean->db->query($query, true);

        $list = array();

        while ($row = $bean->db->fetchByAssoc($result)) {
            $row = $bean->convertRow($row);
            $bean->fetched_row = $row;
            $bean->fromArray($row);
            $bean->processed_dates_times = array();
            $bean->check_date_relationships_load();
            $bean->fill_in_additional_detail_fields();

            /**
             * PHP  5+ always treats objects as passed by reference
             * Need to clone it if we're using 5.0+
             * clone() not supported by 4.x
             */
            if (version_compare(phpversion(), "5.0", ">=")) {
                $newBean = clone($bean);
            } else {
                $newBean = $bean;
            }
            $list[] = $newBean;
        }

        return $list;
    }
}
