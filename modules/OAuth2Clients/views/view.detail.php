<?php


if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class OAuth2ClientsViewDetail extends ViewDetail
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

    private function setViewType()
    {
        switch ($this->bean->allowed_grant_type) {
            case 'password':
                $this->type = 'detailpassword';
                $this->formName = 'DetailPassword';
                break;
            case 'client_credentials':
                $this->type = 'detailcredentials';
                $this->formName = 'DetailCredentials';
                break;
        }
    }

    /**
     * @inheritdoc
     */
    public function display()
    {
        $this->dv->formName = $this->formName;
        parent::display();
    }
}
