<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

//TODO move me out of generic



/**
 * Generic Sugar widget
 * @api
 */
class SugarWidget
{
    public $layout_manager = null;
    public $widget_id;
    protected $form_value;
    protected $parent_bean;

    public function __construct(&$layout_manager)
    {
        $this->layout_manager = $layout_manager;
    }

    public function display($layout_def)
    {
        return 'display class undefined';
    }

    /**
     * getSubpanelWidgetId
     * This is a utility function to return a widget's unique id
     * @return id String label of the widget's unique id
     */
    public function getWidgetId()
    {
        return $this->widget_id;
    }

    /**
     * setSubpanelWidgetId
     * This is a utility function to set the id for a widget
     * @param id String value to set the widget's unique id
     */
    public function setWidgetId($id='')
    {
        $this->widget_id = $id;
    }

    public function getDisplayName()
    {
        return $this->form_value;
    }
    public function getParentBean()
    {
        return $this->parent_bean;
    }

    public function setParentBean($parent_bean)
    {
        $this->parent_bean = $parent_bean;
    }
    /**
     * getTruncatedColumnAlias
     * This function ensures that a column alias is no more than 28 characters.  Should the column_name
     * argument exceed 28 charcters, it creates an alias using the first 22 characters of the column_name
     * plus an md5 of the first 6 characters of the lowercased column_name value.
     *
     */
    protected function getTruncatedColumnAlias($column_name)
    {
        if (empty($column_name) || !is_string($column_name) || strlen($column_name) < 28) {
            return $column_name;
        }
        return strtoupper(substr($column_name, 0, 22) . substr(md5(strtolower($column_name)), 0, 6));
    }
}
