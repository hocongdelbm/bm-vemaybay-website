<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('include/ytree/Tree.php');
require_once('include/JSON.php');

class JQueryTree extends  Tree
{
    public $tree_style = '';
    public $_header_files = array();
}
