<?php

class AccountsHelper
{
    public static function get_supplier_array_search($add_blank = true)
    {
        $db = DBManagerFactory::getInstance();
        $result = array();

        if ($add_blank) {
            $result[''] = '';
        }

        $sql = "SELECT id, name
                FROM accounts
                WHERE deleted = 0
                  AND account_type = 'Supplier'
                  AND is_stop_tracking = 0
                ORDER BY name ASC";

        $res = $db->query($sql, true, 'Error loading supplier list for search');
        while ($row = $db->fetchByAssoc($res)) {
            $result[$row['id']] = $row['name'];
        }

        return $result;
    }
}
