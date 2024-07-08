<?php

require_once 'data/SugarBean.php';
require_once 'include/SugarObjects/templates/basic/Basic.php';
require_once 'include/externalAPI/ExternalAPIFactory.php';
require_once 'include/SugarOauth.php';

class EAPM extends Basic
{
    public $new_schema = true;
    public $module_dir = 'EAPM';
    public $object_name = 'EAPM';
    public $table_name = 'eapm';
    public $importable = false;
    public $id;
    public $type;
    public $name;
    public $date_entered;
    public $date_modified;
    public $modified_user_id;
    public $modified_by_name;
    public $created_by;
    public $created_by_name;
    public $description;
    public $deleted;
    public $created_by_link;
    public $modified_user_link;
    public $assigned_user_id;
    public $assigned_user_name;
    public $assigned_user_link;
    public $password;
    public $url;
    public $validated = false;
    public $oauth_token;
    public $oauth_secret;
    public $application;
    public $consumer_key;
    public $consumer_secret;
    public $disable_row_level_security = true;
    public static $passwordPlaceholder = '::PASSWORD::';

    public function bean_implements($interface)
    {
        switch ($interface) {
            case 'ACL': return true;
        }

        return false;
    }

    public static function getLoginInfo($application, $includeInactive = false)
    {
        global $current_user;

        $eapmBean = new self();

        if (isset($_SESSION['EAPM'][$application]) && !$includeInactive) {
            if (is_array($_SESSION['EAPM'][$application])) {
                $eapmBean->fromArray($_SESSION['EAPM'][$application]);
            } else {
                return;
            }
        } else {
            $queryArray = array('assigned_user_id' => $current_user->id, 'application' => $application, 'deleted' => 0);
            if (!$includeInactive) {
                $queryArray['validated'] = 1;
            }
            $eapmBean = $eapmBean->retrieve_by_string_fields($queryArray, false);

            // Don't cache the include inactive results
            if (!$includeInactive) {
                if ($eapmBean != null) {
                    $_SESSION['EAPM'][$application] = $eapmBean->toArray();
                } else {
                    $_SESSION['EAPM'][$application] = '';

                    return;
                }
            }
        }

        if (isset($eapmBean->password)) {
            require_once 'include/utils/encryption_utils.php';
            $eapmBean->password = blowfishDecode(blowfishGetKey('encrypt_field'), $eapmBean->password);
        }

        return $eapmBean;
    }

    public function create_new_list_query($order_by, $where, $filter = array(), $params = array(), $show_deleted = 0, $join_type = '', $return_array = false, $parentbean = null, $singleSelect = false, $ifListForExport = false)
    {
        global $current_user;

        if (!is_admin($GLOBALS['current_user'])) {
            // Restrict this so only admins can see other people's records
            $owner_where = $this->getOwnerWhere($current_user->id);

            if (empty($where)) {
                $where = $owner_where;
            } else {
                $where .= ' AND '.$owner_where;
            }
        }

        return parent::create_new_list_query($order_by, $where, $filter, $params, $show_deleted, $join_type, $return_array, $parentbean, $singleSelect);
    }

    public function save($check_notify = false)
    {
        $this->fillInName();
        if (!is_admin($GLOBALS['current_user'])) {
            $this->assigned_user_id = $GLOBALS['current_user']->id;
        }

        if (!empty($this->password) && $this->password == self::$passwordPlaceholder) {
            $this->password = empty($this->fetched_row['password']) ? '' : $this->fetched_row['password'];
        }

        $parentRet = parent::save($check_notify);

        // Nuke the EAPM cache for this record
        if (isset($_SESSION['EAPM'][$this->application])) {
            unset($_SESSION['EAPM'][$this->application]);
        }

        return $parentRet;
    }

    public function mark_deleted($id)
    {
        // Nuke the EAPM cache for this record
        if (isset($_SESSION['EAPM'][$this->application])) {
            unset($_SESSION['EAPM'][$this->application]);
        }

        return parent::mark_deleted($id);
    }

    public function validated()
    {
        if (empty($this->id)) {
            return false;
        }
        // Don't use save, it will attempt to revalidate
        $adata = DBManagerFactory::getInstance()->quote(isset($this->api_data) ? $this->api_data : null);
        DBManagerFactory::getInstance()->query("UPDATE eapm SET validated=1,api_data='$adata'  WHERE id = '{$this->id}' AND deleted = 0");
        if (!$this->deleted && !empty($this->application)) {
            // deactivate other EAPMs with same app
            $sql = "UPDATE eapm SET deleted=1 WHERE application = '{$this->application}' AND id != '{$this->id}' AND deleted = 0 AND assigned_user_id = '{$this->assigned_user_id}'";
            DBManagerFactory::getInstance()->query($sql, true);
        }

        // Nuke the EAPM cache for this record
        if (isset($_SESSION['EAPM'][$this->application])) {
            unset($_SESSION['EAPM'][$this->application]);
        }
    }

    protected function fillInName()
    {
        if (!empty($this->application)) {
            $apiList = ExternalAPIFactory::loadFullAPIList(false, true);
        }
        if (!empty($apiList) && isset($apiList[$this->application]) && $apiList[$this->application]['authMethod'] == 'oauth') {
            $this->name = sprintf(translate('LBL_OAUTH_NAME', $this->module_dir), $this->application);
        }
    }

    public function fill_in_additional_detail_fields()
    {
        $this->fillInName();
        parent::fill_in_additional_detail_fields();
    }

    public function fill_in_additional_list_fields()
    {
        $this->fillInName();
        parent::fill_in_additional_list_fields();
    }

    public function save_cleanup()
    {
        $this->oauth_token = '';
        $this->oauth_secret = '';
        $this->api_data = '';
    }

    /**
     * Given a user remove their associated accounts. This is called when a user is deleted from the system.
     *
     * @param  $user_id
     */
    public function delete_user_accounts($user_id)
    {
        $sql = "DELETE FROM {$this->table_name} WHERE assigned_user_id = '{$user_id}'";
        DBManagerFactory::getInstance()->query($sql, true);
    }
}

// External API integration, for the dropdown list of what external API's are available
function getEAPMExternalApiDropDown()
{
    $apiList = ExternalAPIFactory::getModuleDropDown('', true, true);

    return $apiList;
}
