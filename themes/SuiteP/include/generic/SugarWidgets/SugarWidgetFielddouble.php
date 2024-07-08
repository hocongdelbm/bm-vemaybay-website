<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class SugarWidgetFieldDouble extends SugarWidgetFieldInt
{
    public function __construct(&$layout_manager)
    {
        parent::__construct($layout_manager);
    }
}
