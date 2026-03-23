
<?php

class UsersHelper
{
    public static function get_user_array_search($add_blank = true, $status = 'Active', $user_id = '')
    {
        global $locale, $sugar_config, $current_user;

        if (empty($locale)) {
            $locale = new Localization();
        }

        if (empty($user_array)) {
            $db = DBManagerFactory::getInstance();
            $temp_result = array();
            // Including deleted users for now.
            if (empty($status)) {
                $query = 'SELECT id, last_name, first_name, user_name FROM users WHERE 1=1';
            } else {
                $query = "SELECT id, last_name, first_name, user_name from users WHERE status='$status'";
            }

            /* BEGIN - SECURITY GROUPS */
            global $current_user, $sugar_config;
            if (
                !is_admin($current_user) && isset($sugar_config['securitysuite_filter_user_list']) && $sugar_config['securitysuite_filter_user_list'] == true && (empty($_REQUEST['module']) || $_REQUEST['module'] != 'Home') && (empty($_REQUEST['action']) || $_REQUEST['action'] != 'DynamicAction')
            ) {
                require_once 'modules/SecurityGroups/SecurityGroup.php';
                global $current_user;
                $group_where = SecurityGroup::getGroupUsersWhere($current_user->id);
                $query .= ' AND (' . $group_where . ') ';
            }
            /* END - SECURITY GROUPS */

            if (!empty($user_id)) {
                $query .= " OR id='{$user_id}'";
            }

            //get the user preference for name formatting, to be used in order by
            $order_by_string = ' user_name ASC ';
            if (!empty($current_user) && !empty($current_user->id)) {
                $formatString = $current_user->getPreference('default_locale_name_format');

                //create the order by string based on position of first and last name in format string
                $order_by_string = ' user_name ASC ';
                $firstNamePos = strpos((string) $formatString, 'f');
                $lastNamePos = strpos((string) $formatString, 'l');
                if ($firstNamePos !== false || $lastNamePos !== false) {
                    //its possible for first name to be skipped, check for this
                    if ($firstNamePos === false) {
                        $order_by_string = 'last_name ASC';
                    } else {
                        $order_by_string = ($lastNamePos < $firstNamePos) ? 'last_name ASC' : 'last_name ASC';
                    }
                }
            }
            $query = $query . ' ORDER BY ' . $order_by_string;
            $GLOBALS['log']->debug("get_user_array query: $query");
            $result = $db->query($query, true, 'Error filling in user array: ');

            if ($add_blank == true) {
                $temp_result[''] = '';
            }

            // Get the id and the name.
            while ($row = $db->fetchByAssoc($result)) {
                $temp_result[$row['id']] = $locale->getLocaleFormattedName($row['first_name'], $row['last_name']) . ' (' . $row['user_name'] . ')';
            }

            $user_array = $temp_result;
        }

        return $user_array;
    }
}
