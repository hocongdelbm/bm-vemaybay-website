<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/**
 * Class OAuth2Clients
 */
class OAuth2Clients extends SugarBean
{
    /**
     * @var string
     */
    public $secret;

    /**
     * @var string
     */
    public $redirect_uri;

    /**
     * @var string
     */
    public $allowed_grant_type;

    /**
     * @var string
     */
    public $table_name = 'oauth2clients';

    /**
     * @var string
     */
    public $object_name = 'OAuth2Clients';

    /**
     * @var string
     */
    public $module_dir = 'OAuth2Clients';

    /**
     * @var bool
     */
    public $disable_row_level_security = true;

    /**
     * @see SugarBean::get_summary_text()
     */
    public function get_summary_text()
    {
        return (string)$this->name;
    }

    /**
     * @see SugarBean::save()
     *
     * @param bool $check_notify
     * @return string ID
     */
    public function save($check_notify = false)
    {
        if (!empty($_REQUEST['new_secret'])) {
            $this->secret = hash('sha256', $_REQUEST['new_secret']);
        }
        $this->setDurationValue();
        return parent::save();
    }

    private function setDurationValue()
    {
        if (empty($_REQUEST['duration_amount']) || empty($_REQUEST['duration_unit'])) {
            $this->duration_value = 60;
            $this->duration_amount = 1;
            $this->duration_unit = 'minute';
            return;
        }
        $amount = $_REQUEST['duration_amount'];
        $value = 1;
        switch ($_REQUEST['duration_unit']) {
            case 'month': $value = $amount * 30 * 24 * 60 * 60; break;
            case 'week': $value = $amount * 7 * 24 * 60 * 60; break;
            case 'day': $value = $amount * 24 * 60 * 60; break;
            case 'hour': $value = $amount * 60 * 60; break;
            case 'minute': $value = $amount * 60; break;
        }
        $this->duration_value = $value;
    }
}
