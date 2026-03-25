<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}



$view_config = array(
    'actions' => array(
        'detailview' => array(
            'show_header' => true,
            'show_footer' => true,
            'view_print'  => false,
            'show_title' => true,
            'show_subpanels' => true,
            'show_javascript' => true,
            'show_search' => true,
            'json_output' => false,
        ),
    ),
    'req_params' => array(
        'ajax_load' => array(
            'param_value' => true,
            'config' => array(
                'show_header' => false,
                'show_footer' => false,
                'view_print'  => false,
                'show_title' => true,
                'show_subpanels' => true,
                'show_javascript' => false,
                'show_search' => true,
                'json_output' => true,
            )
        ),
    ),
);
