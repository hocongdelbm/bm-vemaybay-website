<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}



$view_config = array(
    'actions' => array(
        'sugarpdf' => array(
            'show_header' => false,
            'show_subpanels' => false,
            'show_search' => false,
            'show_footer' => false,
            'show_javascript' => false,
        ),
    ),
    'req_params' => array(
        'to_pdf' => array(
            'param_value' => true,
            'config' => array('show_all' => false),
        ),
    ),
);
