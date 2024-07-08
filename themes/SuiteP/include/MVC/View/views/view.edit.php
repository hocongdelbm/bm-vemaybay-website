<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}


require_once('include/EditView/EditView2.php');

class ViewEdit extends SugarView
{
    /**
     * @var EditView $ev
     */
    public $ev;

    /**
     * @inheritdoc
     */
    public $type = 'edit';

    /**
     * @var boolean $useForSubpanel determine whether view can be used for subpanel creates
     */
    public $useForSubpanel = false;

    /**
     * @var boolean to determine whether or not SubpanelQuickCreate has a separate display function
     */
    public $useModuleQuickCreateTemplate = false;

    /**
     * @var boolean used to passed showTitle to $ev used for backwards compatibility
     */
    public $showTitle = true;

    /**
     * ViewEdit constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @see SugarView::preDisplay()
     */
    public function preDisplay()
    {
        $metadataFile = $this->getMetaDataFile();
        $this->ev = $this->getEditView();
        $this->ev->ss =& $this->ss;
        $this->ev->setup($this->module, $this->bean, $metadataFile);
    }

    /**
     * @inheritdoc
     */
    public function display()
    {
        $this->ev->process();
        echo $this->ev->display($this->showTitle);
    }

    /**
     * Get a new EditView object
     * @return EditView
     */
    public function getEditView()
    {
        if (empty($this->ev)) {
            $this->ev = new EditView();
        }

        return $this->ev;
    }
}
