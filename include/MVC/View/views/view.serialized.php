<?php


class ViewSerialized extends SugarView
{
    public $type = 'detail';

    public function __construct()
    {
        parent::__construct();
    }




    public function display()
    {
        ob_clean();
        echo serialize($this->bean->toArray());
        sugar_cleanup(true);
    }
}
