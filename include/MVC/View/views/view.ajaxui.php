<?php
require_once('include/MVC/View/SugarView.php');

class ViewAjaxUI extends SugarView
{
    /**
     * Constructor
     *
     * @see SugarView::SugarView()
     */
    public function __construct()
    {
        $this->options['show_title'] = true;
        $this->options['show_header'] = true;
        $this->options['show_footer'] = true;
        $this->options['show_javascript'] = true;
        $this->options['show_subpanels'] = false;
        $this->options['show_search'] = false;

        parent::__construct();
    }

    public function display()
    {
        $user = $GLOBALS["current_user"];
        $etag = $user->id . $user->getETagSeed("mainMenuETag");
        generateEtagHeader($etag);
        //Prevent double footers
        $GLOBALS['app']->headerDisplayed = false;
    }
}
