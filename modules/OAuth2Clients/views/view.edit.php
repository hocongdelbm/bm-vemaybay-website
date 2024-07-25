<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class OAuth2ClientsViewEdit extends ViewEdit
{
    /**
     * @var OAuth2Clients $bean
     */
    public $bean;

    /**
     * @var string $formName
     */
    public $formName;

    /**
     * @see SugarView::preDisplay()
     */
    public function getMetaDataFile()
    {
        $this->setViewType();
        return parent::getMetaDataFile();
    }

    /**
     *
     */
    private function setViewType()
    {
        switch ($this->bean->allowed_grant_type) {
            case 'password':
                $this->type = 'editpassword';
                $this->formName = 'EditPassword';
                break;
            case 'client_credentials':
                $this->type = 'editcredentials';
                $this->formName = 'EditCredentials';
                break;
        }
        if (!empty($_REQUEST['action'])) {
            switch ($_REQUEST['action']) {
                case 'EditViewPassword':
                    $this->type = 'editpassword';
                    $this->formName = 'EditPassword';
                    break;
                case 'EditViewCredentials':
                    $this->type = 'editcredentials';
                    $this->formName = 'EditCredentials';
                    break;
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function display()
    {
        $this->ev->formName = $this->formName;
        parent::display();
    }
}
