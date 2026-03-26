<?php

require_once('include/ListView/ListViewSmarty.php');

class ListViewPackages extends ListViewSmarty
{
    public $secondaryDisplayColumns;
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Override the setup method in ListViewSmarty since we are not passing in a bean
     *
     * @param mixed $data the data to display on the page
     * @param mixed $file the template file to parse
     * @param mixed $where
     * @param mixed $params
     * @param mixed $offset
     * @param mixed $limit
     * @param mixed $filter_fields
     * @param mixed $id_field
     * @param null|mixed $id
     */
    public function setup($data, $file, $where, $params = array(), $offset = 0, $limit = -1, $filter_fields = array(), $id_field = 'id', $id = null)
    {
        $this->data = $data;
        $this->tpl = $file;
    }

    /**
     * Override the display method
     * 
     * @param boolean $end
     */
    public function display($end = true)
    {
        global $odd_bg, $even_bg, $app_strings;
        $this->ss->assign('rowColor', array('oddListRow', 'evenListRow'));
        $this->ss->assign('bgColor', array($odd_bg, $even_bg));
        $this->ss->assign('displayColumns', $this->displayColumns);
        $this->ss->assign('secondaryDisplayColumns', $this->secondaryDisplayColumns);
        $this->ss->assign('data', $this->data);
        return $this->ss->fetch($this->tpl);
    }
}
