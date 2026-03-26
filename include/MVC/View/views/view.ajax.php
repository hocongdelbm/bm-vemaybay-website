<?php
require_once('include/MVC/View/SugarView.php');

class ViewAjax extends SugarView
{
    /**
     * Constructor
     *
     * @see SugarView::SugarView()
     */
    public function __construct()
    {
        $this->options['show_title'] = false;
        $this->options['show_header'] = false;
        $this->options['show_footer'] = false;
        $this->options['show_javascript'] = false;
        $this->options['show_subpanels'] = false;
        $this->options['show_search'] = false;

        parent::__construct();
    }
}
