<?php
/**
 * ContactsViewRetrieveEmailUsername.php
 *
 * This class overrides SugarView and provides an implementation for the RetrieveEmailUsername
 * method used for returning the information about an email address
 *
 * @author Collin Lee
 * */

require_once('include/MVC/View/SugarView.php');
require_once("include/JSON.php");

class ContactsViewRetrieveEmail extends SugarView
{
    public function __construct()
    {
        parent::__construct();
    }

    public function process()
    {
        $this->display();
    }

    public function display()
    {
        $data = array();
        $data['target'] = $_REQUEST['target'];
        if (!empty($_REQUEST['email'])) {
            $db = DBManagerFactory::getInstance();
            $email = DBManagerFactory::getInstance()->quote(strtoupper(trim($_REQUEST['email'])));
            $result = $db->query("SELECT * FROM email_addresses WHERE email_address_caps = '$email' AND deleted = 0");
            if ($row = $db->fetchByAssoc($result)) {
                $data['email'] = $row;
            } else {
                $data['email'] = '';
            }
        }
        $json = new JSON();
        echo $json->encode($data);
    }
}
